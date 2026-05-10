<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('vr_tour_url', 500)->nullable()->after('has_vr_tour');
        });

        // No demo URLs are seeded here — HotelController@show derives a hotel-specific
        // Google Maps Place embed from name + lat/lng for any has_vr_tour hotel that
        // lacks an explicit URL. Admins can override per-hotel later.
    }

    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn('vr_tour_url');
        });
    }
};
