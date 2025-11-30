<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;

class CommunicationSeeder extends Seeder
{
    public function run()
    {
        // Get some users for sender/receiver
        $users = User::limit(10)->get();
        
        if ($users->count() >= 2) {
            // Create sample notifications
            $notifications = [
                [
                    'title' => 'School Holiday Announcement',
                    'message' => 'Dear parents and students, please note that the school will be closed next Monday for a public holiday.',
                    'type' => 'announcement',
                    'recipient_type' => 'all',
                    'sender_id' => $users[0]->id,
                    'created_at' => Carbon::now()->subDays(5),
                    'updated_at' => Carbon::now()->subDays(5)
                ],
                [
                    'title' => 'Exam Schedule Update',
                    'message' => 'The final exam schedule has been updated. Please check the notice board for details.',
                    'type' => 'exam',
                    'recipient_type' => 'students',
                    'sender_id' => $users[0]->id,
                    'created_at' => Carbon::now()->subDays(3),
                    'updated_at' => Carbon::now()->subDays(3)
                ],
                [
                    'title' => 'Parent-Teacher Meeting',
                    'message' => 'Reminder: Parent-teacher meetings will be held next week. Please book your slot.',
                    'type' => 'general',
                    'recipient_type' => 'parents',
                    'sender_id' => $users[0]->id,
                    'created_at' => Carbon::now()->subDays(2),
                    'updated_at' => Carbon::now()->subDays(2)
                ],
                [
                    'title' => 'Staff Meeting Notice',
                    'message' => 'All staff members are requested to attend the monthly meeting tomorrow at 3 PM.',
                    'type' => 'general',
                    'recipient_type' => 'staff',
                    'sender_id' => $users[0]->id,
                    'created_at' => Carbon::now()->subDays(1),
                    'updated_at' => Carbon::now()->subDays(1)
                ]
            ];

            foreach ($notifications as $notificationData) {
                Notification::firstOrCreate([
                    'title' => $notificationData['title'],
                    'sender_id' => $notificationData['sender_id']
                ], $notificationData);
            }

            // Create sample messages
            $messages = [
                [
                    'sender_id' => $users[0]->id,
                    'receiver_id' => $users[1]->id,
                    'subject' => 'Regarding Assignment Submission',
                    'message' => 'Hi, I wanted to remind you about the assignment submission deadline which is tomorrow.',
                    'is_read' => false,
                    'created_at' => Carbon::now()->subHours(6),
                    'updated_at' => Carbon::now()->subHours(6)
                ],
                [
                    'sender_id' => $users[1]->id,
                    'receiver_id' => $users[0]->id,
                    'subject' => 'Re: Regarding Assignment Submission',
                    'message' => 'Thank you for the reminder. I\'ll make sure to submit it on time.',
                    'is_read' => true,
                    'read_at' => Carbon::now()->subHours(5),
                    'created_at' => Carbon::now()->subHours(5),
                    'updated_at' => Carbon::now()->subHours(5)
                ],
                [
                    'sender_id' => $users[0]->id,
                    'receiver_id' => $users[1]->id,
                    'subject' => 'Meeting Request',
                    'message' => 'Can we schedule a meeting to discuss the upcoming project?',
                    'is_read' => false,
                    'created_at' => Carbon::now()->subHours(2),
                    'updated_at' => Carbon::now()->subHours(2)
                ]
            ];

            foreach ($messages as $messageData) {
                Message::firstOrCreate([
                    'sender_id' => $messageData['sender_id'],
                    'receiver_id' => $messageData['receiver_id'],
                    'subject' => $messageData['subject']
                ], $messageData);
            }
        }
    }
}