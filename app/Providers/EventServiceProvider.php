<?php

namespace App\Providers;

use App\Events\NotificationConfirmed;
use App\Events\NotificationTriggered;
use App\Listeners\DispatchPendingNotification;
use App\Listeners\QueueAutoNotification;
use App\Models\DisciplinaryRecord;
use App\Models\MedicalIncident;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Observers\DisciplinaryRecordObserver;
use App\Observers\ExamResultObserver;
use App\Observers\MedicalIncidentObserver;
use App\Observers\StudentClassEnrollmentObserver;
use App\Observers\StudentObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        NotificationTriggered::class => [
            QueueAutoNotification::class,
        ],
        NotificationConfirmed::class => [
            DispatchPendingNotification::class,
        ],
    ];

    protected $observers = [
        Student::class => [StudentObserver::class],
        StudentClassEnrollment::class => [StudentClassEnrollmentObserver::class],
        MedicalIncident::class => [MedicalIncidentObserver::class],
        DisciplinaryRecord::class => [DisciplinaryRecordObserver::class],
        ExamResult::class => [ExamResultObserver::class],
    ];

    public function boot(): void
    {
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
