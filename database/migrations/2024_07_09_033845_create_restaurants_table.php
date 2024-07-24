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
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('status');
            $table->string('address')->unique();
            $table->string('phone');
            $table->string('email');
            $table->string('description');
            $table->boolean('allow_booking');
            $table->string('avatar');
            $table->integer('count_like')->unsigned()->nullable();
            $table->integer('count_comment')->unsigned()->nullable();
            $table->boolean('lock_comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void 
    {
        Schema::dropIfExists('restaurants');
    }
};
