<?php

namespace App\Jobs;

use App\Models\MessageRecipient;
use App\Models\Parents;
use App\Models\SchoolClass;
use App\Models\SentMessage;
use App\Models\Staff;
use App\Models\Student;
use App\Services\Communication\PhoneHelper;
use App\Services\Communication\TemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $sentMessage;
    protected $data;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(SentMessage $sentMessage, array $data)
    {
        $this->sentMessage = $sentMessage;
        $this->data = $data;
    }

    public function handle()
    {
        $recipients = [];
        $group = $this->sentMessage->recipient_type;
        $input = $this->data;

        try {
            if ($group == 'All Students') {
                $students = Student::where('status', 'active')->get();
                foreach ($students as $student) {
                    $recipients[] = [
                        'type' => 'Student',
                        'id' => $student->student_id,
                        'name' => $student->full_name,
                        'contact' => $this->sentMessage->message_type == 'SMS' ? $student->phone : $student->email,
                        'student_name' => $student->full_name,
                        'student_class' => $student->currentEnrollment?->classSection?->schoolClass?->name ?? '',
                    ];
                }
            } elseif ($group == 'All Parents') {
                $parents = Parents::all();
                foreach ($parents as $parent) {
                    $recipients[] = [
                        'type' => 'Parent',
                        'id' => $parent->parent_id,
                        'name' => trim($parent->first_name . ' ' . $parent->last_name),
                        'contact' => $this->sentMessage->message_type == 'SMS' ? $parent->phone : $parent->email,
                    ];
                }
            } elseif ($group == 'All Staff') {
                $staffMembers = Staff::all();
                foreach ($staffMembers as $staff) {
                    $recipients[] = [
                        'type' => 'Staff',
                        'id' => $staff->staff_id,
                        'name' => trim($staff->first_name . ' ' . $staff->last_name),
                        'contact' => $this->sentMessage->message_type == 'SMS' ? $staff->phone : $staff->email,
                    ];
                }
            } elseif ($group == 'Class') {
                if (isset($input['class_id'])) {
                    $students = \App\Models\StudentClassEnrollment::whereHas('classSection', function ($q) use ($input) {
                        $q->where('class_id', $input['class_id']);
                    })->with(['student', 'classSection.schoolClass'])->get();

                    foreach ($students as $enrollment) {
                        $student = $enrollment->student;
                        if ($student) {
                            $recipients[] = [
                                'type' => 'Student',
                                'id' => $student->student_id,
                                'name' => $student->full_name,
                                'contact' => $this->sentMessage->message_type == 'SMS' ? $student->phone : $student->email,
                                'student_name' => $student->full_name,
                                'student_class' => $enrollment->classSection?->schoolClass?->name ?? '',
                            ];
                        }
                    }
                }
            }

            $count = 0;
            $totalCost = 0;

            foreach ($recipients as $recipient) {
                if (empty($recipient['contact'])) continue;

                $rendered = TemplateRenderer::render($this->sentMessage->content, $recipient);
                $formattedContact = $this->sentMessage->message_type == 'SMS'
                    ? PhoneHelper::formatForSms($recipient['contact'])
                    : $recipient['contact'];

                if (!$formattedContact) continue;

                $deliveryStatus = 'Pending';

                if ($this->sentMessage->message_type == 'SMS') {
                    try {
                        $provider = app(\App\Services\Communication\SmsProviderInterface::class);
                        $result = $provider->send($formattedContact, $rendered);
                        $deliveryStatus = $result->success ? 'Sent' : 'Failed';
                        if ($result->cost) {
                            $totalCost += $result->cost;
                        }
                    } catch (\Exception $e) {
                        $deliveryStatus = 'Failed';
                        Log::error('SMS send failed', ['contact' => $formattedContact, 'error' => $e->getMessage()]);
                    }
                } else {
                    $deliveryStatus = 'Sent';
                }

                MessageRecipient::create([
                    'sent_message_id' => $this->sentMessage->id,
                    'recipient_type' => $recipient['type'],
                    'recipient_id' => $recipient['id'],
                    'contact' => $formattedContact,
                    'recipient_name' => $recipient['name'],
                    'delivery_status' => $deliveryStatus,
                    'delivery_time' => $deliveryStatus === 'Sent' ? now() : null,
                ]);
                $count++;
            }

            $this->sentMessage->update([
                'status' => 'Sent',
                'recipient_count' => $count,
                'cost' => $totalCost,
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk Message Error: ' . $e->getMessage());
            $this->sentMessage->update(['status' => 'Failed']);
        }
    }
}
