<?php

use App\Livewire\Admin\Courses;
use App\Livewire\Admin\Groups;
use App\Livewire\Admin\Rooms;
use App\Livewire\Admin\Teachers;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/teachers', Teachers::class)->name('teachers');
    Route::get('/courses', Courses::class)->name('courses');
    Route::get('/rooms', Rooms::class)->name('rooms');
    Route::get('/groups', Groups::class)->name('groups');
});
