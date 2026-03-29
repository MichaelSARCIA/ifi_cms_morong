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
        Schema::create('sacraments', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // Baptism, Wedding, Burial
            $table->unsignedBigInteger('member_id')->nullable();
            $table->date('date_performed');
            $table->string('priest_name')->nullable();
            $table->text('details')->nullable(); // JSON or serialized data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sacraments');
    }
};
