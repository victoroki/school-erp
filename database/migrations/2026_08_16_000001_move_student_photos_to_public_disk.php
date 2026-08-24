<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Move student photos from public/students/photos into public/uploads/student_photos.
 *
 * Why: the public/students directory collides with the GET /students route.
 * PHP's built-in server (`php artisan serve`) and typical Apache/Nginx
 * configs treat an existing path as a static resource and never forward it
 * to Laravel's front controller, so /students 404s.
 *
 * The new location is a plain folder under public/ (uploads/student_photos),
 * so it is directly servable WITHOUT the storage:link symlink — which cannot
 * be created on shared cPanel hosting without terminal/SSH access.
 */
return new class extends Migration
{
    public function up(): void
    {
        $oldDir = public_path('students/photos');
        $newDir = public_path('uploads/student_photos');

        if (! is_dir($newDir)) {
            @mkdir($newDir, 0775, true);
        }

        $moveFile = function (string $fileName) use ($oldDir, $newDir): bool {
            $source = $oldDir.DIRECTORY_SEPARATOR.$fileName;
            $dest = $newDir.DIRECTORY_SEPARATOR.$fileName;

            if (! is_file($source) || file_exists($dest)) {
                return false;
            }

            return @rename($source, $dest);
        };

        $moved = 0;

        // Rewrite DB rows and move the referenced files.
        DB::table('students')
            ->where('photo_url', 'like', 'students/photos/%')
            ->orderBy('student_id')
            ->get()
            ->each(function ($student) use ($moveFile, &$moved) {
                $fileName = basename($student->photo_url);

                if ($moveFile($fileName)) {
                    $moved++;
                }

                DB::table('students')
                    ->where('student_id', $student->student_id)
                    ->update(['photo_url' => 'student_photos/'.$fileName]);
            });

        // Move any orphaned files (no longer referenced by a student row).
        if (is_dir($oldDir)) {
            foreach (scandir($oldDir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                if ($moveFile($file)) {
                    $moved++;
                }
            }

            @rmdir($oldDir);
        }

        @rmdir(public_path('students'));
    }

    public function down(): void
    {
        $newDir = public_path('uploads/student_photos');
        $oldDir = public_path('students/photos');

        if (! is_dir($oldDir)) {
            @mkdir($oldDir, 0775, true);
        }

        $moveBack = function (string $fileName) use ($oldDir, $newDir): bool {
            $source = $newDir.DIRECTORY_SEPARATOR.$fileName;
            $dest = $oldDir.DIRECTORY_SEPARATOR.$fileName;

            if (! is_file($source) || file_exists($dest)) {
                return false;
            }

            return @rename($source, $dest);
        };

        DB::table('students')
            ->where('photo_url', 'like', 'student_photos/%')
            ->orderBy('student_id')
            ->get()
            ->each(function ($student) use ($moveBack) {
                $fileName = basename($student->photo_url);
                $moveBack($fileName);

                DB::table('students')
                    ->where('student_id', $student->student_id)
                    ->update(['photo_url' => 'students/photos/'.$fileName]);
            });

        if (is_dir($newDir)) {
            foreach (scandir($newDir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $moveBack($file);
            }
        }
    }
};
