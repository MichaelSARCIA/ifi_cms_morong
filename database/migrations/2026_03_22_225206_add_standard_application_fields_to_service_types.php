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
        
        $standardFieldsTemplate = [
            [
                'id' => 'cust_sf_first_name',
                'label' => 'First Name',
                'type' => 'text',
                'required' => true,
                'is_standard' => true,
            ],
            [
                'id' => 'cust_sf_middle_initial',
                'label' => 'Middle Initial',
                'type' => 'text',
                'required' => false,
                'is_standard' => true,
            ],
            [
                'id' => 'cust_sf_last_name',
                'label' => 'Last Name',
                'type' => 'text',
                'required' => true,
                'is_standard' => true,
            ],
            [
                'id' => 'cust_sf_address',
                'label' => 'Complete Address',
                'type' => 'textarea',
                'required' => true,
                'is_standard' => true,
            ],
            [
                'id' => 'cust_sf_contact',
                'label' => 'Contact Number',
                'type' => 'text',
                'required' => true,
                'is_standard' => true,
            ],
            [
                'id' => 'cust_sf_dob',
                'label' => 'Age / Date of Birth',
                'type' => 'text', 
                'required' => true,
                'is_standard' => true,
            ],
            [
                'id' => 'cust_sf_civil',
                'label' => 'Civil Status',
                'type' => 'select',
                'required' => true,
                'is_standard' => true,
                'options' => ['Single', 'Married', 'Widowed', 'Separated'],
            ],
            [
                'id' => 'cust_sf_gender',
                'label' => 'Gender',
                'type' => 'select',
                'required' => true,
                'is_standard' => true,
                'options' => ['Male', 'Female'],
            ],
            [
                'id' => 'cust_sf_email',
                'label' => 'Email Address',
                'type' => 'text',
                'required' => false,
                'is_standard' => true,
            ]
        ];

        foreach ($services as $service) {
            $customFields = is_string($service->custom_fields) ? json_decode($service->custom_fields, true) : ($service->custom_fields ?? []);
            
            // Map existing field labels to lowercase for easy lookup
            $existingLabels = array_map(function($field) {
                return strtolower($field['label'] ?? '');
            }, $customFields);
            
            $fieldsToAdd = [];
            
            foreach ($standardFieldsTemplate as $stdField) {
                $stdLabelLower = strtolower($stdField['label']);
                
                // Account for potential aliases in existing fields
                $aliases = [$stdLabelLower];
                if ($stdLabelLower === 'contact number') $aliases[] = 'contact no.';
                if ($stdLabelLower === 'middle initial') {
                    $aliases[] = 'middle name';
                    $aliases[] = 'middle name / initial';
                }
                if ($stdLabelLower === 'age / date of birth') {
                    $aliases[] = 'age';
                    $aliases[] = 'date of birth';
                }
                
                $exists = false;
                foreach($aliases as $alias) {
                    if (in_array($alias, $existingLabels)) {
                        $exists = true;
                        
                        // If we are replacing 'middle name' with 'middle initial', let's rename it in place
                        if ($stdLabelLower === 'middle initial' && ($alias === 'middle name' || $alias === 'middle name / initial')) {
                            foreach ($customFields as &$existingField) {
                                if (strtolower($existingField['label'] ?? '') === $alias) {
                                    $existingField['label'] = 'Middle Initial';
                                    break;
                                }
                            }
                        }
                        
                        // If it's a contact number, rename it to 'Contact Number' just to be clean
                        if ($stdLabelLower === 'contact number' && $alias === 'contact no.') {
                            foreach ($customFields as &$existingField) {
                                if (strtolower($existingField['label'] ?? '') === $alias) {
                                    $existingField['label'] = 'Contact Number';
                                    break;
                                }
                            }
                        }
                        break;
                    }
                }
                
                if (!$exists) {
                    $fieldsToAdd[] = collect($stdField)->put('id', 'cust_sf_' . uniqid())->toArray();
                }
            }
            
            // Add new fields at the top
            if (!empty($fieldsToAdd)) {
                $customFields = array_merge($fieldsToAdd, $customFields);
                $service->update(['custom_fields' => $customFields]);
            } else {
                // Save anyway in case we updated labels
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
                return empty($field['is_standard']);
            });

            // Re-index array
            $service->update(['custom_fields' => array_values($filteredFields)]);
        }
    }
};
