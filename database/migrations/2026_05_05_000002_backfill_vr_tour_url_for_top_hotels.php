<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Previously this migration seeded Pannellum demo panoramas (observatory, JFK
        // speech, mountains) which were unrelated to the actual hotels. Removed because
        // HotelController@show now derives a hotel-specific Google Maps Place embed
        // from each hotel's name + lat/lng when vr_tour_url is null.
    }

    public function down(): void {}
};
