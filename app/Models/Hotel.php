<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'slug', 'district', 'address', 'lat', 'lng', 'stars', 'base_price',
    'rating', 'reviews_count', 'description', 'amenities', 'phone', 'email',
    'has_vr_tour', 'vr_tour_url', 'is_active',
])]
class Hotel extends Model
{
    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'has_vr_tour' => 'boolean',
            'is_active' => 'boolean',
            'lat' => 'decimal:6',
            'lng' => 'decimal:6',
            'rating' => 'decimal:1',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(HotelImage::class);
    }

    public function primaryImage()
    {
        return $this->hasOne(HotelImage::class)->where('is_primary', true);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
