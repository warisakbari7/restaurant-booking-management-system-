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
        Schema::create('reservation_tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')
            ->constrained('reservations')
            ->cascadeOnDelete()
            ->cascadeOnUpdate();

            $table->foreignId('restaurant_table_id')
            ->constrained('restaurant_tables')
            ->restrictOnDelete()
            ->cascadeOnUpdate();

            $table->unique([
                'restaurant_table_id',
                'reservation_id'
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_tables');
    }
};
