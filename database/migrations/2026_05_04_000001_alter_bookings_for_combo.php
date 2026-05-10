<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropForeign(['room_id']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable()->change();
            $table->foreignId('room_id')->nullable()->change();
            $table->string('combo_slug', 80)->nullable()->after('room_id')->index();
            $table->enum('booking_type', ['hotel', 'combo'])->default('hotel')->after('combo_slug')->index();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('hotel_id')->references('id')->on('hotels')->nullOnDelete();
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['hotel_id']);
            $table->dropForeign(['room_id']);
            $table->dropIndex(['combo_slug']);
            $table->dropIndex(['booking_type']);
            $table->dropColumn(['combo_slug', 'booking_type']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('hotel_id')->nullable(false)->change();
            $table->foreignId('room_id')->nullable(false)->change();
            $table->foreign('hotel_id')->references('id')->on('hotels');
            $table->foreign('room_id')->references('id')->on('rooms');
        });
    }
};
