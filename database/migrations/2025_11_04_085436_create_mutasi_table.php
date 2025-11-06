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
        Schema::create('mutasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('location_id');
            $table->uuid('user_id');
            $table->date('date');
            $table->enum('type', ['in', 'out']);
            $table->string('code');
            $table->string('reason');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi');
    }
};
