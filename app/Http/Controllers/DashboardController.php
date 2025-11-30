<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\FeePayment;
use App\Models\ExamResult;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function getData(Request $request)
    {
        $range = $request->get('range', 'year');
        
        // Get statistics
        $statistics = $this->getStatistics();
        
        // Get chart data
        $chartData = $this->getChartData($range);
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        // Get notices
        $notices = $this->getNotices();
        
        // Get schedule
        $schedule = $this->getSchedule();
        
        return response()->json([
            'statistics' => $statistics,
            'charts' => $chartData,
            'activities' => $recentActivities,
            'notices' => $notices,
            'schedule' => $schedule
        ]);
    }

    private function getStatistics()
    {
        $totalStudents = Student::count();
        $totalTeachers = User::whereHas('roles', function($query) {
            $query->where('name', 'teacher');
        })->count();
        $totalClasses = SchoolClass::count();
        $monthlyRevenue = FeePayment::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        return [
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_classes' => $totalClasses,
            'monthly_revenue' => $monthlyRevenue
        ];
    }

    private function getChartData($range)
    {
        $enrollmentData = $this->getEnrollmentTrend($range);
        $classDistribution = $this->getClassDistribution();

        return [
            'enrollment_trend' => $enrollmentData,
            'class_distribution' => $classDistribution
        ];
    }

    private function getEnrollmentTrend($range)
    {
        $months = [];
        $counts = [];
        
        switch ($range) {
            case 'today':
                // Get hourly data for today
                for ($i = 0; $i < 24; $i++) {
                    $hour = Carbon::today()->addHours($i);
                    $months[] = $hour->format('H:00');
                    $counts[] = Student::whereDate('created_at', Carbon::today())
                        ->whereHour('created_at', $i)
                        ->count();
                }
                break;
                
            case 'week':
                // Get daily data for the week
                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $months[] = $date->format('D');
                    $counts[] = Student::whereDate('created_at', $date)->count();
                }
                break;
                
            case 'month':
                // Get daily data for the month
                for ($i = 30; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $months[] = $date->format('M d');
                    $counts[] = Student::whereDate('created_at', $date)->count();
                }
                break;
                
            case 'year':
            default:
                // Get monthly data for the year
                for ($i = 11; $i >= 0; $i--) {
                    $month = Carbon::today()->subMonths($i);
                    $months[] = $month->format('M');
                    $counts[] = Student::whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->count();
                }
                break;
        }

        return [
            'labels' => $months,
            'data' => $counts
        ];
    }

    private function getClassDistribution()
    {
        $classes = SchoolClass::withCount('students')->get();
        
        $labels = [];
        $data = [];
        
        foreach ($classes as $class) {
            $labels[] = $class->name;
            $data[] = $class->students_count;
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getRecentActivities()
    {
        // In a real application, you would fetch actual activities from your models
        // For now, we'll return sample data but in a production environment, you would do something like:
        // $activities = ActivityLog::latest()->limit(10)->get();
        
        return [
            [
                'activity' => 'New student admission',
                'date' => '2 hours ago',
                'user' => 'Admin'
            ],
            [
                'activity' => 'Fee payment received',
                'date' => '4 hours ago',
                'user' => 'Finance'
            ],
            [
                'activity' => 'Exam results published',
                'date' => '1 day ago',
                'user' => 'Academic'
            ],
            [
                'activity' => 'Teacher attendance recorded',
                'date' => '1 day ago',
                'user' => 'HR'
            ],
            [
                'activity' => 'Library book issued',
                'date' => '2 days ago',
                'user' => 'Librarian'
            ]
        ];
    }

    private function getNotices()
    {
        // In a real application, you would fetch actual notices from your models
        // For now, we'll return sample data but in a production environment, you would do something like:
        // $notices = Notice::orderBy('priority', 'desc')->orderBy('created_at', 'desc')->limit(5)->get();
        
        return [
            [
                'title' => 'School Closure Notice',
                'content' => 'School will be closed on Monday for maintenance.',
                'priority' => 'High',
                'posted_at' => '2 days ago'
            ],
            [
                'title' => 'Parent-Teacher Meeting',
                'content' => 'PTM scheduled for next Friday at 2 PM.',
                'priority' => 'Medium',
                'posted_at' => '3 days ago'
            ],
            [
                'title' => 'Sports Day Announcement',
                'content' => 'Annual sports day on 15th of this month.',
                'priority' => 'Low',
                'posted_at' => '1 week ago'
            ]
        ];
    }

    private function getSchedule()
    {
        // In a real application, you would fetch actual schedule from your models
        // For now, we'll return sample data but in a production environment, you would do something like:
        // $schedule = Schedule::whereDate('date', Carbon::today())->orderBy('start_time')->get();
        
        return [
            [
                'title' => 'Morning Assembly',
                'time' => '8:00 AM - 8:30 AM',
                'location' => 'Main Hall'
            ],
            [
                'title' => 'Grade 1 Math Class',
                'time' => '9:00 AM - 10:00 AM',
                'location' => 'Room 101'
            ],
            [
                'title' => 'Grade 2 Science',
                'time' => '10:30 AM - 11:30 AM',
                'location' => 'Lab 2'
            ],
            [
                'title' => 'Lunch Break',
                'time' => '12:00 PM - 1:00 PM',
                'location' => 'Cafeteria'
            ]
        ];
    }
}