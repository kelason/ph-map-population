<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhilippinesMapController;

Route::get('/', [PhilippinesMapController::class, 'index']);
