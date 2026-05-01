<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->enum('type', ['restaurant', 'cafe', 'attraction', 'shopping', 'bar', 'spa'])->index();
            $table->string('district', 100)->index();
            $table->string('address');
            $table->text('description')->nullable();
            $table->decimal('rating', 2, 1)->default(0);
            $table->integer('avg_price')->default(0);
            $table->string('image_url', 500)->nullable();
            $table->decimal('lat', 10, 6)->nullable();
            $table->decimal('lng', 10, 6)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
