<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Production-safe seed run on EVERY deployment by public/deploy-hook.php.
 *
 * Only system/reference data: permissions, roles, modules, the platform owner
 * account (from SAAS_OWNER_* secrets) and default lookup lists. Every seeder
 * here updates or skips existing rows, so re-running it never duplicates data
 * and never touches a school's own records. Demo data (students, staff, exam
 * results, fees…) lives in DatabaseSeeder and is never run in production.
 */
class SystemSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RbacSeeder::class,
            ModuleSeeder::class,
            OwnerSeeder::class,
            GradingScalesSeeder::class,
            ExamTypesSeeder::class,
            CommunicationTriggerSeeder::class,
            CommunicationTemplateSeeder::class,
            SchoolMessageTemplateSeeder::class,
        ]);
    }
}
