<?php

namespace App\Http\Requests;

use App\Models\Timetable;
use App\Models\TeacherSubject;
use App\Models\ClassSubject;
use App\Models\ClassSection;
use App\Services\TimetableConflictService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimetableRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = Timetable::$rules;

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $classSectionId = $this->input('class_section_id');
            $academicYearId = $this->input('academic_year_id');
            $dayOfWeek = $this->input('day_of_week');
            $periodId = $this->input('period_id');
            $teacherId = $this->input('teacher_id');
            $classroomId = $this->input('classroom_id');
            $subjectId = $this->input('subject_id');
            $timetableId = $this->route('timetable');
            $termWeekId = $this->input('term_week_id');

            if (
                !$classSectionId ||
                !$academicYearId ||
                !$dayOfWeek ||
                !$periodId ||
                !$teacherId ||
                !$classroomId ||
                !$subjectId
            ) {
                return;
            }

            if ($termWeekId) {
                $teacherResult = TimetableConflictService::effectiveTeacherConflict(
                    $academicYearId, (int) $termWeekId, (int) $teacherId, $dayOfWeek, (int) $periodId, $timetableId
                );
                if ($teacherResult) {
                    $validator->errors()->add('teacher_id', $teacherResult['message']);
                }

                $classResult = TimetableConflictService::effectiveClassSlotTaken(
                    $academicYearId, (int) $termWeekId, (int) $classSectionId, $dayOfWeek, (int) $periodId, $timetableId
                );
                if ($classResult) {
                    $validator->errors()->add('period_id', $classResult['message']);
                }

                $roomResult = TimetableConflictService::effectiveClassroomConflict(
                    $academicYearId, (int) $termWeekId, (int) $classroomId, $dayOfWeek, (int) $periodId, $timetableId
                );
                if ($roomResult) {
                    $validator->errors()->add('classroom_id', $roomResult['message']);
                }

                $workloadWarnings = TimetableConflictService::checkTeacherWorkload(
                    (int) $teacherId, $academicYearId, $dayOfWeek, $timetableId
                );
                foreach ($workloadWarnings as $warning) {
                    $validator->errors()->add('teacher_id', $warning['message']);
                }
            } else {
                $timetables = Timetable::where('academic_year_id', $academicYearId)->get()->toArray();

                if (TimetableConflictService::classSlotTaken($timetables, $classSectionId, $academicYearId, $dayOfWeek, $periodId, $timetableId)) {
                    $validator->errors()->add(
                        'period_id',
                        'This class and section already has a lesson in the selected period on this day.'
                    );
                }

                if (TimetableConflictService::teacherConflict($timetables, $teacherId, $academicYearId, $dayOfWeek, $periodId, $timetableId)) {
                    $validator->errors()->add(
                        'teacher_id',
                        'The selected teacher is already scheduled for another class at this time.'
                    );
                }

                if (TimetableConflictService::classroomConflict($timetables, $classroomId, $academicYearId, $dayOfWeek, $periodId, $timetableId)) {
                    $validator->errors()->add(
                        'classroom_id',
                        'The selected classroom is already in use at this time.'
                    );
                }
            }

            $teacherSubjects = TeacherSubject::where('academic_year_id', $academicYearId)->get()->toArray();
            if (!TimetableConflictService::teacherSubjectAssigned($teacherSubjects, $teacherId, $subjectId, $classSectionId, $academicYearId)) {
                $validator->errors()->add(
                    'teacher_id',
                    'The selected teacher is not assigned to teach this subject for the chosen class and academic year.'
                );
            }

            $classSection = ClassSection::find($classSectionId);
            if ($classSection) {
                $classId = $classSection->class_id;

                $classSubjects = ClassSubject::where('academic_year_id', $academicYearId)->get()->toArray();
                if (!TimetableConflictService::classSubjectConfigured($classSubjects, $classId, $subjectId, $academicYearId)) {
                    $validator->errors()->add(
                        'subject_id',
                        'The selected subject is not configured for this class in the chosen academic year.'
                    );
                }
            }
        });
    }
}
