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
        $services = [
            'Baptism' => [
                ['label' => "Child's Details", 'type' => 'header'],
                ['label' => 'First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Suffix', 'type' => 'text', 'required' => false],
                ['label' => 'Gender', 'type' => 'select', 'options' => ['Male', 'Female'], 'required' => true],
                ['label' => 'Date of Birth', 'type' => 'date', 'required' => true],
                ['label' => 'Place of Birth', 'type' => 'text', 'required' => true],
                ['label' => "Parents' Information", 'type' => 'header'],
                ['label' => 'Father\'s First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Father\'s Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Father\'s Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Father\'s Suffix', 'type' => 'text', 'required' => false],
                ['label' => 'Mother\'s First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Mother\'s Middle Name (Maiden)', 'type' => 'text', 'required' => true],
                ['label' => 'Mother\'s Last Name (Maiden)', 'type' => 'text', 'required' => true],
                ['label' => 'Contact Details & Sponsors', 'type' => 'header'],
                ['label' => 'Complete Address', 'type' => 'textarea', 'required' => true],
                ['label' => 'Contact Number', 'type' => 'text', 'required' => true],
                ['label' => 'Email Address', 'type' => 'text', 'required' => false],
                ['label' => 'Godparents (Ninong & Ninang)', 'type' => 'textarea', 'required' => true],
            ],
            'Confirmation' => [
                ['label' => "Confirmand's Details", 'type' => 'header'],
                ['label' => 'First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Suffix', 'type' => 'text', 'required' => false],
                ['label' => 'Age', 'type' => 'number', 'required' => true],
                ['label' => 'Baptismal Background', 'type' => 'header'],
                ['label' => 'Date of Baptism', 'type' => 'date', 'required' => true],
                ['label' => 'Parish/Church of Baptism', 'type' => 'text', 'required' => true],
                ['label' => "Parents' Information", 'type' => 'header'],
                ['label' => 'Father\'s First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Father\'s Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Father\'s Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Father\'s Suffix', 'type' => 'text', 'required' => false],
                ['label' => 'Mother\'s First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Mother\'s Middle Name (Maiden)', 'type' => 'text', 'required' => true],
                ['label' => 'Mother\'s Last Name (Maiden)', 'type' => 'text', 'required' => true],
                ['label' => 'Contact Details & Sponsor', 'type' => 'header'],
                ['label' => 'Complete Address', 'type' => 'textarea', 'required' => true],
                ['label' => 'Contact Number', 'type' => 'text', 'required' => true],
                ['label' => 'Email Address', 'type' => 'text', 'required' => false],
                ['label' => 'Godparent/Sponsor', 'type' => 'text', 'required' => true],
            ],
            'Wedding' => [
                ['label' => "Groom's Information", 'type' => 'header'],
                ['label' => 'Groom\'s First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Groom\'s Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Groom\'s Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Groom\'s Suffix', 'type' => 'text', 'required' => false],
                ['label' => 'Groom\'s Date of Birth', 'type' => 'date', 'required' => true],
                ['label' => 'Groom\'s Place of Birth', 'type' => 'text', 'required' => true],
                ['label' => "Bride's Information", 'type' => 'header'],
                ['label' => 'Bride\'s First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Bride\'s Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Bride\'s Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Bride\'s Suffix', 'type' => 'text', 'required' => false],
                ['label' => 'Bride\'s Date of Birth', 'type' => 'date', 'required' => true],
                ['label' => 'Bride\'s Place of Birth', 'type' => 'text', 'required' => true],
                ['label' => "Marriage Details", 'type' => 'header'],
                ['label' => 'Proposed Wedding Date', 'type' => 'date', 'required' => true],
                ['label' => 'Proposed Wedding Time', 'type' => 'text', 'required' => true],
                ['label' => 'Marriage License No.', 'type' => 'text', 'required' => true],
                ['label' => 'Date Issued (Marriage License)', 'type' => 'date', 'required' => true],
                ['label' => 'Place Issued (Marriage License)', 'type' => 'text', 'required' => true],
                ['label' => "Applicant's Information", 'type' => 'header'],
                ['label' => 'Applicant\'s First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Applicant\'s Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Applicant\'s Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Applicant\'s Suffix', 'type' => 'text', 'required' => false],
                ['label' => 'Complete Address', 'type' => 'textarea', 'required' => true],
                ['label' => 'Contact Number', 'type' => 'text', 'required' => true],
                ['label' => 'Email Address', 'type' => 'text', 'required' => false],
                ['label' => "Sponsors (Ninong & Ninang)", 'type' => 'header'],
                ['label' => 'Godparents/Sponsors', 'type' => 'textarea', 'required' => true],
            ],
            'Funeral Mass' => [
                ['label' => "Deceased's Information", 'type' => 'header'],
                ['label' => 'First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Suffix', 'type' => 'text', 'required' => false],
                ['label' => 'Gender', 'type' => 'select', 'options' => ['Male', 'Female'], 'required' => true],
                ['label' => 'Service Details', 'type' => 'header'],
                ['label' => 'Date of Death', 'type' => 'date', 'required' => true],
                ['label' => 'Cause of Death', 'type' => 'text', 'required' => true],
                ['label' => 'Date of Interment/Burial', 'type' => 'date', 'required' => true],
                ['label' => 'Cemetery', 'type' => 'text', 'required' => true],
                ['label' => "Applicant's Information", 'type' => 'header'],
                ['label' => 'Applicant\'s First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Applicant\'s Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Applicant\'s Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Complete Address', 'type' => 'textarea', 'required' => true],
                ['label' => 'Contact Number', 'type' => 'text', 'required' => true],
                ['label' => 'Email Address', 'type' => 'text', 'required' => false],
            ],
            'Wake' => [
                ['label' => "Deceased's Information", 'type' => 'header'],
                ['label' => 'First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Suffix', 'type' => 'text', 'required' => false],
                ['label' => 'Wake Details', 'type' => 'header'],
                ['label' => 'Location of Wake', 'type' => 'text', 'required' => true],
                ['label' => 'Start Date of Wake', 'type' => 'date', 'required' => true],
                ['label' => 'End Date of Wake', 'type' => 'date', 'required' => true],
                ['label' => "Applicant's Information", 'type' => 'header'],
                ['label' => 'Applicant\'s First Name', 'type' => 'text', 'required' => true],
                ['label' => 'Applicant\'s Middle Name', 'type' => 'text', 'required' => true],
                ['label' => 'Applicant\'s Last Name', 'type' => 'text', 'required' => true],
                ['label' => 'Complete Address', 'type' => 'textarea', 'required' => true],
                ['label' => 'Contact Number', 'type' => 'text', 'required' => true],
                ['label' => 'Email Address', 'type' => 'text', 'required' => false],
            ],
        ];

        foreach ($services as $name => $fields) {
            \App\Models\ServiceType::where('name', $name)->update([
                'custom_fields' => json_encode($fields)
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
