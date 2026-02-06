@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-file-invoice mr-2 text-dark"></i>Financial Reports & Statements</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Core Statements -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 rounded-lg overflow-hidden">
                    <div class="card-body p-4">
                        <div class="bg-primary-light p-3 rounded-lg d-inline-block mb-3">
                            <i class="fas fa-file-contract fa-2x text-primary"></i>
                        </div>
                        <h5 class="font-weight-bold">Financial Statements</h5>
                        <p class="text-muted small">Standard statements for auditing and oversight.</p>
                        <hr class="my-4">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <a href="{{ route('financial-reports.cashflow') }}" class="d-flex align-items-center text-dark font-weight-bold text-decoration-none">
                                    <i class="fas fa-chevron-right mr-2 text-primary small"></i> Cashflow Statement
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="{{ route('financial-reports.p-and-l') }}" class="d-flex align-items-center text-dark font-weight-bold text-decoration-none">
                                    <i class="fas fa-chevron-right mr-2 text-primary small"></i> Income Statement (P & L)
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="#" class="d-flex align-items-center text-dark font-weight-bold text-decoration-none opacity-50">
                                    <i class="fas fa-chevron-right mr-2 text-primary small"></i> Balance Sheet
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Analysis Reports -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 rounded-lg overflow-hidden">
                    <div class="card-body p-4">
                        <div class="bg-success-light p-3 rounded-lg d-inline-block mb-3">
                            <i class="fas fa-chart-pie fa-2x text-success"></i>
                        </div>
                        <h5 class="font-weight-bold">Analysis Reports</h5>
                        <p class="text-muted small">Detailed breakdowns of revenue and spending.</p>
                        <hr class="my-4">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <a href="{{ route('budgets.vs-actual') }}" class="d-flex align-items-center text-dark font-weight-bold text-decoration-none">
                                    <i class="fas fa-chevron-right mr-2 text-success small"></i> Budget vs Actual Analysis
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="#" class="d-flex align-items-center text-dark font-weight-bold text-decoration-none opacity-50">
                                    <i class="fas fa-chevron-right mr-2 text-success small"></i> Expense Breakdown by Staff
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="#" class="d-flex align-items-center text-dark font-weight-bold text-decoration-none opacity-50">
                                    <i class="fas fa-chevron-right mr-2 text-success small"></i> Fee Collection Trends
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Registry & Logs -->
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100 rounded-lg overflow-hidden">
                    <div class="card-body p-4">
                        <div class="bg-info-light p-3 rounded-lg d-inline-block mb-3">
                            <i class="fas fa-stream fa-2x text-info"></i>
                        </div>
                        <h5 class="font-weight-bold">Audit & Compliance</h5>
                        <p class="text-muted small">Records for tracking changes and ensuring accuracy.</p>
                        <hr class="my-4">
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <a href="{{ route('audit-trail.index') }}" class="d-flex align-items-center text-dark font-weight-bold text-decoration-none">
                                    <i class="fas fa-chevron-right mr-2 text-info small"></i> Financial Audit Trail
                                </a>
                            </li>
                            <li class="mb-3">
                                <a href="{{ route('bank-reconciliations.index') }}" class="d-flex align-items-center text-dark font-weight-bold text-decoration-none">
                                    <i class="fas fa-chevron-right mr-2 text-info small"></i> Reconciliation Reports
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .bg-primary-light { background-color: #e0f2fe; }
        .bg-success-light { background-color: #dcfce7; }
        .bg-info-light { background-color: #f0f9ff; }
        .opacity-50 { opacity: 0.5; }
    </style>
@endsection
