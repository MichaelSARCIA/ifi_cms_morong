<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Update Payments table: Add unique index to reference_number if it doesn't have one
        Schema::table('payments', function (Blueprint $table) {
            $table->unique('reference_number');
        });

        // 2. Update Donations table: Add reference_number column
        Schema::table('donations', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->unique()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['reference_number']);
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('reference_number');
        });
    }
};
