<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'booking_code', 'user_id', 'hotel_id', 'room_id', 'guest_name',
    'guest_email', 'guest_phone', 'checkin_date', 'checkout_date', 'nights',
    'guests_count', 'subtotal', 'tax', 'total_amount', 'status',
    'special_requests',
])]
class Booking extends Model
{
    protected function casts(): array
    {
        return [
            'checkin_date' => 'date',
            'checkout_date' => 'date',
        ];
    }

    public static function generateCode(): string
    {
        do {
            $code = 'SS' . strtoupper(bin2hex(random_bytes(4)));
        } while (self::where('booking_code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function getRouteKeyName(): string
    {
        return 'booking_code';
    }
}
