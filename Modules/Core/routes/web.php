<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Livewire\Dashboard;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});
