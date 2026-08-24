<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    /**
     * The modules shown in the sidebar and gateable via the Modules screen.
     *
     * Core modules (dashboard, user-management, academics, students,
     * administration) are always on and cannot be disabled. The remaining
     * modules can be toggled per school installation.
     *
     * Uses updateOrCreate for idempotency — re-running resets any module that
     * was disabled back to its default enabled state.
     */
    private array $modules = [
        ['key' => 'dashboard',        'name' => 'Dashboard',          'icon' => 'fas fa-tachometer-alt', 'route_prefix' => 'home',               'is_core' => true,  'order' => 10],
        ['key' => 'user-management',  'name' => 'User Management',    'icon' => 'fas fa-user-shield',    'route_prefix' => 'users',              'is_core' => true,  'order' => 20],
        ['key' => 'academics',        'name' => 'Academic Management', 'icon' => 'fas fa-graduation-cap', 'route_prefix' => 'academics',          'is_core' => true,  'order' => 30],
        ['key' => 'academic-teacher-management', 'name' => 'Teacher Management', 'icon' => 'fas fa-user-tie', 'route_prefix' => 'teacher-management', 'is_core' => false, 'order' => 35],
        ['key' => 'students',         'name' => 'Student Management', 'icon' => 'fas fa-user-graduate',  'route_prefix' => 'students',           'is_core' => true,  'order' => 40],
        ['key' => 'exams',            'name' => 'Examinations',       'icon' => 'fas fa-file-invoice',   'route_prefix' => 'exams',              'is_core' => false, 'order' => 50],
        ['key' => 'inventory',        'name' => 'Inventory',          'icon' => 'fas fa-boxes',          'route_prefix' => 'inventory',          'is_core' => false, 'order' => 60],
        ['key' => 'library',          'name' => 'Library',            'icon' => 'fas fa-book',           'route_prefix' => 'library',            'is_core' => false, 'order' => 70],
        ['key' => 'fees',             'name' => 'Fees',               'icon' => 'fas fa-coins',          'route_prefix' => 'fees',               'is_core' => false, 'order' => 80],
        ['key' => 'hr',               'name' => 'Human Resources',    'icon' => 'fas fa-user-tie',       'route_prefix' => 'hr',                 'is_core' => false, 'order' => 90],
        ['key' => 'finance',          'name' => 'Finance',            'icon' => 'fas fa-chart-line',     'route_prefix' => 'finance',            'is_core' => false, 'order' => 100],
        ['key' => 'hostel',           'name' => 'Hostels',            'icon' => 'fas fa-hotel',          'route_prefix' => 'hostel',             'is_core' => false, 'order' => 110],
        ['key' => 'transport',        'name' => 'Transport',          'icon' => 'fas fa-bus-alt',        'route_prefix' => 'transportation',     'is_core' => false, 'order' => 120],
        ['key' => 'communication',    'name' => 'Communication',      'icon' => 'fas fa-comments',       'route_prefix' => 'communication',      'is_core' => false, 'order' => 130],
        ['key' => 'administration',   'name' => 'Administration',     'icon' => 'fas fa-shield-alt',     'route_prefix' => 'administration',     'is_core' => true,  'order' => 140],
    ];

    public function run(): void
    {
        foreach ($this->modules as $module) {
            Module::updateOrCreate(
                ['key' => $module['key']],
                [
                    'name' => $module['name'],
                    'icon' => $module['icon'],
                    'route_prefix' => $module['route_prefix'],
                    'is_core' => $module['is_core'],
                    'is_active' => true,
                    'order' => $module['order'],
                ]
            );
        }
    }
}
