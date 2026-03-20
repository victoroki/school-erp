@extends('layouts.app')

@section('content')
    <div class="content-header py-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-dark mb-0" style="font-size: 1.85rem;">
                        <i class="fas fa-chart-line mr-2 text-primary"></i>
                        Financial Insights
                    </h1>
                    <p class="text-muted mb-0">Overview of student fees and revenue streams</p>
                </div>
                <div class="col-sm-6 text-right mt-3 mt-sm-0">
                    <div class="d-inline-flex align-items-center bg-white border px-3 py-2 rounded-lg shadow-xs">
                        <i class="fas fa-calendar-alt text-primary mr-2"></i>
                        <span class="text-dark font-weight-bold small">{{ $currentYear->name ?? 'Select Academic Year' }}</span>
                        @if($currentYear && $currentYear->is_current)
                            <span class="badge badge-success-soft ml-2 x-small">CURRENT</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content px-4">
        @include('flash::message')
        
        @if(!$currentYear)
            <div class="alert bg-amber-soft border-amber text-amber-dark mb-4 p-3 rounded-lg d-flex align-items-center">
                <i class="fas fa-exclamation-triangle mr-3 fa-lg"></i>
                <div>
                    <strong>Action Required:</strong> Please configure an active academic year to see current financial data.
                    <a href="{{ route('academic-years.index') }}" class="ml-2 font-weight-bold text-decoration-underline">Set Up Now</a>
                </div>
            </div>
        @endif

        <!-- Premium Summary Section -->
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden h-100 summary-card bg-primary-gradient">
                    <div class="card-body p-4 position-relative text-white">
                        <div class="summary-icon">
                            <i class="fas fa-money-check-alt opacity-25"></i>
                        </div>
                        <div class="extra-small text-uppercase font-weight-bold mb-1" style="opacity: 0.85;">Expected Revenue</div>
                        <h2 class="font-weight-bold mb-1">KES {{ number_format($expectedRevenue) }}</h2>
                        <div class="progress progress-xxs bg-white-opacity mt-3" style="height: 4px;">
                            <div class="progress-bar bg-white" style="width: 75%"></div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="extra-small" style="opacity: 0.85;">Assigned Fees</span>
                            <a href="{{ route('fees.reports.expected-revenue') }}" class="text-white extra-small font-weight-bold">View Report <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden h-100 summary-card bg-emerald-gradient">
                    <div class="card-body p-4 position-relative text-white">
                        <div class="summary-icon">
                            <i class="fas fa-file-invoice opacity-25"></i>
                        </div>
                        <div class="extra-small text-uppercase font-weight-bold mb-1" style="opacity: 0.85;">Active Structures</div>
                        <h2 class="font-weight-bold mb-1">{{ $totalFeeStructures }}</h2>
                        <div class="progress progress-xxs bg-white-opacity mt-3" style="height: 4px;">
                            <div class="progress-bar bg-white" style="width: 100%"></div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="extra-small" style="opacity: 0.85;">Fee Templates</span>
                            <a href="{{ route('fee-structures.index') }}" class="text-white extra-small font-weight-bold">Manage <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden h-100 summary-card bg-amber-gradient">
                    <div class="card-body p-4 position-relative text-white">
                        <div class="summary-icon">
                            <i class="fas fa-tags opacity-25"></i>
                        </div>
                        <div class="extra-small text-uppercase font-weight-bold mb-1" style="opacity: 0.85;">Total Discounts</div>
                        <h2 class="font-weight-bold mb-1">KES {{ number_format($totalDiscounts) }}</h2>
                        <div class="progress progress-xxs bg-white-opacity mt-3" style="height: 4px;">
                            <div class="progress-bar bg-white" style="width: 45%"></div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="extra-small" style="opacity: 0.85;">Fee Reductions</span>
                            <a href="{{ route('fees.reports.discount-summary') }}" class="text-white extra-small font-weight-bold">Details <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden h-100 summary-card bg-rose-gradient">
                    <div class="card-body p-4 position-relative text-white">
                        <div class="summary-icon">
                            <i class="fas fa-user-clock opacity-25"></i>
                        </div>
                        <div class="extra-small text-uppercase font-weight-bold mb-1" style="opacity: 0.85;">Pending Setup</div>
                        <h2 class="font-weight-bold mb-1">{{ $notAssignedCount }}</h2>
                        <div class="progress progress-xxs bg-white-opacity mt-3" style="height: 4px;">
                            @php
                                $totalStd = ($studentsWithFees ?? 0) + $notAssignedCount;
                                $assignedPercent = $totalStd > 0 ? ( ($studentsWithFees ?? 0) / $totalStd) * 100 : 0;
                                $unassignedPercent = 100 - $assignedPercent;
                            @endphp
                            <div class="progress-bar bg-white" style="width: {{ $unassignedPercent }}%"></div>
                        </div>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <span class="extra-small" style="opacity: 0.85;">Unassigned Students</span>
                            <a href="{{ route('fees.assignments.unassigned') }}" class="text-white extra-small font-weight-bold">Fix Now <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Assignment Progress Card -->
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="font-weight-bold text-dark">Enrollment & Fees</h5>
                        <p class="text-muted small">Tracking student fee assignments</p>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="position-relative d-inline-block mb-4">
                            <svg class="progress-ring" width="160" height="160">
                                <circle class="progress-ring__circle_bg" stroke="#f1f5f9" stroke-width="12" fill="transparent" r="70" cx="80" cy="80"/>
                                <circle class="progress-ring__circle" stroke="#3b82f6" stroke-width="12" stroke-dasharray="{{ 2 * pi() * 70 }}" stroke-dashoffset="{{ (1 - ($assignedPercent/100)) * (2 * pi() * 70) }}" stroke-linecap="round" fill="transparent" r="70" cx="80" cy="80"/>
                            </svg>
                            <div class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                                <h2 class="font-weight-bold mb-0">{{ round($assignedPercent) }}%</h2>
                                <span class="extra-small text-muted font-weight-bold">ASSIGNED</span>
                            </div>
                        </div>
                        
                        <div class="row text-left px-3">
                            <div class="col-12 mb-3">
                                <div class="p-3 rounded-lg border bg-light-soft d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary rounded-circle mr-2" style="width: 10px; height: 10px;"></div>
                                        <span class="small font-weight-bold">Assigned</span>
                                    </div>
                                    <span class="font-weight-bold">{{ $studentsWithFees ?? 0 }} Students</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="p-3 rounded-lg border bg-light-soft d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-rose rounded-circle mr-2" style="width: 10px; height: 10px;"></div>
                                        <span class="small font-weight-bold">Remaining</span>
                                    </div>
                                    <span class="font-weight-bold">{{ $notAssignedCount }} Students</span>
                                </div>
                            </div>
                        </div>
                        
                        <a href="{{ route('fees.assignments.create') }}" class="btn btn-primary btn-block mt-4 py-3 font-weight-bold shadow-sm" style="border-radius: 12px;">
                            <i class="fas fa-plus-circle mr-2"></i> Start Bulk Assignment
                        </a>
                    </div>
                </div>
            </div>

            <!-- Revenue Ranking Card -->
            <div class="col-lg-7 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="font-weight-bold text-dark">Revenue by Class</h5>
                            <p class="text-muted small">Top 5 classes by expected revenue</p>
                        </div>
                        <div class="bg-blue-light text-blue rounded-pill px-3 py-1 extra-small font-weight-bold">
                            TOP PERFORMERS
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light-soft">
                                    <tr>
                                        <th class="border-0 px-4 py-3 small font-weight-bold text-muted">CLASS</th>
                                        <th class="border-0 px-4 py-3 small font-weight-bold text-muted">PROGRESS</th>
                                        <th class="border-0 px-4 py-3 small font-weight-bold text-muted text-right">EXPECTED</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $maxRev = count($revenueByClass) > 0 ? $revenueByClass->max('total') : 1;
                                    @endphp
                                    @foreach($revenueByClass as $row)
                                        <tr>
                                            <td class="px-4 py-4 align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-blue-light text-blue rounded p-2 mr-3 font-weight-bold small" style="width: 40px; text-align: center;">
                                                        {{ substr($row->class_name, 0, 2) }}
                                                    </div>
                                                    <span class="font-weight-bold text-dark">{{ $row->class_name }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 align-middle" style="width: 200px;">
                                                @php $classPercent = ($row->total / $maxRev) * 100; @endphp
                                                <div class="progress progress-sm" style="height: 6px; border-radius: 3px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $classPercent }}%"></div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 align-middle text-right font-weight-bold text-dark">
                                                KES {{ number_format($row->total) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3 text-center">
                        <a href="{{ route('fees.reports.expected-revenue') }}" class="text-primary font-weight-bold small">View Full Distribution <i class="fas fa-chevron-right ml-1"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Grid -->
        <div class="mt-4 mb-5">
            <h5 class="font-weight-bold text-dark mb-4">Control Panel</h5>
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="{{ route('fees.assignments.create') }}" class="action-card text-decoration-none h-100">
                        <div class="card border-0 shadow-sm h-100 transition-all card-hover-primary" style="border-radius: 16px;">
                            <div class="card-body p-4 text-center">
                                <div class="icon-circle bg-blue-light text-blue mb-3 mx-auto">
                                    <i class="fas fa-users-cog fa-lg"></i>
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">Bulk Assign</h6>
                                <p class="text-muted extra-small mb-0">Assign fees by class or group</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="{{ route('fee-structures.create') }}" class="action-card text-decoration-none h-100">
                        <div class="card border-0 shadow-sm h-100 transition-all card-hover-emerald" style="border-radius: 16px;">
                            <div class="card-body p-4 text-center">
                                <div class="icon-circle bg-green-light text-green mb-3 mx-auto">
                                    <i class="fas fa-file-invoice-dollar fa-lg"></i>
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">Structure Setup</h6>
                                <p class="text-muted extra-small mb-0">Define new billing templates</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="{{ route('fees.discounts.create') }}" class="action-card text-decoration-none h-100">
                        <div class="card border-0 shadow-sm h-100 transition-all card-hover-amber" style="border-radius: 16px;">
                            <div class="card-body p-4 text-center">
                                <div class="icon-circle bg-orange-light text-orange mb-3 mx-auto">
                                    <i class="fas fa-percentage fa-lg"></i>
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">Fee Reliefs</h6>
                                <p class="text-muted extra-small mb-0">Manage scholarships & discounts</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <a href="{{ route('fee-management.index') }}" class="action-card text-decoration-none h-100">
                        <div class="card border-0 shadow-sm h-100 transition-all card-hover-rose" style="border-radius: 16px;">
                            <div class="card-body p-4 text-center">
                                <div class="icon-circle bg-red-light text-red mb-3 mx-auto">
                                    <i class="fas fa-cash-register fa-lg"></i>
                                </div>
                                <h6 class="font-weight-bold text-dark mb-1">Collection</h6>
                                <p class="text-muted extra-small mb-0">Record and verify payments</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Typography & Utility */
        .extra-small { font-size: 0.7rem; letter-spacing: 0.5px; }
        .x-small { font-size: 0.6rem; }
        .bg-light-soft { background-color: #f8fafc; }
        
        /* Badges */
        .badge-success-soft { background-color: #dcfce7; color: #166534; font-weight: 700; border-radius: 4px; }
        
        /* Gradients */
        .bg-primary-gradient { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important; color: white !important; }
        .bg-emerald-gradient { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; color: white !important; }
        .bg-amber-gradient { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; color: white !important; }
        .bg-rose-gradient { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important; color: white !important; }
        
        .bg-white-opacity { background-color: rgba(255, 255, 255, 0.2); }
        
        /* Cards */
        .summary-card { border-radius: 18px; position: relative; border: none !important; }
        .summary-card h2, .summary-card span, .summary-card a, .summary-card div { color: white !important; }
        .summary-icon { position: absolute; right: -10px; bottom: -10px; font-size: 5rem; color: white !important; opacity: 0.25; }
        
        /* Progress Circle */
        .progress-ring__circle { transition: stroke-dashoffset 1s ease-in-out; }
        
        /* Icon Circles */
        .icon-circle { width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
        
        /* Action Cards Hover */
        .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .action-card:hover .card { transform: translateY(-8px); box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.1) !important; }
        
        .card-hover-primary:hover { border-bottom: 4px solid #3b82f6 !important; }
        .card-hover-emerald:hover { border-bottom: 4px solid #10b981 !important; }
        .card-hover-amber:hover { border-bottom: 4px solid #f59e0b !important; }
        .card-hover-rose:hover { border-bottom: 4px solid #f43f5e !important; }

        /* Generic Colors */
        .bg-blue-light { background-color: #eff6ff; }
        .text-blue { color: #3b82f6; }
        .bg-green-light { background-color: #ecfdf5; }
        .text-green { color: #10b981; }
        .bg-orange-light { background-color: #fffbeb; }
        .text-orange { color: #f59e0b; }
        .bg-red-light { background-color: #fef2f2; }
        .text-red { color: #ef4444; }
        .bg-rose { background-color: #f43f5e; }
        .bg-amber-soft { background-color: #fffbeb; }
        .border-amber { border: 1px solid #fcd34d !important; }
        .text-amber-dark { color: #92400e; }
        
        .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    </style>
@endsection
