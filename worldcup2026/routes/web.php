<?php

use App\Http\Controllers\WorldCupController;
use Illuminate\Support\Facades\Route;

Route::get('/',             [WorldCupController::class, 'index'])->name('worldcup.index');
Route::get('/groups',       [WorldCupController::class, 'groups'])->name('worldcup.groups');
Route::get('/bracket',      [WorldCupController::class, 'bracket'])->name('worldcup.bracket');
Route::get('/api/snapshot', [WorldCupController::class, 'snapshot'])->name('worldcup.snapshot');
