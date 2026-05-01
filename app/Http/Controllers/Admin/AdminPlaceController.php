<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminPlaceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Place::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        $places = $query->orderBy('district')->paginate(20)->withQueryString();

        $places->getCollection()->transform(fn ($p) => [
            'id' => $p->id,
            'name' => $this->utf8($p->name),
            'type' => $p->type,
            'district' => $this->utf8($p->district),
            'address' => $this->utf8($p->address),
            'description' => $this->utf8($p->description),
            'rating' => $p->rating,
            'avg_price' => $p->avg_price,
            'image_url' => $p->image_url,
            'is_active' => $p->is_active,
        ]);

        return Inertia::render('Admin/Places/Index', [
            'places' => $places,
            'filters' => $request->only(['q', 'type', 'district']),
            'districts' => Place::distinct()->orderBy('district')->pluck('district')
                ->map(fn ($d) => $this->utf8($d)),
        ]);
    }

    private function utf8(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Places/Form', ['place' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);
        Place::create($validated);

        return redirect()->route('admin.places.index')->with('success', 'Đã tạo địa điểm.');
    }

    public function edit(Place $place): Response
    {
        return Inertia::render('Admin/Places/Form', ['place' => $place]);
    }

    public function update(Request $request, Place $place): RedirectResponse
    {
        $validated = $this->validateData($request);
        $place->update($validated);

        return redirect()->route('admin.places.index')->with('success', 'Đã cập nhật địa điểm.');
    }

    public function destroy(Place $place): RedirectResponse
    {
        $place->delete();

        return back()->with('success', 'Đã xóa địa điểm.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:restaurant,cafe,attraction,shopping,bar,spa'],
            'district' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'avg_price' => ['nullable', 'integer', 'min:0'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'is_active' => ['boolean'],
        ]);
    }
}
