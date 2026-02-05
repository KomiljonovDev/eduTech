<?php

namespace App\Livewire\Student;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Payment;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class StudentGroupDetail extends Component
{
    public Group $group;

    public Enrollment $enrollment;

    public function mount(Group $group): void
    {
        $student = auth()->user()->student;

        if (! $student) {
            abort(403, 'O\'quvchi profili topilmadi');
        }

        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('group_id', $group->id)
            ->first();

        if (! $enrollment) {
            abort(403, 'Bu guruhga kirishga ruxsat yo\'q');
        }

        $this->group = $group->load(['course', 'teacher', 'room']);
        $this->enrollment = $enrollment;
    }

    public function getTitle(): string
    {
        return $this->group->name.' - '.$this->group->course->name;
    }

    #[Computed]
    public function attendanceStats(): array
    {
        $attendances = Attendance::where('enrollment_id', $this->enrollment->id)->get();
        $total = $attendances->count();
        $present = $attendances->where('present', true)->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $total - $present,
            'percentage' => $total > 0 ? round(($present / $total) * 100) : 0,
        ];
    }

    #[Computed]
    public function attendanceHistory()
    {
        return Attendance::where('enrollment_id', $this->enrollment->id)
            ->orderBy('lesson_number')
            ->get();
    }

    #[Computed]
    public function payments()
    {
        return Payment::where('enrollment_id', $this->enrollment->id)
            ->latest('paid_at')
            ->get();
    }

    #[Computed]
    public function lessonDates(): array
    {
        $existingLessons = Attendance::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group->id))
            ->select('lesson_number', 'date')
            ->distinct()
            ->orderBy('lesson_number')
            ->get()
            ->keyBy('lesson_number');

        $lessons = [];
        $maxLesson = max($existingLessons->keys()->max() ?? 0, 1);

        for ($i = 1; $i <= max($maxLesson, $this->group->total_lessons ?? 12); $i++) {
            $lessons[$i] = $existingLessons->has($i)
                ? $existingLessons[$i]->date->format('d.m.Y')
                : null;
        }

        return $lessons;
    }

    public function render()
    {
        return view('livewire.student.group-detail');
    }
}
