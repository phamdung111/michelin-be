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
        Schema::create('restaurant_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number');
            $table->foreignId('restaurant_id')->constrained('restaurants')->onDelete('cascade');
            $table->boolean('is_booking')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
