<?php

namespace App\Console\Commands;

use App\Models\CommunicationLog;
use App\Models\CommunicationTrigger;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Services\Communication\NotificationDispatcher;
use Illuminate\Console\Command;

class SendFeeReminders extends Command
{
    protected $signature = 'communication:fee-reminders {--dry-run}';
    protected $description = 'Send fee payment reminder notifications to parents of students with outstanding balances';

    public function handle(NotificationDispatcher $dispatcher): int
    {
        if (!CommunicationTrigger::isEnabled('fee_reminder')) {
            $this->info('Fee reminder trigger is disabled. Skipping.');
            return self::SUCCESS;
        }

        $assignments = StudentFeeAssignment::active()
            ->whereRaw('(final_amount - paid_amount) > 0')
            ->with(['student.parents', 'feeStructure'])
            ->get();

        $studentIds = $assignments->pluck('student_id')->unique();
        $sent = 0;
        $skipped = 0;

        foreach ($studentIds as $studentId) {
            $studentAssignments = $assignments->where('student_id', $studentId);
            $totalBalance = $studentAssignments->sum(fn($a) => $a->final_amount - $a->paid_amount);

            $student = Student::find($studentId);
            if (!$student) continue;

            $context = [
                'fee_balance' => 'KES ' . number_format($totalBalance, 2),
                'fee_total' => 'KES ' . number_format($studentAssignments->sum('final_amount'), 2),
                'fee_paid' => 'KES ' . number_format($studentAssignments->sum('paid_amount'), 2),
                'term' => $studentAssignments->first()?->term ?? '',
            ];

            if ($this->option('dry-run')) {
                $this->line("DRY RUN: Would send fee reminder to parents of {$student->full_name} (Balance: {$context['fee_balance']})");
                $skipped++;
                continue;
            }

            $result = $dispatcher->dispatchToParents(
                'fee_reminder',
                $studentId,
                Student::class,
                $studentId,
                $context
            );

            $sent += $result['sent'];
            $skipped += $result['skipped'];
        }

        $this->info("Fee reminders: {$sent} sent, {$skipped} skipped");
        return self::SUCCESS;
    }
}
