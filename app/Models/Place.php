<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'type', 'district', 'address', 'description', 'rating',
    'avg_price', 'image_url', 'lat', 'lng', 'is_active',
])]
class Place extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:1',
            'lat' => 'decimal:6',
            'lng' => 'decimal:6',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
        ];
    }
}
