<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        $featuredHotels = Hotel::query()
            ->where('is_active', true)
            ->with('primaryImage')
            ->orderByDesc('rating')
            ->orderByDesc('reviews_count')
            ->limit(8)
            ->get()
            ->map(fn (Hotel $h) => [
                'id' => $h->id,
                'name' => $h->name,
                'slug' => $h->slug,
                'district' => $h->district,
                'base_price' => $h->base_price,
                'rating' => $h->rating,
                'reviews_count' => $h->reviews_count,
                'has_vr_tour' => $h->has_vr_tour,
                'image' => $h->primaryImage?->url ?? 'https://placehold.co/600x450/14724f/fbf8f1?text=STAY-SMART',
            ]);

        $combos = collect(config('combos'))->map(fn ($c) => [
            'slug' => $c['slug'],
            'title' => $c['title'],
            'tagline' => $c['tagline'],
            'image' => $c['image'],
        ])->all();

        return Inertia::render('Home', [
            'featuredHotels' => $featuredHotels,
            'combos' => $combos,
        ]);
    }
}
