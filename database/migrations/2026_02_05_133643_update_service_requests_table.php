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
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('client_name');
            $table->string('first_name')->after('id');
            $table->string('middle_name')->nullable()->after('first_name');
            $table->string('last_name')->after('middle_name');
            $table->string('fathers_name')->nullable()->after('email');
            $table->string('mothers_name')->nullable()->after('fathers_name');
            $table->unsignedBigInteger('priest_id')->nullable()->after('mothers_name');
            $table->json('requirements')->nullable()->after('details');
            $table->string('payment_status')->default('Pending')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('client_name');
            $table->dropColumn(['first_name', 'middle_name', 'last_name', 'fathers_name', 'mothers_name', 'priest_id', 'requirements', 'payment_status']);
        });
    }
};
