<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PromotionController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $sort = $request->input('sort', 'discount');

        // Lấy 12 KS rating cao để mô phỏng deal
        $hotels = Hotel::query()
            ->where('is_active', true)
            ->with('primaryImage')
            ->orderByDesc('rating')
            ->orderByDesc('reviews_count')
            ->limit(12)
            ->get();

        $discountPercents = [25, 20, 18, 15, 30, 22, 17, 12, 28, 19, 14, 23];

        // Map deal data trước khi sort
        $hotels = $hotels->values()->map(function (Hotel $h, int $index) use ($discountPercents) {
            $discount = $discountPercents[$index] ?? 15;
            $originalPrice = (int) round($h->base_price / (1 - $discount / 100));

            return (object) [
                'id' => $h->id,
                'name' => $h->name,
                'slug' => $h->slug,
                'district' => $h->district,
                'base_price' => $h->base_price,
                'original_price' => $originalPrice,
                'discount_percent' => $discount,
                'rating' => $h->rating,
                'reviews_count' => $h->reviews_count,
                'has_vr_tour' => $h->has_vr_tour,
                'image' => $h->primaryImage?->url ?? 'https://placehold.co/600x450/14724f/fbf8f1?text=STAY-SMART',
            ];
        });

        // Áp dụng sort
        $hotels = match ($sort) {
            'name' => $hotels->sortBy('name')->values(),
            'popular' => $hotels->sortByDesc('reviews_count')->values(),
            'newest' => $hotels->sortByDesc('id')->values(),
            'price-asc' => $hotels->sortBy('base_price')->values(),
            'price-desc' => $hotels->sortByDesc('base_price')->values(),
            default => $hotels->sortByDesc('discount_percent')->values(), // 'discount'
        };

        // Convert objects → arrays for Inertia
        $hotels = $hotels->map(fn ($h) => (array) $h);

        $banners = [
            ['title' => 'Flash Sale Cuối Tuần', 'tagline' => 'Giảm tới 30% · Còn 18 giờ', 'color' => '#b85c3c', 'icon' => '⚡'],
            ['title' => 'Combo 2N1Đ', 'tagline' => 'Tặng buffet sáng + đưa đón sân bay', 'color' => '#14724f', 'icon' => '🎁'],
            ['title' => 'Đặt sớm 7 ngày', 'tagline' => 'Hoàn tiền 10% nếu đổi ý', 'color' => '#c4965a', 'icon' => '📅'],
        ];

        return Inertia::render('Promotions', [
            'hotels' => $hotels,
            'banners' => $banners,
            'filters' => ['sort' => $sort],
        ]);
    }
}
