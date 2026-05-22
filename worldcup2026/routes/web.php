<?php

use App\Http\Controllers\WorldCupController;
use Illuminate\Support\Facades\Route;

Route::get('/',              [WorldCupController::class, 'index'])->name('worldcup.index');
Route::get('/api/snapshot',  [WorldCupController::class, 'snapshot'])->name('worldcup.snapshot');
