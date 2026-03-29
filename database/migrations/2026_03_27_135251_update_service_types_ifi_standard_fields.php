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
                'fields' => [
                    ['label' => 'First Name', 'type' => 'text', 'required' => true],
                    ['label' => 'Middle Name', 'type' => 'text', 'required' => true],
                    ['label' => 'Last Name', 'type' => 'text', 'required' => true],
                    ['label' => 'Suffix', 'type' => 'text', 'required' => true],
                    ['label' => 'Gender', 'type' => 'select', 'options' => ['Male', 'Female'], 'required' => true],
                    ['label' => 'Date of Birth', 'type' => 'date', 'required' => true],
                    ['label' => 'Place of Birth', 'type' => 'text', 'required' => true],
                    ['label' => "Father's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Father's Middle Name", 'type' => 'text', 'required' => true],
                    ['label' => "Father's Last Name", 'type' => 'text', 'required' => true],
                    ['label' => "Father's Suffix", 'type' => 'text', 'required' => true],
                    ['label' => "Mother's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Mother's Middle Name (Maiden)", 'type' => 'text', 'required' => true],
                    ['label' => "Mother's Last Name (Maiden)", 'type' => 'text', 'required' => true],
                    ['label' => 'Complete Address', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Contact Number', 'type' => 'text', 'required' => true],
                    ['label' => 'Email Address', 'type' => 'text', 'required' => false],
                    ['label' => 'Godparents (Ninong & Ninang)', 'type' => 'textarea', 'required' => true]
                ],
                'requirements' => [
                    'PSA Birth Certificate of the Child',
                    'Marriage Certificate of Parents (if married)',
                    'Valid ID of the Applicant'
                ]
            ],
            'Confirmation' => [
                'fields' => [
                    ['label' => 'First Name', 'type' => 'text', 'required' => true],
                    ['label' => 'Middle Name', 'type' => 'text', 'required' => true],
                    ['label' => 'Last Name', 'type' => 'text', 'required' => true],
                    ['label' => 'Suffix', 'type' => 'text', 'required' => true],
                    ['label' => 'Age', 'type' => 'number', 'required' => true],
                    ['label' => 'Date of Baptism', 'type' => 'date', 'required' => true],
                    ['label' => 'Parish/Church of Baptism', 'type' => 'text', 'required' => true],
                    ['label' => "Father's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Father's Middle Name", 'type' => 'text', 'required' => true],
                    ['label' => "Father's Last Name", 'type' => 'text', 'required' => true],
                    ['label' => "Father's Suffix", 'type' => 'text', 'required' => true],
                    ['label' => "Mother's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Mother's Middle Name (Maiden)", 'type' => 'text', 'required' => true],
                    ['label' => "Mother's Last Name (Maiden)", 'type' => 'text', 'required' => true],
                    ['label' => 'Complete Address', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Contact Number', 'type' => 'text', 'required' => true],
                    ['label' => 'Email Address', 'type' => 'text', 'required' => false],
                    ['label' => 'Sponsor (Ninong/Ninang)', 'type' => 'text', 'required' => true]
                ],
                'requirements' => [
                    'Original Baptismal Certificate',
                    'PSA Birth Certificate'
                ]
            ],
            'Wedding' => [
                'fields' => [
                    ['label' => "Groom's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Middle Name", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Last Name", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Suffix", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Age", 'type' => 'number', 'required' => true],
                    ['label' => "Groom's Complete Address", 'type' => 'textarea', 'required' => true],
                    ['label' => "Groom's Civil Status", 'type' => 'select', 'options' => ['Single', 'Annulled', 'Widowed'], 'required' => true],
                    ['label' => "Groom's Religion", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Occupation", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Father's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Father's Middle Name", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Father's Last Name", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Mother's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Mother's Middle Name (Maiden)", 'type' => 'text', 'required' => true],
                    ['label' => "Groom's Mother's Last Name (Maiden)", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Middle Name", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Last Name", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Suffix", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Age", 'type' => 'number', 'required' => true],
                    ['label' => "Bride's Complete Address", 'type' => 'textarea', 'required' => true],
                    ['label' => "Bride's Civil Status", 'type' => 'select', 'options' => ['Single', 'Annulled', 'Widowed'], 'required' => true],
                    ['label' => "Bride's Religion", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Occupation", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Father's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Father's Middle Name", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Father's Last Name", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Mother's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Mother's Middle Name (Maiden)", 'type' => 'text', 'required' => true],
                    ['label' => "Bride's Mother's Last Name (Maiden)", 'type' => 'text', 'required' => true],
                    ['label' => "Applicant's Full Name (Contact Person)", 'type' => 'text', 'required' => true],
                    ['label' => 'Relationship to the Couple (e.g., Groom, Bride, Coordinator)', 'type' => 'text', 'required' => true],
                    ['label' => 'Contact Number', 'type' => 'text', 'required' => true],
                    ['label' => 'Email Address', 'type' => 'text', 'required' => false],
                    ['label' => 'Principal Sponsors (Ninongs & Ninangs)', 'type' => 'textarea', 'required' => true]
                ],
                'requirements' => [
                    'PSA Birth Certificate (Groom & Bride)',
                    'CENOMAR / Certificate of No Marriage (Groom & Bride)',
                    'Baptismal & Confirmation Certificates (Annotated "For Marriage")',
                    'Marriage License (from Local Civil Registry) OR Affidavit of Cohabitation (Article 34)',
                    'Pre-Marriage / Pre-Cana Seminar Certificate'
                ]
            ],
            'Funeral Mass' => [
                'fields' => [
                    ['label' => "Deceased's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Deceased's Middle Name", 'type' => 'text', 'required' => true],
                    ['label' => "Deceased's Last Name", 'type' => 'text', 'required' => true],
                    ['label' => "Deceased's Suffix", 'type' => 'text', 'required' => true],
                    ['label' => 'Gender', 'type' => 'select', 'options' => ['Male', 'Female'], 'required' => true],
                    ['label' => 'Civil Status', 'type' => 'select', 'options' => ['Single', 'Married', 'Widowed', 'Separated'], 'required' => true],
                    ['label' => 'Age', 'type' => 'number', 'required' => true],
                    ['label' => 'Date of Death', 'type' => 'date', 'required' => true],
                    ['label' => 'Place of Death', 'type' => 'text', 'required' => true],
                    ['label' => 'Cause of Death', 'type' => 'text', 'required' => true],
                    ['label' => 'Date and Time of Interment', 'type' => 'text', 'required' => true],
                    ['label' => 'Cemetery / Place of Interment', 'type' => 'text', 'required' => true],
                    ['label' => "Applicant's Full Name", 'type' => 'text', 'required' => true],
                    ['label' => 'Relationship to Deceased', 'type' => 'text', 'required' => true],
                    ['label' => 'Contact Number', 'type' => 'text', 'required' => true],
                    ['label' => 'Email Address', 'type' => 'text', 'required' => false],
                    ['label' => 'Complete Address', 'type' => 'textarea', 'required' => true]
                ],
                'requirements' => [
                    'Registered Death Certificate (LGU or PSA)'
                ]
            ],
            'Wake' => [
                'fields' => [
                    ['label' => "Deceased's First Name", 'type' => 'text', 'required' => true],
                    ['label' => "Deceased's Middle Name", 'type' => 'text', 'required' => true],
                    ['label' => "Deceased's Last Name", 'type' => 'text', 'required' => true],
                    ['label' => "Deceased's Suffix", 'type' => 'text', 'required' => true],
                    ['label' => 'Gender', 'type' => 'select', 'options' => ['Male', 'Female'], 'required' => true],
                    ['label' => 'Age', 'type' => 'number', 'required' => true],
                    ['label' => 'Location / Complete Address of Wake', 'type' => 'textarea', 'required' => true],
                    ['label' => 'Start Date of Wake', 'type' => 'date', 'required' => true],
                    ['label' => 'Expected Date of Interment (Libing)', 'type' => 'date', 'required' => true],
                    ['label' => "Applicant's Full Name", 'type' => 'text', 'required' => true],
                    ['label' => 'Relationship to Deceased', 'type' => 'text', 'required' => true],
                    ['label' => 'Contact Number', 'type' => 'text', 'required' => true],
                    ['label' => 'Email Address', 'type' => 'text', 'required' => false]
                ],
                'requirements' => [
                    'Registered Death Certificate (LGU or PSA)'
                ]
            ],
        ];

        foreach ($services as $name => $config) {
            \App\Models\ServiceType::where('name', $name)->update([
                'custom_fields' => json_encode($config['fields']),
                'requirements' => json_encode($config['requirements'])
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
