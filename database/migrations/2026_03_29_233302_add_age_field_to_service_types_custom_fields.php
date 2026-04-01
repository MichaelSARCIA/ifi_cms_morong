<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $services = \App\Models\ServiceType::all();
        foreach ($services as $service) {
            $fields = $service->custom_fields;
            if (is_string($fields)) {
                $fields = json_decode($fields, true);
            }
            if (!is_array($fields)) continue;

            $newFields = [];
            foreach ($fields as $field) {
                $newFields[] = $field;
                $label = strtolower($field['label'] ?? '');
                
                // Add Age for Baptism
                if ($service->name === 'Baptism' && $label === 'date of birth') {
                    if (!in_array('Age', array_column($fields, 'label'))) {
                        $newFields[] = ['label' => 'Age', 'type' => 'number', 'required' => true];
                    }
                }
                
                // Add Age for Wedding (Groom)
                if ($service->name === 'Wedding' && $label === 'groom\'s date of birth') {
                    if (!in_array('Groom\'s Age', array_column($fields, 'label'))) {
                        $newFields[] = ['label' => 'Groom\'s Age', 'type' => 'number', 'required' => true];
                    }
                }
                
                // Add Age for Wedding (Bride)
                if ($service->name === 'Wedding' && $label === 'bride\'s date of birth') {
                    if (!in_array('Bride\'s Age', array_column($fields, 'label'))) {
                        $newFields[] = ['label' => 'Bride\'s Age', 'type' => 'number', 'required' => true];
                    }
                }
                
                // Add Age for Funeral Mass
                if ($service->name === 'Funeral Mass' && $label === 'gender') {
                    if (!in_array('Age', array_column($fields, 'label'))) {
                        $newFields[] = ['label' => 'Age', 'type' => 'number', 'required' => true];
                    }
                }
                
                // Add Age for Wake
                if ($service->name === 'Wake' && $label === 'suffix') {
                    if (!in_array('Age', array_column($fields, 'label'))) {
                        $newFields[] = ['label' => 'Age', 'type' => 'number', 'required' => true];
                    }
                }
            }
            
            $service->custom_fields = $newFields;
            $service->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed as users can edit Custom fields
    }
};
