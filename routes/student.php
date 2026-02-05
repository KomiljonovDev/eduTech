<?php

use App\Livewire\Student\StudentDashboard;
use App\Livewire\Student\StudentGroupDetail;
use App\Livewire\Student\StudentPayments;
use App\Livewire\Student\StudentSchedule;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', StudentDashboard::class)->name('dashboard');
    Route::get('/schedule', StudentSchedule::class)->name('schedule');
    Route::get('/groups/{group}', StudentGroupDetail::class)->name('groups.show');
    Route::get('/payments', StudentPayments::class)->name('payments');
});
