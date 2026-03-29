<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Activate the missing priest (ID 5)
        DB::table('users')->where('id', 5)->update(['status' => 'Active']);

        // 2. Update Service Types with correct Icons and Colors
        $updates = [
            'Baptism' => ['icon' => 'fa-water', 'color' => 'blue'],
            'Confirmation' => ['icon' => 'fa-fire', 'color' => 'red'],
            'Wedding' => ['icon' => 'fa-heart', 'color' => 'pink'],
            'Funeral' => ['icon' => 'fa-cross', 'color' => 'gray'],
            'Anointing of the Sick' => ['icon' => 'fa-hand-holding-medical', 'color' => 'green'],
            'House Blessing' => ['icon' => 'fa-star', 'color' => 'yellow'],
        ];

        foreach ($updates as $name => $data) {
            DB::table('service_types')->where('name', $name)->update($data);
        }
    }

    public function down(): void
    {
        // Optional: Revert changes if needed (complexity: low priority for data fix)
    }
};
