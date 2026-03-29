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
        Schema::table('donations', function (Blueprint $table) {
            $table->index('date_received');
            $table->index('type');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->index('start_datetime');
            $table->index('end_datetime');
        });

        Schema::table('sacraments', function (Blueprint $table) {
            $table->index('date_performed');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex(['date_received']);
            $table->dropIndex(['type']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['start_datetime']);
            $table->dropIndex(['end_datetime']);
        });

        Schema::table('sacraments', function (Blueprint $table) {
            $table->dropIndex(['date_performed']);
            $table->dropIndex(['type']);
        });
    }
};
