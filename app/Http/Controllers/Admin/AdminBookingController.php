<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminBookingController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Booking::with(['hotel:id,name,district', 'user:id,name,email', 'room:id,name', 'payment']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('booking_code', 'like', "%{$q}%")
                  ->orWhere('guest_name', 'like', "%{$q}%")
                  ->orWhere('guest_email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort by id DESC để booking mới insert luôn lên đầu, không phụ thuộc seed timestamps
        $bookings = $query->orderByDesc('id')->paginate(20)->withQueryString();
        $bookings->getCollection()->transform(fn ($b) => [
            'id' => $b->id,
            'booking_code' => $b->booking_code,
            'guest_name' => $b->guest_name,
            'guest_email' => $b->guest_email,
            'guest_phone' => $b->guest_phone,
            'checkin_date' => $b->checkin_date->format('Y-m-d'),
            'checkout_date' => $b->checkout_date->format('Y-m-d'),
            'nights' => $b->nights,
            'guests_count' => $b->guests_count,
            'total_amount' => $b->total_amount,
            'status' => $b->status,
            'created_at' => $b->created_at->format('Y-m-d H:i'),
            'hotel' => ['id' => $b->hotel->id, 'name' => $b->hotel->name, 'district' => $b->hotel->district],
            'room' => ['name' => $b->room->name],
            'payment' => $b->payment ? ['method' => $b->payment->method, 'status' => $b->payment->status] : null,
        ]);

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        $booking->update(['status' => $validated['status']]);

        return back()->with('success', "Đã cập nhật trạng thái đơn {$booking->booking_code}.");
    }
}
