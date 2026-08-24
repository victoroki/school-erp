<?php

namespace App\Observers;

use App\Events\NotificationTriggered;
use App\Models\DisciplinaryRecord;

class DisciplinaryRecordObserver
{
    public function created(DisciplinaryRecord $record): void
    {
        event(new NotificationTriggered(
            triggerType: 'disciplinary',
            studentId: $record->student_id,
            triggerModel: DisciplinaryRecord::class,
            triggerId: $record->disciplinary_record_id,
            context: [
                'incident_date' => $record->incident_date?->format('d/m/Y') ?? '',
                'incident_type' => $record->incident_type ?? '',
                'incident_description' => $record->description ?? '',
                'action_taken' => $record->action_taken ?? '',
            ]
        ));
    }
}
