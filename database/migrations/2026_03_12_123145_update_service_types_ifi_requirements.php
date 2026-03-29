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
            [
                'name' => 'Baptism',
                'icon' => 'fa-water',
                'color' => 'blue',
                'requirements' => json_encode(['PSA Birth Certificate', "Parents' Marriage Contract (if married)", 'List of Godparents / Sponsors']),
                'fee' => 500.00
            ],
            [
                'name' => 'Confirmation',
                'icon' => 'fa-dove',
                'color' => 'purple',
                'requirements' => json_encode(['Baptismal Certificate', 'PSA Birth Certificate', 'List of Sponsors']),
                'fee' => 500.00
            ],
            [
                'name' => 'Wedding',
                'icon' => 'fa-ring',
                'color' => 'pink',
                'requirements' => json_encode([
                    'Marriage License from Local Civil Registrar',
                    'PSA Birth Certificate (Groom & Bride)',
                    'CENOMAR (Certificate of No Marriage)',
                    'Baptismal Certificate (For Marriage Purposes)',
                    'Confirmation Certificate (For Marriage Purposes)',
                    'Pre-Marriage Counseling Certificate',
                    'List of Principal Sponsors (Ninongs & Ninangs)'
                ]),
                'fee' => 5000.00
            ],
            [
                'name' => 'Burial / Funeral Mass',
                'icon' => 'fa-cross',
                'color' => 'gray',
                'requirements' => json_encode([
                    'Registered Death Certificate',
                    'Permit to Bury / Transfer Permit'
                ]),
                'fee' => 1000.00
            ]
        ];

        foreach ($services as $service) {
            DB::table('service_types')->updateOrInsert(
                ['name' => $service['name']],
                [
                    'icon' => $service['icon'],
                    'color' => $service['color'],
                    'requirements' => $service['requirements'],
                    'fee' => $service['fee'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('service_types')->where('name', 'Burial / Funeral Mass')->delete();
    }
};
