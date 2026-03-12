Revamping Communication Module - Implementation Summary
=========================================================

1. Database Enhancements
------------------------
- Created `template_categories` table for organizing message templates.
- Created `sent_messages` table to track all bulk and single messages sent.
- Created `message_recipients` table to track individual delivery status for each recipient.
- Enhanced `sms_templates` and `email_templates` tables with `category`, `status`, `usage_count`, and `created_by` fields.

2. New Models
-------------
- `TemplateCategory`
- `SentMessage`
- `MessageRecipient`
- Updated `SmsTemplate` and `EmailTemplate`

3. Controllers
--------------
- `CommunicationController`: Handles the core logic for composing, previewing, sending, and viewing history.
- `CommunicationDashboardController`: Provides a statistical overview of communication activities.
- `SmsTemplateController` & `EmailTemplateController`: Updated to support new fields, categorization, and status management.

4. Views (UI/UX)
----------------
- **Communication Dashboard**: Shows stats on sent messages, popular templates, and recent logs.
- **Compose Message**: A robust interface to toggle between SMS/Email, select target groups (Students, Parents, Staff, Classes), pick templates, and preview content with dynamic character counting.
- **Message History**: A detailed log of all communication with status tracking (Sent, Failed, Scheduled).
- **Template Management**: Refactored indices and forms for SMS and Email templates to be more user-friendly and functional.

5. Background Processing
------------------------
- Implemented `SendBulkMessage` Job: Handles the heavy lifting of fetching recipients (e.g., all students in a class) and creating delivery records in the background, ensuring the UI remains responsive.

6. Sidebar Navigation
---------------------
- Reorganized the "Communication" menu to include easier access to Dashboard, Compose, History, and Templates.

Next Steps for Production:
- Configure actual SMS Gateway credentials in `.env` and `Services`.
- Setup SMTP for Email sending.
- Run a queue worker (`php artisan queue:work`) to process the background jobs.
