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

    public function payment(Booking $booking, Request $request): Response
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        $booking->load(['hotel:id,name,slug,district,address', 'room:id,name,price_per_night']);
        $heroImage = $booking->hotel->images()->orderByDesc('is_primary')->value('url');

        return Inertia::render('Booking/Payment', [
            'booking' => [
                'booking_code' => $booking->booking_code,
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
                'hotel' => [
                    'name' => $booking->hotel->name,
                    'district' => $booking->hotel->district,
                    'address' => $booking->hotel->address,
                    'image' => $heroImage,
                ],
                'room' => [
                    'name' => $booking->room->name,
                ],
            ],
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
            Mail::to($booking->guest_email)->send(new BookingConfirmation($booking->fresh(['hotel', 'room', 'payment'])));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('booking.confirmation', $booking->booking_code);
    }

    public function confirmation(Booking $booking, Request $request): Response
    {
        abort_if($booking->user_id !== $request->user()->id, 403);

        $booking->load(['hotel:id,name,slug,district,address', 'room:id,name', 'payment']);
        $heroImage = $booking->hotel->images()->orderByDesc('is_primary')->value('url');

        return Inertia::render('Booking/Confirmation', [
            'booking' => [
                'booking_code' => $booking->booking_code,
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
                'created_at' => $booking->created_at->format('Y-m-d H:i'),
                'hotel' => [
                    'name' => $booking->hotel->name,
                    'slug' => $booking->hotel->slug,
                    'district' => $booking->hotel->district,
                    'address' => $booking->hotel->address,
                    'image' => $heroImage,
                ],
                'room' => [
                    'name' => $booking->room->name,
                ],
                'payment' => $booking->payment ? [
                    'method' => $booking->payment->method,
                    'transaction_ref' => $booking->payment->transaction_ref,
                    'paid_at' => $booking->payment->paid_at?->format('Y-m-d H:i'),
                ] : null,
            ],
        ]);
    }
}
