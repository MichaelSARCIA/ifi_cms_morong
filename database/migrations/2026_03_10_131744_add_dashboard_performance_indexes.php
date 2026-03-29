<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds composite and single-column indexes to dashboard-critical tables
     * to speed up WHERE, GROUP BY, and ORDER BY operations.
     */
    public function up(): void
    {
        // -------------------------------------------------------
        // service_requests
        // Used in: avg_processing_time, ytd_sacraments, charts
        // -------------------------------------------------------
        Schema::table('service_requests', function (Blueprint $table) {
            // Speeds up: WHERE status IN (...) AND YEAR(created_at) = ...
            $table->index(['status', 'created_at'], 'idx_sr_status_created');
            // Speeds up: GROUP BY service_type
            $table->index('service_type', 'idx_sr_service_type');
        });

        // -------------------------------------------------------
        // donations
        // Used in: collection_frequency, donation_frequency, trend charts
        // -------------------------------------------------------
        Schema::table('donations', function (Blueprint $table) {
            // Speeds up: WHERE type IN (...) AND date_received BETWEEN ...
            $table->index(['type', 'date_received'], 'idx_don_type_date');
        });

        // -------------------------------------------------------
        // schedules
        // Used in: heatmap_data, upcoming_events
        // -------------------------------------------------------
        Schema::table('schedules', function (Blueprint $table) {
            // Speeds up: WHERE status = 'Confirmed' ORDER BY start_datetime
            $table->index(['status', 'start_datetime'], 'idx_sch_status_datetime');
        });

        // -------------------------------------------------------
        // audit_logs
        // Used in: recent_activities
        // -------------------------------------------------------
        Schema::table('audit_logs', function (Blueprint $table) {
            // Speeds up: WHERE user_id = ? ORDER BY created_at DESC LIMIT 5
            $table->index(['user_id', 'created_at'], 'idx_al_user_created');
        });

        // -------------------------------------------------------
        // payments
        // Used in: trend_fees
        // -------------------------------------------------------
        Schema::table('payments', function (Blueprint $table) {
            // Speeds up: WHERE paid_at BETWEEN ... GROUP BY MONTH(paid_at)
            $table->index('paid_at', 'idx_pay_paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropIndex('idx_sr_status_created');
            $table->dropIndex('idx_sr_service_type');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex('idx_don_type_date');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('idx_sch_status_datetime');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_al_user_created');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_pay_paid_at');
        });
    }
};
