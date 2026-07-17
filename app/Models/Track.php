<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'jenis',
        'sort_order',
        'keterangan',
    ];

    public function signals()
    {
        return $this->hasMany(Signal::class);
    }

    public function schedules()
    {
        return $this->hasMany(TrainSchedule::class);
    }
}
