<?php

use App\Livewire\Admin\Attendance;
use App\Livewire\Admin\Courses;
use App\Livewire\Admin\Discounts;
use App\Livewire\Admin\GroupDetail;
use App\Livewire\Admin\Groups;
use App\Livewire\Admin\Leads;
use App\Livewire\Admin\Rooms;
use App\Livewire\Admin\Students;
use App\Livewire\Admin\Teachers;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:manager'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/teachers', Teachers::class)->name('teachers');
    Route::get('/courses', Courses::class)->name('courses');
    Route::get('/rooms', Rooms::class)->name('rooms');
    Route::get('/groups', Groups::class)->name('groups');
    Route::get('/groups/{group}', GroupDetail::class)->name('groups.show');
    Route::get('/students', Students::class)->name('students');
    Route::get('/leads', Leads::class)->name('leads');
    Route::get('/discounts', Discounts::class)->name('discounts');
    Route::get('/attendance', Attendance::class)->name('attendance');
});
