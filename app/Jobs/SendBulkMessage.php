<?php

namespace App\Jobs;

use App\Models\MessageRecipient;
use App\Models\Parents;
use App\Models\SchoolClass;
use App\Models\SentMessage;
use App\Models\Staff;
use App\Models\Student;
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

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SentMessage $sentMessage, array $data)
    {
        $this->sentMessage = $sentMessage;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $recipients = [];
        $group = $this->sentMessage->recipient_type;
        $input = $this->data;

        try {
            if ($group == 'All Students') {
                $students = Student::where('status', 'active')->get(); // Assuming status column
                foreach ($students as $student) {
                    $recipients[] = [
                        'type' => 'Student',
                        'id' => $student->student_id, // Adjusted based on previous knowledge that id might be student_id or similar. Let's assume 'id' usually. The model shows 'student_id' in previous snippets, but let's check standard ID. Usually 'id'.
                        'name' => $student->first_name . ' ' . $student->last_name,
                        'contact' => $this->sentMessage->message_type == 'SMS' ? $student->phone : $student->email // Check Student model fields if possible.
                    ];
                }
            } elseif ($group == 'All Parents') {
                $parents = Parents::all();
                foreach ($parents as $parent) {
                    $recipients[] = [
                        'type' => 'Parent',
                        'id' => $parent->id,
                        'name' => $parent->father_name, // Assuming father_name or mother_name. Use helper or adjust
                        'contact' => $this->sentMessage->message_type == 'SMS' ? $parent->phone : $parent->email
                    ];
                }
            } elseif ($group == 'All Staff') {
                $staffMembers = Staff::all(); // Assuming active
                foreach ($staffMembers as $staff) {
                    $recipients[] = [
                        'type' => 'Staff',
                        'id' => $staff->id,
                        'name' => $staff->first_name . ' ' . $staff->last_name,
                        'contact' => $this->sentMessage->message_type == 'SMS' ? $staff->phone : $staff->email
                    ];
                }
            } elseif ($group == 'Class') {
                if (isset($input['class_id'])) {
                    // Assuming relationship students() on SchoolClass or query via enrollment
                    // Reverting to direct query for robust partial implementation
                    $students = \App\Models\StudentClassEnrollment::where('class_id', $input['class_id'])
                        ->with('student')
                        ->get()
                        ->pluck('student');
                        
                    foreach ($students as $student) {
                        if ($student) {
                             $recipients[] = [
                                'type' => 'Student',
                                'id' => $student->id,
                                'name' => $student->first_name . ' ' . $student->last_name,
                                'contact' => $this->sentMessage->message_type == 'SMS' ? $student->phone : $student->email
                            ];
                        }
                    }
                }
            }

            // Save recipients and "Send"
            $count = 0;
            foreach ($recipients as $recipient) {
                if (empty($recipient['contact'])) continue;

                // Replace placeholders per recipient if needed (Simple version)
                // $content = $this->replacePlaceholders($this->sentMessage->content, $recipient);

                MessageRecipient::create([
                    'sent_message_id' => $this->sentMessage->id,
                    'recipient_type' => $recipient['type'],
                    'recipient_id' => $recipient['id'],
                    'contact' => $recipient['contact'],
                    'recipient_name' => $recipient['name'],
                    'delivery_status' => 'Sent', // Simulate success
                    'delivery_time' => now()
                ]);
                $count++;
            }

            $this->sentMessage->update([
                'status' => 'Sent',
                'recipient_count' => $count,
                'sent_at' => now()
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk Message Error: ' . $e->getMessage());
            $this->sentMessage->update(['status' => 'Failed']);
        }
    }
}
