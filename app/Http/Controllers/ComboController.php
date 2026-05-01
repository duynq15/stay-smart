<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Place;
use Inertia\Inertia;
use Inertia\Response;

class ComboController extends Controller
{
    public function index(\Illuminate\Http\Request $request): Response
    {
        $combos = collect(config('combos'))->map(function ($c) {
            $hotelCount = Hotel::where('district', $c['district'])
                ->whereBetween('base_price', [$c['price_min'], $c['price_max']])
                ->where('is_active', true)
                ->count();

            return [
                'slug' => $c['slug'],
                'title' => $c['title'],
                'tagline' => $c['tagline'],
                'duration' => $c['duration'],
                'from_price' => $c['from_price'],
                'district' => $c['district'],
                'image' => $c['image'],
                'description' => $c['description'],
                'hotel_count' => $hotelCount,
                'highlights' => $c['highlights'] ?? [],
            ];
        });

        $sort = $request->input('sort', 'recommended');
        $combos = match ($sort) {
            'name' => $combos->sortBy('title')->values(),
            'popular' => $combos->sortByDesc('hotel_count')->values(),
            'newest' => $combos->reverse()->values(), // theo thứ tự config (mới nhất ở cuối)
            'price-asc' => $combos->sortBy('from_price')->values(),
            'price-desc' => $combos->sortByDesc('from_price')->values(),
            default => $combos->values(),
        };

        return Inertia::render('Combos/Index', [
            'combos' => $combos,
            'filters' => ['sort' => $sort],
        ]);
    }

    public function show(string $slug): Response
    {
        $combo = collect(config('combos'))->firstWhere('slug', $slug);
        abort_if(! $combo, 404);

        $hotels = Hotel::query()
            ->where('is_active', true)
            ->where('district', $combo['district'])
            ->whereBetween('base_price', [$combo['price_min'], $combo['price_max']])
            ->with('primaryImage')
            ->orderByDesc('rating')
            ->orderByDesc('reviews_count')
            ->limit(6)
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

        $places = Place::query()
            ->where('is_active', true)
            ->where('district', $combo['district'])
            ->orderByDesc('rating')
            ->limit(6)
            ->get(['id', 'name', 'type', 'address', 'rating', 'avg_price', 'image_url']);

        return Inertia::render('Combos/Show', [
            'combo' => $combo,
            'hotels' => $hotels,
            'places' => $places->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'type' => $p->type,
                'address' => $p->address,
                'rating' => $p->rating,
                'avg_price' => $p->avg_price,
                'image_url' => $p->image_url,
            ]),
        ]);
    }
}
