<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVehicleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = Vehicle::$rules;
        
        // Get the vehicle ID from the route parameter
        $vehicleId = $this->route('vehicle');
        
        // If it's a model instance, get the ID
        if (is_object($vehicleId)) {
            $vehicleId = $vehicleId->vehicle_id;
        }
        
        // Modify rules for update (e.g., unique validation)
        $rules['vehicle_number'] = 'required|string|max:20|unique:vehicles,vehicle_number,' . $vehicleId . ',vehicle_id';
        
        return $rules;
    }
}