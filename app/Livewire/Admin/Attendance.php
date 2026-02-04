<?php

namespace App\Livewire\Admin;

use App\Models\Attendance as AttendanceModel;
use App\Models\Enrollment;
use App\Models\Group;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Davomat')]
class Attendance extends Component
{
    #[Url]
    public string $group_id = '';

    public string $lesson_date = '';

    public int $lesson_number = 1;

    public array $attendance = [];

    public function mount(): void
    {
        $this->lesson_date = now()->format('Y-m-d');
    }

    #[Computed]
    public function groups()
    {
        return Group::with(['course', 'teacher'])
            ->whereIn('status', ['active', 'pending'])
            ->whereHas('enrollments', fn ($q) => $q->where('status', 'active'))
            ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')])
            ->get();
    }

    #[Computed]
    public function selectedGroup()
    {
        return $this->group_id ? Group::with(['course', 'teacher', 'room'])->find($this->group_id) : null;
    }

    #[Computed]
    public function enrollments()
    {
        if (! $this->group_id) {
            return collect();
        }

        return Enrollment::with(['student', 'attendances'])
            ->where('group_id', $this->group_id)
            ->where('status', 'active')
            ->get();
    }

    #[Computed]
    public function existingAttendance()
    {
        if (! $this->group_id || ! $this->lesson_number) {
            return collect();
        }

        return AttendanceModel::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group_id))
            ->where('lesson_number', $this->lesson_number)
            ->get()
            ->keyBy('enrollment_id');
    }

    #[Computed]
    public function lessonDates()
    {
        if (! $this->group_id) {
            return [];
        }

        $group = $this->selectedGroup;
        if (! $group) {
            return [];
        }

        $existingLessons = AttendanceModel::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group_id))
            ->select('lesson_number', 'date')
            ->distinct()
            ->orderBy('lesson_number')
            ->get()
            ->keyBy('lesson_number');

        $lessons = [];
        $maxLesson = max($existingLessons->keys()->max() ?? 0, $this->lesson_number);

        for ($i = 1; $i <= max($maxLesson, $group->total_lessons ?? 12); $i++) {
            $lessons[$i] = $existingLessons->has($i)
                ? $existingLessons[$i]->date->format('d.m.Y')
                : null;
        }

        return $lessons;
    }

    public function updatedGroupId(): void
    {
        $this->attendance = [];
        $this->loadAttendance();
    }

    public function updatedLessonNumber(): void
    {
        $this->loadAttendance();
    }

    public function loadAttendance(): void
    {
        $this->attendance = [];

        foreach ($this->enrollments as $enrollment) {
            $existing = $this->existingAttendance->get($enrollment->id);
            $this->attendance[$enrollment->id] = $existing ? $existing->present : false;
        }

        $existingRecord = $this->existingAttendance->first();
        if ($existingRecord) {
            $this->lesson_date = $existingRecord->date->format('Y-m-d');
        }
    }

    public function saveAttendance(): void
    {
        if (! $this->group_id) {
            return;
        }

        foreach ($this->enrollments as $enrollment) {
            AttendanceModel::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'lesson_number' => $this->lesson_number,
                ],
                [
                    'date' => $this->lesson_date,
                    'present' => $this->attendance[$enrollment->id] ?? false,
                ]
            );
        }

        $this->dispatch('attendance-saved');
    }

    public function toggleAttendance(int $enrollmentId): void
    {
        $this->attendance[$enrollmentId] = ! ($this->attendance[$enrollmentId] ?? false);
    }

    public function markAllPresent(): void
    {
        foreach ($this->enrollments as $enrollment) {
            $this->attendance[$enrollment->id] = true;
        }
    }

    public function markAllAbsent(): void
    {
        foreach ($this->enrollments as $enrollment) {
            $this->attendance[$enrollment->id] = false;
        }
    }

    public function getAttendanceStats(Enrollment $enrollment): array
    {
        $total = $enrollment->attendances->count();
        $present = $enrollment->attendances->where('present', true)->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $total - $present,
            'percentage' => $total > 0 ? round(($present / $total) * 100) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.admin.attendance');
    }
}
