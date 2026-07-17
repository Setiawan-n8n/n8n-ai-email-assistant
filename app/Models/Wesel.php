<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wesel extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'track_from_id',
        'track_to_id',
        'side',
        'posisi_km',
        'pos_x',
        'pos_y',
        'keterangan',
    ];

    public function trackFrom()
    {
        return $this->belongsTo(Track::class, 'track_from_id');
    }

    public function trackTo()
    {
        return $this->belongsTo(Track::class, 'track_to_id');
    }
}
