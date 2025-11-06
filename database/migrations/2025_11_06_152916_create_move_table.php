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
        Schema::create('move', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('source_location_id');
            $table->uuid('destination_location_id');
            $table->uuid('user_id');
            $table->string('code');
            $table->string('description');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('source_location_id')->references('id')->on('locations');
            $table->foreign('destination_location_id')->references('id')->on('locations');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('move');
    }
};
