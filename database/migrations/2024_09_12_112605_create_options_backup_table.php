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
        Schema::create('options_backups', function (Blueprint $table) {
            $table->id();
            $table->string('indexName');
            $table->integer('strike_price');
            $table->date('currentExpiry');
            $table->longText('callOption');
            $table->longText('putOption');
            $table->boolean('is_processed');
            $table->timestamps();

            $table->unique(['indexName', 'strike_price', 'currentExpiry']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('options_backups');
    }
};
