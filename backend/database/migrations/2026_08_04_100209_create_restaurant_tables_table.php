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
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('table_number')->unique();
            $table->unsignedTinyInteger('capacity');
            $table->enum('status',[
                'Available',
                'Occupied',
                'Cleaning',
                'Out of Service'
            ])->default('Available');
            $table->string('floor',30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_tables');
    }
};
