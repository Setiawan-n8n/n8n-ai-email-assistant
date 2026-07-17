<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signal extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'track_id',
        'side',
        'jenis',
        'posisi_km',
        'pos_x',
        'pos_y',
        'keterangan',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}
