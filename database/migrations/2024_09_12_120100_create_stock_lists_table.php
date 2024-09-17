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
        Schema::create('stock_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('lastDate');
            $table->boolean('is_processed');
            $table->timestamps();

            $table->unique(['name', 'lastDate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_lists');
    }
};
