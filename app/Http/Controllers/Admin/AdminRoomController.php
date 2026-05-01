<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminRoomController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Room::query()->with('hotel:id,name,district');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhereHas('hotel', fn ($h) => $h->where('name', 'like', "%{$q}%"));
            });
        }
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        match ($request->input('sort', 'price-asc')) {
            'price-desc' => $query->orderByDesc('price_per_night'),
            'capacity' => $query->orderByDesc('capacity'),
            default => $query->orderBy('price_per_night'),
        };

        $rooms = $query->paginate(20)->withQueryString();
        $rooms->getCollection()->transform(fn ($r) => [
            'id' => $r->id,
            'name' => $r->name,
            'description' => $r->description,
            'price_per_night' => $r->price_per_night,
            'capacity' => $r->capacity,
            'available_units' => $r->available_units,
            'is_active' => $r->is_active,
            'hotel' => ['id' => $r->hotel->id, 'name' => $r->hotel->name, 'district' => $r->hotel->district],
        ]);

        return Inertia::render('Admin/Rooms/Index', [
            'rooms' => $rooms,
            'filters' => $request->only(['q', 'hotel_id', 'sort']),
            'hotels' => Hotel::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'price_per_night' => ['required', 'integer', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1', 'max:10'],
            'available_units' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $room->update($validated);

        return back()->with('success', "Đã cập nhật phòng \"{$room->name}\".");
    }

    public function destroy(Room $room): RedirectResponse
    {
        $name = $room->name;
        $room->delete();

        return back()->with('success', "Đã xóa phòng \"{$name}\".");
    }
}
