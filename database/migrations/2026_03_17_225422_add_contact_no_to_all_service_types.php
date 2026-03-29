<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ServiceType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $services = ServiceType::all();
        foreach ($services as $service) {
            $customFields = is_string($service->custom_fields) ? json_decode($service->custom_fields, true) : ($service->custom_fields ?? []);
            
            // Check if contact no already exists
            $exists = false;
            foreach ($customFields as $field) {
                if (isset($field['label']) && (strtolower($field['label']) === 'contact no.' || strtolower($field['label']) === 'contact number')) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $customFields[] = [
                    'id' => 'cust_contact_' . uniqid(),
                    'label' => 'Contact No.',
                    'type' => 'text',
                    'required' => true,
                    'is_standard' => false,
                ];
                $service->update(['custom_fields' => $customFields]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $services = ServiceType::all();
        foreach ($services as $service) {
            $customFields = is_string($service->custom_fields) ? json_decode($service->custom_fields, true) : ($service->custom_fields ?? []);
            
            $filteredFields = array_filter($customFields, function ($field) {
                return !(isset($field['label']) && strtolower($field['label']) === 'contact no.');
            });

            // Re-index array
            $service->update(['custom_fields' => array_values($filteredFields)]);
        }
    }
};
