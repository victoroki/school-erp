<?php

use App\Models\School;

if (!function_exists('school_name')) {
    /**
     * The onboarded school's name, falling back to the configured app name
     * until the one-time setup has been completed.
     */
    function school_name(): string
    {
        try {
            $name = School::query()->value('name');
            return $name ?: config('app.name');
        } catch (\Throwable $e) {
            return config('app.name');
        }
    }
}
