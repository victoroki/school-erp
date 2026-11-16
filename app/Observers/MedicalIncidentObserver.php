<?php

namespace App\Observers;

use App\Events\NotificationTriggered;
use App\Models\MedicalIncident;

class MedicalIncidentObserver
{
    public function created(MedicalIncident $incident): void
    {
        // Note: Dispatchable::dispatch() declares no parameters, so named
        // arguments throw "Unknown named parameter". Build the event
        // explicitly and dispatch the instance instead.
        event(new NotificationTriggered(
            triggerType: 'medical_incident',
            studentId: $incident->student_id,
            triggerModel: MedicalIncident::class,
            triggerId: $incident->medical_incident_id,
            context: [
                'incident_date' => $incident->incident_date?->format('d/m/Y') ?? '',
                'symptoms' => $incident->symptoms ?? '',
                'incident_description' => $incident->details ?? '',
                'treatment' => $incident->treatment_given ?? '',
            ]
        ));
    }
}
