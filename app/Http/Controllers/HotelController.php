<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HotelController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Hotel::query()
            ->where('is_active', true)
            ->with('primaryImage');

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('district', 'like', "%{$q}%")
                  ->orWhere('address', 'like', "%{$q}%");
            });
        }

        if ($request->filled('location') && $request->location !== 'all') {
            $query->where('district', $request->location);
        }

        if ($request->filled('price') && $request->price !== 'all') {
            [$min, $max] = explode('-', $request->price) + [0, PHP_INT_MAX];
            $query->whereBetween('base_price', [(int) $min, (int) $max]);
        }

        $amenities = (array) $request->input('amenities', []);
        if (! empty($amenities)) {
            foreach ($amenities as $a) {
                $query->whereJsonContains('amenities', $a);
            }
        }

        match ($request->input('sort', 'recommended')) {
            'name' => $query->orderBy('name'),
            'popular' => $query->orderByDesc('reviews_count')->orderByDesc('rating'),
            'newest' => $query->orderByDesc('id'),
            'price-asc' => $query->orderBy('base_price'),
            'price-desc' => $query->orderByDesc('base_price'),
            'rating' => $query->orderByDesc('rating')->orderByDesc('reviews_count'),
            default => $query->orderByDesc('rating')->orderByDesc('reviews_count'),
        };

        $totalCount = (clone $query)->count();
        $hotels = $query->paginate(12)->withQueryString();

        $hotels->getCollection()->transform(fn (Hotel $h) => [
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

        return Inertia::render('Hotels/Index', [
            'hotels' => $hotels,
            'filters' => [
                'sort' => $request->input('sort', 'recommended'),
                'price' => $request->input('price', 'all'),
                'location' => $request->input('location', 'all'),
                'amenities' => $amenities,
                'q' => $request->input('q', ''),
            ],
            'districts' => Hotel::query()->select('district')->distinct()->orderBy('district')->pluck('district'),
            'totalCount' => $totalCount,
        ]);
    }

    public function show(Hotel $hotel): Response
    {
        $hotel->load(['rooms' => fn ($q) => $q->where('is_active', true)->orderBy('price_per_night'), 'images']);

        return Inertia::render('Hotels/Show', [
            'hotel' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'slug' => $hotel->slug,
                'district' => $hotel->district,
                'address' => $hotel->address,
                'lat' => $hotel->lat,
                'lng' => $hotel->lng,
                'stars' => $hotel->stars,
                'base_price' => $hotel->base_price,
                'rating' => $hotel->rating,
                'reviews_count' => $hotel->reviews_count,
                'description' => $hotel->description,
                'amenities' => $hotel->amenities ?? [],
                'has_vr_tour' => $hotel->has_vr_tour,
                'phone' => $hotel->phone,
                'email' => $hotel->email,
                'images' => $hotel->images->map(fn ($i) => [
                    'id' => $i->id,
                    'url' => $i->url,
                    'caption' => $i->caption,
                    'is_primary' => $i->is_primary,
                ])->values(),
                'rooms' => $hotel->rooms->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'description' => $r->description,
                    'price_per_night' => $r->price_per_night,
                    'capacity' => $r->capacity,
                    'available_units' => $r->available_units,
                    'image' => $r->image,
                ])->values(),
            ],
        ]);
    }
}
