<?php

namespace App\Providers;

use App\Models\LeaveApplication;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\User;
use App\Models\Parents;
use App\Models\Module;
use App\Models\Role;
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
use App\Policies\ModulePolicy;
use App\Policies\RolePolicy;
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
        Role::class              => RolePolicy::class,
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
        Module::class            => ModulePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Before any Gate check, test whether the ability name IS a permission
        // name that the user actually holds. This replaces the per-permission
        // Gate definitions (which broke under tests where seeder data arrived
        // after the provider booted) with a single before-hook that handles
        // ALL permission-based abilities.
        // Before-hook: if the ability looks like a dotted permission name and
        // the user holds it, allow immediately.  If the user does NOT hold it
        // we return null (not false) so that policy-based Gates can still
        // grant access for ownership-scoped checks (e.g. portal controllers).
        Gate::before(function (User $user, string $ability) {
            if (str_contains($ability, '.')) {
                if ($user->hasPermission($ability)) {
                    return true;
                }
                return null; // fall through to individual Gate / policy
            }
            return null;
        });

        // Per-permission Gates — these are still registered so that blade
        // directives like @can('exams.view') work correctly.
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                foreach (\App\Models\Permission::pluck('permission_name') as $permission) {
                    if (!Gate::has($permission)) {
                        Gate::define($permission, function (User $user) use ($permission) {
                            return $user->hasPermission($permission);
                        });
                    }
                }
            }
        } catch (\Exception $e) {
            // Gracefully skip if permissions table doesn't exist yet
        }
    }
}
