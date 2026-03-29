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
        $fields = [
            ['label' => "Groom's Details", 'type' => 'header'],
            ['label' => "Groom's First Name", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Middle Name", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Last Name", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Suffix", 'type' => 'text', 'required' => false],
            ['label' => "Groom's Age", 'type' => 'number', 'required' => true],
            ['label' => "Groom's Complete Address", 'type' => 'textarea', 'required' => true],
            ['label' => "Groom's Civil Status", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Religion", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Occupation", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Father's First Name", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Father's Middle Name", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Father's Last Name", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Mother's First Name", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Mother's Middle Name (Maiden)", 'type' => 'text', 'required' => true],
            ['label' => "Groom's Mother's Last Name (Maiden)", 'type' => 'text', 'required' => true],

            ['label' => "Bride's Details", 'type' => 'header'],
            ['label' => "Bride's First Name", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Middle Name", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Last Name", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Suffix", 'type' => 'text', 'required' => false],
            ['label' => "Bride's Age", 'type' => 'number', 'required' => true],
            ['label' => "Bride's Complete Address", 'type' => 'textarea', 'required' => true],
            ['label' => "Bride's Civil Status", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Religion", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Occupation", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Father's First Name", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Father's Middle Name", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Father's Last Name", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Mother's First Name", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Mother's Middle Name (Maiden)", 'type' => 'text', 'required' => true],
            ['label' => "Bride's Mother's Last Name (Maiden)", 'type' => 'text', 'required' => true],

            ['label' => "Applicant's Details (Contact Person)", 'type' => 'header'],
            ['label' => "Full Name", 'type' => 'text', 'required' => true],
            ['label' => "Relationship to the Couple", 'type' => 'text', 'required' => true],
            ['label' => "Contact Number", 'type' => 'text', 'required' => true],
            ['label' => "Email Address", 'type' => 'text', 'required' => false],

            ['label' => "Sponsors", 'type' => 'header'],
            ['label' => "Principal Sponsors (Ninongs & Ninangs)", 'type' => 'textarea', 'required' => true]
        ];

        ServiceType::where('name', 'Wedding')->update([
            'custom_fields' => json_encode($fields)
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to some previous state if necessary
    }
};
