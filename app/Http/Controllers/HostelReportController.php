<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\HostelRoom;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/**
 * Hostel reporting: bed vacancy and the allocation register.
 *
 * The allocation register can be narrowed to a hostel, academic year, class and
 * section (stream) and exported, which is what the hostel office needs at the
 * start of each term.
 */
class HostelReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:hostel.view');
    }

    public function index()
    {
        $hostels = Hostel::withCount('hostelRooms')->get()->map(function (Hostel $hostel) {
            $hostel->beds = $hostel->getBedCapacity();
            $hostel->residents = $hostel->getCurrentOccupancy();

            return $hostel;
        });

        $totals = [
            'hostels' => $hostels->count(),
            'rooms' => (int) $hostels->sum('hostel_rooms_count'),
            'beds' => (int) $hostels->sum('beds'),
            'residents' => (int) $hostels->sum('residents'),
            'maintenance_rooms' => HostelRoom::where('status', HostelRoom::STATUS_UNDER_MAINTENANCE)->count(),
        ];
        $totals['free_beds'] = max(0, $totals['beds'] - $totals['residents']);

        return view('hostels.reports', compact('hostels', 'totals'));
    }

    /**
     * Beds still free, grouped by room.
     *
     * Both the page and the PDF are built by vacancyRoomQuery() so the printed
     * sheet can never disagree with the screen.
     */
    public function vacancyReport(Request $request)
    {
        $request->validate([
            'hostel_id' => 'nullable|exists:hostels,hostel_id',
        ]);

        $rooms = $this->vacancyRooms($request);
        $summary = $this->vacancySummary($rooms, $request);

        return view('hostels.reports.vacancy', [
            'rooms' => $rooms,
            'summary' => $summary,
            'hostel' => $request->filled('hostel_id') ? Hostel::find($request->hostel_id) : null,
            'hostels' => Hostel::pluck('name', 'hostel_id')->toArray(),
        ]);
    }

    /**
     * Vacancy report as a printable PDF.
     */
    public function vacancyReportPdf(Request $request)
    {
        $request->validate([
            'hostel_id' => 'nullable|exists:hostels,hostel_id',
        ]);

        $rooms = $this->vacancyRooms($request);
        $summary = $this->vacancySummary($rooms, $request);
        $hostel = $request->filled('hostel_id') ? Hostel::find($request->hostel_id) : null;

        $pdf = Pdf::loadView('hostels.reports.exports.vacancy_pdf', [
            'rooms' => $rooms,
            'summary' => $summary,
            'hostel' => $hostel,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('hostel-vacancy-report-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Rooms that can still take a resident, most free beds first.
     */
    private function vacancyRooms(Request $request)
    {
        return HostelRoom::with('hostel')
            ->where('status', '!=', HostelRoom::STATUS_UNDER_MAINTENANCE)
            ->whereColumn('occupied', '<', 'capacity')
            ->when($request->filled('hostel_id'), fn ($q) => $q->where('hostel_id', $request->hostel_id))
            ->get()
            ->sortByDesc(fn (HostelRoom $room) => $room->getAvailableBeds())
            ->values();
    }

    private function vacancySummary($rooms, Request $request): array
    {
        $summary = [
            'rooms' => $rooms->count(),
            'capacity' => (int) $rooms->sum('capacity'),
            'occupied' => (int) $rooms->sum('occupied'),
            'free_beds' => (int) $rooms->sum(fn (HostelRoom $room) => $room->getAvailableBeds()),
        ];

        $summary['maintenance_rooms'] = HostelRoom::where('status', HostelRoom::STATUS_UNDER_MAINTENANCE)
            ->when($request->filled('hostel_id'), fn ($q) => $q->where('hostel_id', $request->hostel_id))
            ->count();

        return $summary;
    }

    /**
     * Allocation register with class/stream breakdowns and export.
     */
    public function studentList(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $query = HostelAllocation::filter($filters);

        // The register is about current residents unless another status is asked for.
        if (empty($filters['status'])) {
            $query->where('status', 'active');
        }

        $allocations = $query
            ->orderBy('hostel_id')
            ->orderBy('allocation_date')
            ->paginate(25)
            ->withQueryString();

        return view('hostels.reports.student_list', [
            'allocations' => $allocations,
            'hostel' => !empty($filters['hostel_id']) ? Hostel::find($filters['hostel_id']) : null,
            'hostels' => Hostel::pluck('name', 'hostel_id')->toArray(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->pluck('name', 'academic_year_id')->toArray(),
            'classes' => SchoolClass::orderBy('numeric_value')->pluck('name', 'class_id')->toArray(),
            'sections' => Section::orderBy('name')->pluck('name', 'section_id')->toArray(),
            'sectionClasses' => Section::pluck('class_id', 'section_id')->toArray(),
            'filters' => $filters,
        ]);
    }

    /**
     * Export the register to PDF using the active filters.
     */
    public function studentListPdf(Request $request)
    {
        $filters = $this->validatedFilters($request);

        $allocations = HostelAllocation::filter($filters)
            ->when(empty($filters['status']), fn ($q) => $q->where('status', 'active'))
            ->orderBy('hostel_id')
            ->orderBy('allocation_date')
            ->get();

        $byClass = $allocations
            ->groupBy(fn (HostelAllocation $a) => $a->class_info)
            ->map->count()
            ->sortDesc();

        $pdf = Pdf::loadView('hostels.reports.exports.allocation_register_pdf', [
            'allocations' => $allocations,
            'byClass' => $byClass,
            'scopeLabel' => $this->scopeLabel($filters),
            'hostel' => !empty($filters['hostel_id']) ? Hostel::find($filters['hostel_id']) : null,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('hostel-allocation-register-' . date('Y-m-d') . '.pdf');
    }

    /**
     * @return array<string,mixed>
     */
    private function validatedFilters(Request $request): array
    {
        $request->validate([
            'hostel_id' => 'nullable|exists:hostels,hostel_id',
            'status' => 'nullable|in:active,vacated,pending',
            'academic_year_id' => 'nullable|exists:academic_years,academic_year_id',
            'class_id' => 'nullable|exists:classes,class_id',
            'section_id' => 'nullable|exists:sections,section_id',
            'search' => 'nullable|string|max:120',
        ]);

        return array_filter($request->only([
            'hostel_id', 'status', 'academic_year_id', 'class_id', 'section_id', 'search',
        ]), fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param  array<string,mixed>  $filters
     */
    private function scopeLabel(array $filters): string
    {
        $parts = [];

        if (!empty($filters['hostel_id'])) {
            $parts[] = Hostel::where('hostel_id', $filters['hostel_id'])->value('name') ?? 'All hostels';
        }

        if (!empty($filters['academic_year_id'])) {
            $parts[] = 'Year ' . (AcademicYear::where('academic_year_id', $filters['academic_year_id'])->value('name') ?? '');
        }

        if (!empty($filters['class_id'])) {
            $parts[] = SchoolClass::where('class_id', $filters['class_id'])->value('name') ?? '';
        }

        if (!empty($filters['section_id'])) {
            $parts[] = (Section::where('section_id', $filters['section_id'])->value('name') ?? '') . ' stream';
        }

        if (!empty($filters['status'])) {
            $parts[] = ucfirst($filters['status']) . ' allocations';
        } else {
            $parts[] = 'Current residents';
        }

        if (!empty($filters['search'])) {
            $parts[] = 'search "' . $filters['search'] . '"';
        }

        return implode(' · ', array_filter($parts));
    }
}
