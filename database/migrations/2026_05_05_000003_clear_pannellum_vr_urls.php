<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The earlier two migrations seeded Pannellum demo panoramas (observatory, JFK
        // speech, mountains) that are unrelated to hotels. Clear them so the controller
        // can fall back to a hotel-specific Google Maps embed derived from lat/lng.
        DB::table('hotels')
            ->where('vr_tour_url', 'like', '%pannellum.org%')
            ->orWhere('vr_tour_url', 'like', '%cdn.pannellum%')
            ->update(['vr_tour_url' => null]);
    }

    public function down(): void
    {
        // Intentionally empty — restoring fake demo URLs would re-introduce the bug.
    }
};
