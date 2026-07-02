<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ChatSession;
use App\Models\Hotel;
use App\Models\Place;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    public function index(): Response
    {
        $monthStart = Carbon::now()->startOfMonth();
        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $monthRevenue = (int) Booking::where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $monthStart)
            ->sum('total_amount');
        $lastMonthRevenue = (int) Booking::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('total_amount');
        $revenueDelta = $lastMonthRevenue > 0 ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;

        $totalBookings = Booking::count();
        $todayBookings = Booking::whereDate('created_at', today())->count();
        $totalUsers = User::where('role', 'customer')->count();
        $newUsersThisWeek = User::where('role', 'customer')->where('created_at', '>=', now()->startOfWeek())->count();

        $todayChats = ChatSession::whereDate('started_at', today())->count();

        // Bookings by week (last 7 days)
        $bookingsByDay = collect();
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $bookingsByDay->push([
                'label' => $day->isoFormat('dd'),
                'date' => $day->format('Y-m-d'),
                'completed' => Booking::whereDate('created_at', $day)->where('status', 'completed')->count(),
                'confirmed' => Booking::whereDate('created_at', $day)->where('status', 'confirmed')->count(),
                'cancelled' => Booking::whereDate('created_at', $day)->where('status', 'cancelled')->count(),
                'total' => Booking::whereDate('created_at', $day)->count(),
            ]);
        }

        // District distribution
        $districtsRaw = Hotel::select('district', DB::raw('count(*) as count'))
            ->groupBy('district')
            ->orderByDesc('count')
            ->get();

        $top4 = $districtsRaw->take(4);
        $other = $districtsRaw->slice(4);

        $districts = $top4->map(fn ($d) => ['name' => $d->district, 'count' => $d->count])->values();
        if ($other->isNotEmpty()) {
            $districts->push(['name' => 'Quận khác', 'count' => $other->sum('count')]);
        }

        $recentBookings = Booking::with(['hotel:id,name,district', 'user:id,name'])
            ->orderByDesc('id')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'booking_code' => $b->booking_code,
                'guest_name' => $b->guest_name,
                'hotel_name' => $b->hotel?->name ?? 'N/A',
                'checkin_date' => $b->checkin_date->format('d/m'),
                'checkout_date' => $b->checkout_date->format('d/m'),
                'total_amount' => $b->total_amount,
                'status' => $b->status,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'kpis' => [
                'monthRevenue' => $monthRevenue,
                'revenueDelta' => $revenueDelta,
                'totalBookings' => $totalBookings,
                'todayBookings' => $todayBookings,
                'totalUsers' => $totalUsers,
                'newUsersThisWeek' => $newUsersThisWeek,
                'todayChats' => $todayChats,
            ],
            'bookingsByDay' => $bookingsByDay,
            'districts' => $districts,
            'totalHotels' => Hotel::count(),
            'recentBookings' => $recentBookings,
        ]);
    }
}
