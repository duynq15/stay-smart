<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminHotelController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Hotel::query()->withCount('rooms');

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }
        if ($request->filled('stars')) {
            $query->where('stars', $request->stars);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $hotels = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $hotels->getCollection()->transform(fn ($h) => [
            'id' => $h->id,
            'name' => $h->name,
            'slug' => $h->slug,
            'district' => $h->district,
            'stars' => $h->stars,
            'base_price' => $h->base_price,
            'rating' => $h->rating,
            'reviews_count' => $h->reviews_count,
            'rooms_count' => $h->rooms_count,
            'is_active' => (bool) $h->is_active,
            'has_vr_tour' => (bool) $h->has_vr_tour,
            'primary_image' => $h->images()->orderByDesc('is_primary')->value('url'),
        ]);

        return Inertia::render('Admin/Hotels/Index', [
            'hotels' => $hotels,
            'filters' => $request->only(['q', 'district', 'stars', 'status']),
            'districts' => Hotel::query()->distinct()->orderBy('district')->pluck('district'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Hotels/Form', [
            'hotel' => null,
            'amenityOptions' => $this->amenityOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);
        $imagesPayload = $this->validateImagesArray($request);

        $hotel = Hotel::create([
            ...$validated,
            'slug' => $this->generateUniqueSlug($validated['name']),
        ]);

        if (! empty($imagesPayload)) {
            $hasExplicitPrimary = collect($imagesPayload)->contains(fn ($i) => ! empty($i['is_primary']));
            foreach ($imagesPayload as $idx => $img) {
                $hotel->images()->create([
                    'url' => $img['url'],
                    'caption' => $img['caption'] ?? null,
                    'is_primary' => $hasExplicitPrimary
                        ? (bool) ($img['is_primary'] ?? false)
                        : $idx === 0,
                ]);
            }
        }

        $msg = empty($imagesPayload)
            ? "Đã tạo khách sạn \"{$hotel->name}\". Hãy thêm ảnh & phòng cho khách sạn."
            : "Đã tạo khách sạn \"{$hotel->name}\" với " . count($imagesPayload) . " ảnh.";

        return redirect()->route('admin.hotels.edit', $hotel->id)->with('success', $msg);
    }

    public function edit(Hotel $hotel): Response
    {
        return Inertia::render('Admin/Hotels/Form', [
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
                'description' => $hotel->description,
                'amenities' => $hotel->amenities ?? [],
                'phone' => $hotel->phone,
                'email' => $hotel->email,
                'has_vr_tour' => (bool) $hotel->has_vr_tour,
                'vr_tour_url' => $hotel->vr_tour_url,
                'is_active' => (bool) $hotel->is_active,
                'rooms_count' => $hotel->rooms()->count(),
                'bookings_count' => $hotel->bookings()->count(),
                'images' => $hotel->images()
                    ->orderByDesc('is_primary')
                    ->orderBy('id')
                    ->get(['id', 'url', 'caption', 'is_primary'])
                    ->toArray(),
            ],
            'amenityOptions' => $this->amenityOptions(),
        ]);
    }

    public function update(Request $request, Hotel $hotel): RedirectResponse
    {
        $validated = $this->validateData($request);

        $payload = $validated;
        if ($validated['name'] !== $hotel->name) {
            $payload['slug'] = $this->generateUniqueSlug($validated['name'], $hotel->id);
        }

        $hotel->update($payload);

        return back()->with('success', "Đã cập nhật \"{$hotel->name}\".");
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        $bookingsCount = $hotel->bookings()->count();
        if ($bookingsCount > 0) {
            return back()->with('error', "Không thể xóa: khách sạn có {$bookingsCount} đơn đặt phòng. Hãy đặt 'Tạm ngưng' thay vì xóa.");
        }

        $name = $hotel->name;
        $hotel->delete();

        return redirect()->route('admin.hotels.index')->with('success', "Đã xóa \"{$name}\".");
    }

    public function toggleActive(Hotel $hotel): RedirectResponse
    {
        $hotel->update(['is_active' => ! $hotel->is_active]);

        return back()->with('success', $hotel->is_active
            ? "Đã kích hoạt \"{$hotel->name}\"."
            : "Đã tạm ngưng \"{$hotel->name}\".");
    }

    public function addImage(Request $request, Hotel $hotel): RedirectResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'caption' => ['nullable', 'string', 'max:200'],
            'is_primary' => ['boolean'],
        ]);

        $isPrimary = (bool) ($validated['is_primary'] ?? false);

        if ($isPrimary) {
            $hotel->images()->update(['is_primary' => false]);
        }

        // If first image of this hotel, force primary regardless
        if ($hotel->images()->count() === 0) {
            $isPrimary = true;
        }

        $hotel->images()->create([
            'url' => $validated['url'],
            'caption' => $validated['caption'] ?? null,
            'is_primary' => $isPrimary,
        ]);

        return back()->with('success', 'Đã thêm ảnh.');
    }

    public function deleteImage(Hotel $hotel, HotelImage $image): RedirectResponse
    {
        abort_if($image->hotel_id !== $hotel->id, 404);

        $wasPrimary = $image->is_primary;
        $image->delete();

        // If we deleted the primary, promote first remaining image
        if ($wasPrimary) {
            $next = $hotel->images()->orderBy('id')->first();
            $next?->update(['is_primary' => true]);
        }

        return back()->with('success', 'Đã xóa ảnh.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'district' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
            'base_price' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:5000'],
            'amenities' => ['nullable', 'array'],
            'amenities.*' => ['string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
            'has_vr_tour' => ['boolean'],
            'vr_tour_url' => ['nullable', 'url', 'max:500'],
            'is_active' => ['boolean'],
        ]);
    }

    private function validateImagesArray(Request $request): array
    {
        $validated = $request->validate([
            'images' => ['nullable', 'array', 'max:20'],
            'images.*.url' => ['required_with:images', 'url', 'max:500'],
            'images.*.caption' => ['nullable', 'string', 'max:200'],
            'images.*.is_primary' => ['boolean'],
        ]);

        return $validated['images'] ?? [];
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'khach-san';
        }
        $slug = $base;
        $counter = 1;

        while (Hotel::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }

    private function amenityOptions(): array
    {
        return [
            ['key' => 'wifi', 'label' => 'Wi-Fi'],
            ['key' => 'breakfast', 'label' => 'Bữa sáng'],
            ['key' => 'parking', 'label' => 'Bãi đỗ xe'],
            ['key' => 'elevator', 'label' => 'Thang máy'],
            ['key' => 'gym', 'label' => 'Gym'],
            ['key' => 'restaurant', 'label' => 'Nhà hàng'],
            ['key' => 'bar', 'label' => 'Quầy bar'],
            ['key' => 'laundry', 'label' => 'Giặt ủi'],
            ['key' => 'pool', 'label' => 'Bể bơi'],
            ['key' => 'spa', 'label' => 'Spa'],
            ['key' => 'concierge', 'label' => 'Lễ tân 24/7'],
            ['key' => 'room_service', 'label' => 'Phục vụ phòng'],
            ['key' => 'view', 'label' => 'View đẹp'],
        ];
    }
}
