<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourierLocation extends Model
{
    protected $fillable = [
        'courier_profile_id',
        'latitude',
        'longitude',
        'heading',
        'speed_kmh'
    ];

    public function courier(): BelongsTo
    {
        return $this->belongsTo(CourierProfile::class);
    }
}
