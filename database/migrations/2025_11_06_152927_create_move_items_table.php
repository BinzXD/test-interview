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
        Schema::create('move_items', function (Blueprint $table) {
          $table->uuid('id')->primary();
            $table->uuid('move_id');
            $table->uuid('product_id');
            $table->integer('qty');
            $table->timestamps();

            $table->foreign('move_id')->references('id')->on('move');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('move_items');
    }
};
