<?php

use App\Http\Controllers\Api\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::get('/schedule', [ScheduleController::class, 'index']);
