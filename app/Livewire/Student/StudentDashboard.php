<?php

namespace App\Livewire\Student;

use App\Models\Attendance;
use App\Models\Payment;
use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title("O'quvchi Dashboard")]
class StudentDashboard extends Component
{
    #[Computed]
    public function student(): ?Student
    {
        return auth()->user()->student;
    }

    #[Computed]
    public function stats(): array
    {
        $student = $this->student;

        if (! $student) {
            return [];
        }

        $activeEnrollments = $student->enrollments()->where('status', 'active')->count();
        $completedEnrollments = $student->enrollments()->where('status', 'completed')->count();

        // Davomat statistikasi
        $totalAttendance = Attendance::whereHas('enrollment', fn ($q) => $q->where('student_id', $student->id))->count();
        $presentCount = Attendance::whereHas('enrollment', fn ($q) => $q->where('student_id', $student->id))->where('present', true)->count();
        $attendanceRate = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;

        return [
            'active_groups' => $activeEnrollments,
            'completed_groups' => $completedEnrollments,
            'attendance_rate' => $attendanceRate,
        ];
    }

    #[Computed]
    public function activeEnrollments()
    {
        $student = $this->student;

        if (! $student) {
            return collect();
        }

        return $student->enrollments()
            ->where('status', 'active')
            ->with(['group.course', 'group.teacher', 'group.room'])
            ->get();
    }

    #[Computed]
    public function todayGroups()
    {
        $student = $this->student;

        if (! $student) {
            return collect();
        }

        $today = now()->dayOfWeek;
        $isOddDay = in_array($today, [1, 3, 5]); // Mon, Wed, Fri

        return $student->enrollments()
            ->where('status', 'active')
            ->whereHas('group', function ($q) use ($isOddDay) {
                $q->whereIn('status', ['active', 'pending'])
                    ->where('days', $isOddDay ? 'odd' : 'even');
            })
            ->with(['group.course', 'group.teacher', 'group.room'])
            ->get()
            ->sortBy('group.start_time');
    }

    #[Computed]
    public function recentPayments()
    {
        $student = $this->student;

        if (! $student) {
            return collect();
        }

        return Payment::whereHas('enrollment', fn ($q) => $q->where('student_id', $student->id))
            ->with('enrollment.group.course')
            ->latest('paid_at')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.student.dashboard');
    }
}
