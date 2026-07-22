<?php

namespace App\Providers;

use App\Models\LeaveApplication;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\User;
use App\Models\Parents;
use App\Policies\AcademicsPolicy;
use App\Policies\CommunicationPolicy;
use App\Policies\DisciplinePolicy;
use App\Policies\ExamPolicy;
use App\Policies\FeePolicy;
use App\Policies\FinancePolicy;
use App\Policies\HostelPolicy;
use App\Policies\HrPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\LibraryPolicy;
use App\Policies\ParentPolicy;
use App\Policies\StudentPolicy;
use App\Policies\TransportPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class              => UserPolicy::class,
        Student::class           => StudentPolicy::class,
        'academics'              => AcademicsPolicy::class,
        'exam'                   => ExamPolicy::class,
        FeePayment::class        => FeePolicy::class,
        'finance'                => FinancePolicy::class,
        LeaveApplication::class  => HrPolicy::class,
        'inventory'              => InventoryPolicy::class,
        'library'                => LibraryPolicy::class,
        'hostel'                 => HostelPolicy::class,
        'transport'              => TransportPolicy::class,
        'communication'          => CommunicationPolicy::class,
        'discipline'             => DisciplinePolicy::class,
        Parents::class           => ParentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Dynamic Gate definitions — created for every permission_name in the DB.
        // This allows `@can('users.manage')` in blades and
        // `$this->authorize('users.manage')` in controllers to work.
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                foreach (\App\Models\Permission::pluck('permission_name') as $permission) {
                    Gate::define($permission, function (User $user) use ($permission) {
                        return $user->hasPermission($permission);
                    });
                }
            }
        } catch (\Exception $e) {
            // Gracefully skip if permissions table doesn't exist yet (e.g. during migrations)
        }
    }
}
