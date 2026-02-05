<?php

namespace App\Livewire\Teacher;

use App\Models\Attendance as AttendanceModel;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Teacher;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Davomat')]
class TeacherAttendance extends Component
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
    public function teacher(): ?Teacher
    {
        return auth()->user()->teacher;
    }

    #[Computed]
    public function groups()
    {
        $teacher = $this->teacher;

        if (! $teacher) {
            return collect();
        }

        return $teacher->groups()
            ->with(['course'])
            ->whereIn('status', ['active', 'pending'])
            ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')])
            ->get();
    }

    #[Computed]
    public function selectedGroup(): ?Group
    {
        if (! $this->group_id) {
            return null;
        }

        $group = Group::with(['course', 'room'])->find($this->group_id);

        if ($group && $group->teacher_id !== $this->teacher?->id) {
            return null;
        }

        return $group;
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
    public function lessonDates(): array
    {
        if (! $this->group_id || ! $this->selectedGroup) {
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

        for ($i = 1; $i <= max($maxLesson, $this->selectedGroup->total_lessons ?? 12); $i++) {
            $lessons[$i] = $existingLessons->has($i)
                ? $existingLessons[$i]->date->format('d.m.Y')
                : null;
        }

        return $lessons;
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

    public function updatedGroupId(): void
    {
        $this->attendance = [];
        $this->lesson_number = 1;
        $this->findNextLesson();
    }

    public function updatedLessonNumber(): void
    {
        $this->loadAttendance();
    }

    public function findNextLesson(): void
    {
        foreach ($this->lessonDates as $num => $date) {
            if ($date === null) {
                $this->lesson_number = $num;
                $this->loadAttendance();

                return;
            }
        }
        $this->lesson_number = 1;
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
        return view('livewire.teacher.attendance');
    }
}
