<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmation;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $bookings = Booking::query()
            ->where('user_id', $request->user()->id)
            ->with(['hotel:id,name,slug,district', 'room:id,name', 'payment'])
            ->orderByDesc('id')
            ->paginate(10);

        $bookings->getCollection()->transform(fn (Booking $b) => [
            'id' => $b->id,
            'booking_code' => $b->booking_code,
            'booking_type' => $b->booking_type,
            'checkin_date' => $b->checkin_date->format('Y-m-d'),
            'checkout_date' => $b->checkout_date->format('Y-m-d'),
            'nights' => $b->nights,
            'guests_count' => $b->guests_count,
            'total_amount' => $b->total_amount,
            'status' => $b->status,
            'hotel' => $b->hotel ? [
                'name' => $b->hotel->name,
                'district' => $b->hotel->district,
            ] : null,
            'room' => $b->room ? ['name' => $b->room->name] : null,
            'combo' => $b->isCombo() ? $this->comboSummary($b->combo()) : null,
        ]);

        return Inertia::render('Booking/Index', [
            'bookings' => $bookings,
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $request->validate([
            'hotel' => ['required', 'string'],
            'room' => ['required', 'integer'],
        ]);

        $hotel = Hotel::where('slug', $request->hotel)->firstOrFail();
        $room = Room::where('hotel_id', $hotel->id)->where('id', $request->room)->firstOrFail();

        if (! Auth::check()) {
            return redirect()->route('login')->with('info', 'Vui lòng đăng nhập để đặt phòng.');
        }

        $today = Carbon::today();

        return Inertia::render('Booking/Create', [
            'hotel' => [
                'id' => $hotel->id,
                'name' => $hotel->name,
                'slug' => $hotel->slug,
                'district' => $hotel->district,
                'address' => $hotel->address,
                'image' => $hotel->images()->orderByDesc('is_primary')->value('url'),
            ],
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'price_per_night' => $room->price_per_night,
                'capacity' => $room->capacity,
                'available_units' => $room->available_units,
                'image' => $room->image,
            ],
            'defaults' => [
                'checkin_date' => $today->copy()->addDays(7)->format('Y-m-d'),
                'checkout_date' => $today->copy()->addDays(9)->format('Y-m-d'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'guest_name' => ['required', 'string', 'max:120'],
            'guest_email' => ['required', 'email', 'max:150'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'checkin_date' => ['required', 'date', 'after_or_equal:today'],
            'checkout_date' => ['required', 'date', 'after:checkin_date'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:10'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ]);

        $room = Room::where('hotel_id', $validated['hotel_id'])
            ->where('id', $validated['room_id'])
            ->firstOrFail();

        if ($validated['guests_count'] > $room->capacity) {
            return back()->withErrors(['guests_count' => "Phòng này chỉ phù hợp tối đa {$room->capacity} khách."])->withInput();
        }

        $checkin = Carbon::parse($validated['checkin_date']);
        $checkout = Carbon::parse($validated['checkout_date']);
        $nights = $checkin->diffInDays($checkout);

        $subtotal = $room->price_per_night * $nights;
        $tax = (int) round($subtotal * 0.10);
        $total = $subtotal + $tax;

        $booking = DB::transaction(function () use ($request, $validated, $room, $nights, $subtotal, $tax, $total) {
            return Booking::create([
                'booking_code' => Booking::generateCode(),
                'user_id' => $request->user()->id,
                'hotel_id' => $validated['hotel_id'],
                'room_id' => $room->id,
                'booking_type' => 'hotel',
                'guest_name' => $validated['guest_name'],
                'guest_email' => $validated['guest_email'],
                'guest_phone' => $validated['guest_phone'],
                'checkin_date' => $validated['checkin_date'],
                'checkout_date' => $validated['checkout_date'],
                'nights' => $nights,
                'guests_count' => $validated['guests_count'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $total,
                'status' => 'pending',
                'special_requests' => $validated['special_requests'] ?? null,
            ]);
        });

        return redirect()->route('booking.payment', $booking->booking_code);
    }

    public function createCombo(string $slug, Request $request): Response|RedirectResponse
    {
        $combo = collect(config('combos'))->firstWhere('slug', $slug);
        abort_if(! $combo, 404);

        if (! Auth::check()) {
            return redirect()->route('login')->with('info', 'Vui lòng đăng nhập để đặt tour.');
        }

        $hotels = Hotel::query()
            ->where('is_active', true)
            ->where('district', $combo['district'])
            ->whereBetween('base_price', [$combo['price_min'], $combo['price_max']])
            ->orderByDesc('rating')
            ->limit(8)
            ->get(['id', 'name', 'slug', 'district', 'base_price', 'rating'])
            ->map(fn (Hotel $h) => [
                'id' => $h->id,
                'name' => $h->name,
                'district' => $h->district,
                'base_price' => $h->base_price,
                'rating' => $h->rating,
            ])
            ->all();

        $today = Carbon::today();

        return Inertia::render('Booking/CreateCombo', [
            'combo' => $this->comboSummary($combo),
            'hotels' => $hotels,
            'defaults' => [
                'checkin_date' => $today->copy()->addDays(7)->format('Y-m-d'),
            ],
        ]);
    }

    public function storeCombo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'combo_slug' => ['required', 'string'],
            'hotel_id' => ['nullable', 'integer', 'exists:hotels,id'],
            'guest_name' => ['required', 'string', 'max:120'],
            'guest_email' => ['required', 'email', 'max:150'],
            'guest_phone' => ['required', 'string', 'max:20'],
            'checkin_date' => ['required', 'date', 'after_or_equal:today'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:10'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
        ]);

        $combo = collect(config('combos'))->firstWhere('slug', $validated['combo_slug']);
        abort_if(! $combo, 404, 'Combo không tồn tại.');

        $nights = (int) ($combo['nights'] ?? 1);
        $checkin = Carbon::parse($validated['checkin_date']);
        $checkout = $checkin->copy()->addDays($nights);

        $subtotal = (int) $combo['from_price'] * (int) $validated['guests_count'];
        $tax = (int) round($subtotal * 0.10);
        $total = $subtotal + $tax;

        $booking = DB::transaction(function () use ($request, $validated, $combo, $nights, $checkin, $checkout, $subtotal, $tax, $total) {
            return Booking::create([
                'booking_code' => Booking::generateCode(),
                'user_id' => $request->user()->id,
                'hotel_id' => $validated['hotel_id'] ?? null,
                'room_id' => null,
                'combo_slug' => $combo['slug'],
                'booking_type' => 'combo',
                'guest_name' => $validated['guest_name'],
                'guest_email' => $validated['guest_email'],
                'guest_phone' => $validated['guest_phone'],
                'checkin_date' => $checkin->format('Y-m-d'),
                'checkout_date' => $checkout->format('Y-m-d'),
                'nights' => $nights,
                'guests_count' => $validated['guests_count'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total_amount' => $total,
                'status' => 'pending',
                'special_requests' => $validated['special_requests'] ?? null,
            ]);
        });

        return redirect()->route('booking.payment', $booking->booking_code);
    }

    public function payment(Booking $booking, Request $request): Response
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        return Inertia::render('Booking/Payment', [
            'booking' => $this->bookingPayload($booking),
        ]);
    }

    public function processPayment(Booking $booking, Request $request): RedirectResponse
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'method' => ['required', 'in:credit_card,vnpay,momo,bank_transfer,cash_at_hotel'],
        ]);

        DB::transaction(function () use ($booking, $validated) {
            Payment::create([
                'booking_id' => $booking->id,
                'method' => $validated['method'],
                'amount' => $booking->total_amount,
                'status' => 'paid',
                'transaction_ref' => 'DEMO-' . strtoupper(bin2hex(random_bytes(6))),
                'paid_at' => now(),
            ]);
            $booking->update(['status' => 'confirmed']);
        });

        try {
            $relations = ['payment'];
            if ($booking->hotel_id) $relations[] = 'hotel';
            if ($booking->room_id) $relations[] = 'room';
            Mail::to($booking->guest_email)->send(new BookingConfirmation($booking->fresh($relations)));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('booking.confirmation', $booking->booking_code);
    }

    public function confirmation(Booking $booking, Request $request): Response
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        return Inertia::render('Booking/Confirmation', [
            'booking' => $this->bookingPayload($booking, includePayment: true, includeCreatedAt: true),
        ]);
    }

    private function bookingPayload(Booking $booking, bool $includePayment = true, bool $includeCreatedAt = false): array
    {
        $relations = [];
        if ($booking->hotel_id) $relations[] = 'hotel:id,name,slug,district,address';
        if ($booking->room_id) $relations[] = 'room:id,name,price_per_night';
        if ($includePayment) $relations[] = 'payment';
        if ($relations) $booking->load($relations);

        $hotelPayload = null;
        if ($booking->hotel) {
            $heroImage = $booking->hotel->images()->orderByDesc('is_primary')->value('url');
            $hotelPayload = [
                'name' => $booking->hotel->name,
                'slug' => $booking->hotel->slug,
                'district' => $booking->hotel->district,
                'address' => $booking->hotel->address,
                'image' => $heroImage,
            ];
        }

        $payload = [
            'booking_code' => $booking->booking_code,
            'booking_type' => $booking->booking_type,
            'guest_name' => $booking->guest_name,
            'guest_email' => $booking->guest_email,
            'guest_phone' => $booking->guest_phone,
            'checkin_date' => $booking->checkin_date->format('Y-m-d'),
            'checkout_date' => $booking->checkout_date->format('Y-m-d'),
            'nights' => $booking->nights,
            'guests_count' => $booking->guests_count,
            'subtotal' => $booking->subtotal,
            'tax' => $booking->tax,
            'total_amount' => $booking->total_amount,
            'status' => $booking->status,
            'special_requests' => $booking->special_requests,
            'hotel' => $hotelPayload,
            'room' => $booking->room ? ['name' => $booking->room->name] : null,
            'combo' => $booking->isCombo() ? $this->comboSummary($booking->combo()) : null,
        ];

        if ($includeCreatedAt) {
            $payload['created_at'] = $booking->created_at->format('Y-m-d H:i');
        }

        if ($includePayment) {
            $payload['payment'] = $booking->payment ? [
                'method' => $booking->payment->method,
                'transaction_ref' => $booking->payment->transaction_ref,
                'paid_at' => $booking->payment->paid_at?->format('Y-m-d H:i'),
            ] : null;
        }

        return $payload;
    }

    private function comboSummary(?array $combo): ?array
    {
        if (! $combo) return null;

        return [
            'slug' => $combo['slug'],
            'title' => $combo['title'],
            'tagline' => $combo['tagline'] ?? null,
            'duration' => $combo['duration'],
            'nights' => $combo['nights'] ?? 1,
            'from_price' => $combo['from_price'],
            'district' => $combo['district'],
            'image' => $combo['image'],
            'description' => $combo['description'] ?? null,
            'highlights' => $combo['highlights'] ?? [],
        ];
    }
}
