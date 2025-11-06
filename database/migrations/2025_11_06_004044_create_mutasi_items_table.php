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
        Schema::create('mutasi_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('mutasi_id');
            $table->uuid('product_id');
            $table->integer('qty');
            $table->timestamps();

            $table->foreign('mutasi_id')->references('id')->on('mutasi');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_items');
    }
};
