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
        Schema::table('users', function (Blueprint $table) {
            $table->json('working_days')->nullable(); // e.g. ["Monday", "Wednesday"]
            $table->json('working_hours')->nullable(); // e.g. {"start": "08:00", "end": "12:00"}
            $table->integer('max_services_per_day')->nullable()->default(5);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['working_days', 'working_hours', 'max_services_per_day']);
        });
    }
};
