<?php

use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminHotelController;
use App\Http\Controllers\Admin\AdminMiscController;
use App\Http\Controllers\Admin\AdminPlaceController;
use App\Http\Controllers\Admin\AdminRoomController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ComboController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/hotels', [HotelController::class, 'index'])->name('hotels.index');
Route::get('/hotels/{hotel:slug}', [HotelController::class, 'show'])->name('hotels.show');

Route::get('/combos', [ComboController::class, 'index'])->name('combos.index');
Route::get('/combos/{slug}', [ComboController::class, 'show'])->name('combos.show');

Route::get('/promotions', PromotionController::class)->name('promotions');

Route::get('/support', [SupportController::class, 'index'])->name('support');
Route::post('/support', [SupportController::class, 'submit'])->name('support.submit');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
Route::get('/booking/combo/{slug}/create', [BookingController::class, 'createCombo'])->name('booking.combo.create');

Route::middleware('auth')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
    Route::post('/booking/combo', [BookingController::class, 'storeCombo'])->name('booking.combo.store');
    Route::get('/booking/{booking:booking_code}/payment', [BookingController::class, 'payment'])->name('booking.payment');
    Route::post('/booking/{booking:booking_code}/payment', [BookingController::class, 'processPayment'])->name('booking.process');
    Route::get('/booking/{booking:booking_code}/confirmation', [BookingController::class, 'confirmation'])->name('booking.confirmation');
});

Route::post('/api/chat/start', [ChatController::class, 'start'])->name('chat.start');
Route::post('/api/chat/message', [ChatController::class, 'message'])->name('chat.message');
Route::post('/api/chat/places', [ChatController::class, 'places'])->name('chat.places');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [AdminMiscController::class, 'analytics'])->name('analytics');

    Route::get('/hotels', [AdminHotelController::class, 'index'])->name('hotels.index');
    Route::get('/hotels/create', [AdminHotelController::class, 'create'])->name('hotels.create');
    Route::post('/hotels', [AdminHotelController::class, 'store'])->name('hotels.store');
    Route::get('/hotels/{hotel:id}/edit', [AdminHotelController::class, 'edit'])->name('hotels.edit');
    Route::put('/hotels/{hotel:id}', [AdminHotelController::class, 'update'])->name('hotels.update');
    Route::delete('/hotels/{hotel:id}', [AdminHotelController::class, 'destroy'])->name('hotels.destroy');
    Route::patch('/hotels/{hotel:id}/toggle', [AdminHotelController::class, 'toggleActive'])->name('hotels.toggle');
    Route::post('/hotels/{hotel:id}/images', [AdminHotelController::class, 'addImage'])->name('hotels.images.store');
    Route::delete('/hotels/{hotel:id}/images/{image}', [AdminHotelController::class, 'deleteImage'])->name('hotels.images.destroy');

    Route::get('/rooms', [AdminRoomController::class, 'index'])->name('rooms.index');
    Route::put('/rooms/{room}', [AdminRoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [AdminRoomController::class, 'destroy'])->name('rooms.destroy');

    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::put('/bookings/{booking:id}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');

    Route::get('/places', [AdminPlaceController::class, 'index'])->name('places.index');
    Route::get('/places/create', [AdminPlaceController::class, 'create'])->name('places.create');
    Route::post('/places', [AdminPlaceController::class, 'store'])->name('places.store');
    Route::get('/places/{place}/edit', [AdminPlaceController::class, 'edit'])->name('places.edit');
    Route::put('/places/{place}', [AdminPlaceController::class, 'update'])->name('places.update');
    Route::delete('/places/{place}', [AdminPlaceController::class, 'destroy'])->name('places.destroy');

    Route::get('/reviews', [AdminMiscController::class, 'reviews'])->name('reviews.index');
    Route::get('/chats', [AdminMiscController::class, 'chats'])->name('chats.index');
    Route::get('/chats/{session}', [AdminMiscController::class, 'chatTranscript'])->name('chats.show');
    Route::get('/settings', [AdminMiscController::class, 'settings'])->name('settings');
});
