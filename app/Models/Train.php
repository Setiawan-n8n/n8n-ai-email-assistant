<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Train extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_ka',
        'nama',
        'kategori',
        'keterangan',
    ];

    public function schedules()
    {
        return $this->hasMany(TrainSchedule::class);
    }
}
