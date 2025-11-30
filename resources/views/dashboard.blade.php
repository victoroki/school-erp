@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Date Range Filter -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Dashboard Filters</h5>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-range="today">Today</button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-range="week">Week</button>
                                <button type="button" class="btn btn-outline-primary btn-sm" data-range="month">Month</button>
                                <button type="button" class="btn btn-outline-primary btn-sm active" data-range="year">Year</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="total-students">0</h3>
                        <p>Total Students</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <a href="{{ route('students.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="total-teachers">0</h3>
                        <p>Total Teachers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <a href="{{ route('staff.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="total-classes">0</h3>
                        <p>Total Classes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <a href="{{ route('school-classes.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="monthly-revenue">₵0</h3>
                        <p>Monthly Revenue</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <a href="{{ route('fee-management.index') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Student Enrollment Trend</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="enrollmentChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Class Distribution</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="classDistributionChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Quick Actions -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activities</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Activity</th>
                                        <th>Date</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-activities">
                                    <tr>
                                        <td colspan="3" class="text-center">Loading activities...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <button class="btn btn-primary btn-block" id="add-student-btn">
                                    <i class="fas fa-user-plus mr-2"></i>Add Student
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button class="btn btn-success btn-block" id="record-attendance-btn">
                                    <i class="fas fa-user-check mr-2"></i>Record Attendance
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button class="btn btn-warning btn-block" id="fee-collection-btn">
                                    <i class="fas fa-money-bill-wave mr-2"></i>Fee Collection
                                </button>
                            </div>
                            <div class="col-md-6 mb-3">
                                <button class="btn btn-info btn-block" id="generate-report-btn">
                                    <i class="fas fa-file-alt mr-2"></i>Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notices & Schedule -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Important Notices</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="notices-list">
                            <li class="list-group-item text-center">Loading notices...</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Today's Schedule</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" id="schedule-list">
                            <li class="list-group-item text-center">Loading schedule...</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize charts
    let enrollmentChart, classDistributionChart;
    
    // Initialize dashboard
    document.addEventListener('DOMContentLoaded', function() {
        // Store route URLs in JavaScript variables
        const studentCreateUrl = "{{ route('students.create') }}";
        const feeManagementUrl = "{{ route('fee-management.index') }}";
        // Set up date range filters
        document.querySelectorAll('[data-range]').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('[data-range]').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                loadDashboardData(this.dataset.range);
            });
        });
        
        // Load initial data
        loadDashboardData('year');
        
        // Set up quick action buttons
        document.getElementById('add-student-btn').addEventListener('click', function() {
            window.location.href = studentCreateUrl;
        });
        
        document.getElementById('record-attendance-btn').addEventListener('click', function() {
            // Implement attendance recording functionality
            alert('Attendance recording feature will be implemented');
        });
        
        document.getElementById('fee-collection-btn').addEventListener('click', function() {
            window.location.href = feeManagementUrl;
        });
        
        document.getElementById('generate-report-btn').addEventListener('click', function() {
            // Implement report generation functionality
            alert('Report generation feature will be implemented');
        });
    });
    
    function loadDashboardData(range) {
        fetch(`/dashboard/data?range=${range}`)
            .then(response => response.json())
            .then(data => {
                // Update statistics
                document.getElementById('total-students').textContent = data.statistics.total_students.toLocaleString();
                document.getElementById('total-teachers').textContent = data.statistics.total_teachers;
                document.getElementById('total-classes').textContent = data.statistics.total_classes;
                document.getElementById('monthly-revenue').textContent = '₵' + data.statistics.monthly_revenue.toLocaleString();
                
                // Update charts
                updateEnrollmentChart(data.charts.enrollment_trend);
                updateClassDistributionChart(data.charts.class_distribution);
                
                // Update recent activities
                updateRecentActivities(data.activities);
                
                // Update notices
                updateNotices(data.notices);
                
                // Update schedule
                updateSchedule(data.schedule);
            })
            .catch(error => {
                console.error('Error loading dashboard data:', error);
                // Fallback to mock data if there's an error
                loadMockData();
            });
    }
    
    function loadMockData() {
        // Update statistics
        document.getElementById('total-students').textContent = '1,248';
        document.getElementById('total-teachers').textContent = '42';
        document.getElementById('total-classes').textContent = '18';
        document.getElementById('monthly-revenue').textContent = '₵42,500';
        
        // Update charts with mock data
        updateEnrollmentChart({
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            data: [1120, 1150, 1180, 1200, 1230, 1248]
        });
        
        updateClassDistributionChart({
            labels: ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'],
            data: [210, 205, 198, 202, 195, 188]
        });
        
        // Update recent activities
        updateRecentActivities([
            { activity: 'New student admission', date: '2 hours ago', user: 'Admin' },
            { activity: 'Fee payment received', date: '4 hours ago', user: 'Finance' },
            { activity: 'Exam results published', date: '1 day ago', user: 'Academic' },
            { activity: 'Teacher attendance recorded', date: '1 day ago', user: 'HR' },
            { activity: 'Library book issued', date: '2 days ago', user: 'Librarian' }
        ]);
        
        // Update notices
        updateNotices([
            { title: 'School Closure Notice', content: 'School will be closed on Monday for maintenance.', priority: 'High', posted_at: '2 days ago' },
            { title: 'Parent-Teacher Meeting', content: 'PTM scheduled for next Friday at 2 PM.', priority: 'Medium', posted_at: '3 days ago' },
            { title: 'Sports Day Announcement', content: 'Annual sports day on 15th of this month.', priority: 'Low', posted_at: '1 week ago' }
        ]);
        
        // Update schedule
        updateSchedule([
            { title: 'Morning Assembly', time: '8:00 AM - 8:30 AM', location: 'Main Hall' },
            { title: 'Grade 1 Math Class', time: '9:00 AM - 10:00 AM', location: 'Room 101' },
            { title: 'Grade 2 Science', time: '10:30 AM - 11:30 AM', location: 'Lab 2' },
            { title: 'Lunch Break', time: '12:00 PM - 1:00 PM', location: 'Cafeteria' }
        ]);
    }
    
    function initializeCharts() {
        // Enrollment Trend Chart
        const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
        enrollmentChart = new Chart(enrollmentCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Student Enrollment',
                    data: [],
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Class Distribution Chart
        const classDistributionCtx = document.getElementById('classDistributionChart').getContext('2d');
        classDistributionChart = new Chart(classDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#007bff',
                        '#28a745',
                        '#ffc107',
                        '#dc3545',
                        '#6f42c1',
                        '#17a2b8'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    
    function updateEnrollmentChart(data) {
        if (enrollmentChart) {
            enrollmentChart.data.labels = data.labels;
            enrollmentChart.data.datasets[0].data = data.data;
            enrollmentChart.update();
        }
    }
    
    function updateClassDistributionChart(data) {
        if (classDistributionChart) {
            classDistributionChart.data.labels = data.labels;
            classDistributionChart.data.datasets[0].data = data.data;
            classDistributionChart.update();
        }
    }
    
    function updateRecentActivities(activities) {
        let activitiesHtml = '';
        
        if (activities && activities.length > 0) {
            activities.forEach(activity => {
                activitiesHtml += `
                    <tr>
                        <td>${activity.activity}</td>
                        <td>${activity.date}</td>
                        <td>${activity.user}</td>
                    </tr>
                `;
            });
        } else {
            activitiesHtml = '<tr><td colspan="3" class="text-center">No recent activities</td></tr>';
        }
        
        document.getElementById('recent-activities').innerHTML = activitiesHtml;
    }
    
    function updateNotices(notices) {
        let noticesHtml = '';
        
        if (notices && notices.length > 0) {
            notices.forEach(notice => {
                let badgeClass = 'badge-info';
                if (notice.priority === 'High') badgeClass = 'badge-danger';
                else if (notice.priority === 'Medium') badgeClass = 'badge-warning';
                
                noticesHtml += `
                    <li class="list-group-item">
                        <span class="badge ${badgeClass} float-right">${notice.priority}</span>
                        <h6 class="mb-1">${notice.title}</h6>
                        <p class="mb-1">${notice.content}</p>
                        <small class="text-muted">Posted ${notice.posted_at}</small>
                    </li>
                `;
            });
        } else {
            noticesHtml = '<li class="list-group-item text-center">No notices available</li>';
        }
        
        document.getElementById('notices-list').innerHTML = noticesHtml;
    }
    
    function updateSchedule(schedule) {
        let scheduleHtml = '';
        
        if (schedule && schedule.length > 0) {
            schedule.forEach(item => {
                scheduleHtml += `
                    <li class="list-group-item">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${item.title}</h6>
                            <small>${item.time}</small>
                        </div>
                        <p class="mb-1">${item.location}</p>
                    </li>
                `;
            });
        } else {
            scheduleHtml = '<li class="list-group-item text-center">No schedule available</li>';
        }
        
        document.getElementById('schedule-list').innerHTML = scheduleHtml;
    }
</script>
@endsection