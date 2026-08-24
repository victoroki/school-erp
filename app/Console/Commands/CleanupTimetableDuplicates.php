<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class CleanupTimetableDuplicates extends Command
{
    protected $signature = 'timetable:cleanup-duplicates {--execute : Actually move rows (default is dry-run)}';
    protected $description = 'Move duplicate timetable rows to a backup table, keeping the lowest timetable_id per group';

    private array $constraints = [
        [
            'name' => 'Teacher Slot',
            'columns' => ['teacher_id', 'day_of_week', 'period_id', 'academic_year_id'],
            'reason' => 'teacher_double_booking',
        ],
        [
            'name' => 'Classroom Slot',
            'columns' => ['classroom_id', 'day_of_week', 'period_id', 'academic_year_id'],
            'reason' => 'classroom_double_booking',
        ],
        [
            'name' => 'Class Section Slot',
            'columns' => ['class_section_id', 'day_of_week', 'period_id', 'academic_year_id'],
            'reason' => 'class_section_double_booking',
        ],
    ];

    public function handle(): int
    {
        $execute = $this->option('execute');
        $dryRun = !$execute;

        if ($dryRun) {
            $this->warn('DRY RUN — no data will be modified. Use --execute to perform the cleanup.');
        } else {
            $this->ensureBackupTableExists();
        }

        $totalMoved = 0;

        foreach ($this->constraints as $constraint) {
            $this->newLine();
            $this->info("═══ {$constraint['name']} ═══");

            $cols = $constraint['columns'];
            $groupBy = implode(', ', $cols);

            $duplicates = DB::table('timetable')
                ->select(DB::raw("{$groupBy}, COUNT(*) as cnt"))
                ->groupBy(DB::raw($groupBy))
                ->havingRaw('COUNT(*) > 1')
                ->get();

            if ($duplicates->isEmpty()) {
                $this->comment('   No duplicates.');
                continue;
            }

            foreach ($duplicates as $group) {
                $query = DB::table('timetable');
                foreach ($cols as $col) {
                    $query->where($col, $group->{$col});
                }

                $rows = $query->orderBy('timetable_id')->get();
                $keep = $rows->first();
                $moveRows = $rows->slice(1);

                $this->newLine();
                $this->info("   Group: {$groupBy}");
                $this->table(
                    ['action', 'timetable_id', 'class_section', 'teacher', 'classroom', 'day', 'period', 'subject', 'year'],
                    array_merge(
                        [['KEEP', $keep->timetable_id, $keep->class_section_id, $keep->teacher_id, $keep->classroom_id, $keep->day_of_week, $keep->period_id, $keep->subject_id, $keep->academic_year_id]],
                        $moveRows->map(fn($r) => ['MOVE → backup', $r->timetable_id, $r->class_section_id, $r->teacher_id, $r->classroom_id, $r->day_of_week, $r->period_id, $r->subject_id, $r->academic_year_id])->toArray()
                    )
                );

                if (!$dryRun) {
                    foreach ($moveRows as $row) {
                        $backupData = (array) $row;
                        $backupData['duplicate_reason'] = $constraint['reason'];
                        $backupData['moved_at'] = now();
                        DB::table('timetable_duplicates_backup')->insert($backupData);
                        DB::table('timetable')->where('timetable_id', $row->timetable_id)->delete();
                        $totalMoved++;
                    }
                } else {
                    $totalMoved += $moveRows->count();
                }
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        if ($dryRun) {
            $this->warn("Would move {$totalMoved} rows to timetable_duplicates_backup.");
            $this->warn('Run with --execute to perform the cleanup.');
        } else {
            $this->info("Moved {$totalMoved} rows to timetable_duplicates_backup.");
        }
        $this->info('═══════════════════════════════════════');

        return 0;
    }

    private function ensureBackupTableExists(): void
    {
        if (Schema::hasTable('timetable_duplicates_backup')) {
            return;
        }

        Schema::create('timetable_duplicates_backup', function (Blueprint $table) {
            $table->integer('timetable_id');
            $table->integer('class_section_id')->nullable();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->integer('period_id')->nullable();
            $table->integer('subject_id')->nullable();
            $table->integer('teacher_id')->nullable();
            $table->integer('classroom_id')->nullable();
            $table->integer('academic_year_id')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('duplicate_reason', 50);
            $table->dateTime('moved_at');
        });

        $this->info('Created timetable_duplicates_backup table.');
    }
}
