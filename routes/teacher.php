<?php

use App\Livewire\Teacher\TeacherAttendance;
use App\Livewire\Teacher\TeacherDashboard;
use App\Livewire\Teacher\TeacherFinance;
use App\Livewire\Teacher\TeacherGroupDetail;
use App\Livewire\Teacher\TeacherSchedule;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboard::class)->name('dashboard');
    Route::get('/schedule', TeacherSchedule::class)->name('schedule');
    Route::get('/groups/{group}', TeacherGroupDetail::class)->name('groups.show');
    Route::get('/attendance', TeacherAttendance::class)->name('attendance');
    Route::get('/finance', TeacherFinance::class)->name('finance');
});
