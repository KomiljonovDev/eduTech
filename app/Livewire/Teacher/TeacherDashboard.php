<?php

namespace App\Livewire\Teacher;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Teacher;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Ustoz Dashboard')]
class TeacherDashboard extends Component
{
    #[Computed]
    public function teacher(): ?Teacher
    {
        return auth()->user()->teacher;
    }

    #[Computed]
    public function stats(): array
    {
        $teacher = $this->teacher;

        if (! $teacher) {
            return [];
        }

        $activeGroups = $teacher->groups()->where('status', 'active')->count();
        $pendingGroups = $teacher->groups()->where('status', 'pending')->count();

        $totalStudents = Enrollment::whereHas('group', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->where('status', 'active')
            ->count();

        return [
            'active_groups' => $activeGroups,
            'pending_groups' => $pendingGroups,
            'total_students' => $totalStudents,
        ];
    }

    #[Computed]
    public function todayGroups()
    {
        $teacher = $this->teacher;

        if (! $teacher) {
            return collect();
        }

        $today = now()->dayOfWeek;
        $isOddDay = in_array($today, [1, 3, 5]); // Mon, Wed, Fri

        return $teacher->groups()
            ->with(['course', 'room'])
            ->whereIn('status', ['active', 'pending'])
            ->where('days', $isOddDay ? 'odd' : 'even')
            ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('start_time')
            ->get();
    }

    #[Computed]
    public function recentPayments()
    {
        $teacher = $this->teacher;

        if (! $teacher) {
            return collect();
        }

        return Payment::with(['enrollment.student', 'enrollment.group.course'])
            ->whereHas('enrollment.group', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->latest('paid_at')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function attendanceToday(): array
    {
        $teacher = $this->teacher;

        if (! $teacher) {
            return ['marked' => 0, 'total' => 0];
        }

        $today = now()->format('Y-m-d');

        $groupIds = $teacher->groups()->whereIn('status', ['active', 'pending'])->pluck('id');

        $total = Enrollment::whereIn('group_id', $groupIds)->where('status', 'active')->count();
        $marked = Attendance::whereDate('date', $today)
            ->whereHas('enrollment', fn ($q) => $q->whereIn('group_id', $groupIds))
            ->distinct('enrollment_id')
            ->count('enrollment_id');

        return [
            'marked' => $marked,
            'total' => $total,
        ];
    }

    public function render()
    {
        return view('livewire.teacher.dashboard');
    }
}
