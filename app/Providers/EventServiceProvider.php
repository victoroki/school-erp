<?php

namespace App\Providers;

use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Observers\StudentObserver;
use App\Observers\StudentClassEnrollmentObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    protected $observers = [
        Student::class => [StudentObserver::class],
        StudentClassEnrollment::class => [StudentClassEnrollmentObserver::class],
    ];

    public function boot(): void
    {
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
