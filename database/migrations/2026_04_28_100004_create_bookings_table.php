<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 20)->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('hotel_id')->constrained('hotels');
            $table->foreignId('room_id')->constrained('rooms');
            $table->string('guest_name', 120);
            $table->string('guest_email', 150);
            $table->string('guest_phone', 20);
            $table->date('checkin_date')->index();
            $table->date('checkout_date');
            $table->tinyInteger('nights');
            $table->tinyInteger('guests_count')->default(1);
            $table->integer('subtotal');
            $table->integer('tax');
            $table->integer('total_amount');
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending')->index();
            $table->text('special_requests')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
