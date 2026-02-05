<?php

use App\Livewire\Dashboard;
use App\Livewire\Help;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('dashboard', Dashboard::class)
    ->middleware(['auth', 'verified', 'role:manager'])
    ->name('dashboard');

Route::get('help', Help::class)
    ->middleware(['auth', 'verified'])
    ->name('help');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
require __DIR__.'/teacher.php';
require __DIR__.'/student.php';
