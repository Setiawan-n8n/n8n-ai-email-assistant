<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'side',
        'is_own_station',
        'keterangan',
    ];

    protected $casts = [
        'is_own_station' => 'boolean',
    ];

    public function scheduleAsOrigin()
    {
        return $this->hasMany(TrainSchedule::class, 'relasi_asal_id');
    }

    public function scheduleAsDestination()
    {
        return $this->hasMany(TrainSchedule::class, 'relasi_tujuan_id');
    }
}
