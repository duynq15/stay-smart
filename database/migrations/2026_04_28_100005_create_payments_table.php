<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->enum('method', ['credit_card', 'vnpay', 'momo', 'bank_transfer', 'cash_at_hotel']);
            $table->integer('amount');
            $table->enum('status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
            $table->string('transaction_ref', 100)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
