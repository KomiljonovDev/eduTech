<?php

namespace App\Livewire\Teacher;

use App\Models\Attendance as AttendanceModel;
use App\Models\Enrollment;
use App\Models\Group;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class TeacherGroupDetail extends Component
{
    public Group $group;

    public string $activeTab = 'students';

    public int $lesson_number = 1;

    public string $lesson_date = '';

    public array $attendance = [];

    public string $paymentPeriod = '';

    public function mount(Group $group): void
    {
        $teacher = auth()->user()->teacher;

        if (! $teacher || $group->teacher_id !== $teacher->id) {
            abort(403, 'Bu guruhga kirishga ruxsat yo\'q');
        }

        $this->group = $group->load(['course', 'teacher', 'room']);
        $this->lesson_date = now()->format('Y-m-d');
        $this->paymentPeriod = now()->format('Y-m');
        $this->findNextLesson();
    }

    public function getTitle(): string
    {
        return $this->group->name.' - '.$this->group->course->name;
    }

    #[Computed]
    public function enrollments()
    {
        return Enrollment::with(['student.phones', 'attendances'])
            ->where('group_id', $this->group->id)
            ->where('status', 'active')
            ->get();
    }

    #[Computed]
    public function enrollmentsWithPayments()
    {
        return Enrollment::with(['student.phones', 'payments' => function ($query) {
            $query->where('period', $this->paymentPeriod);
        }])
            ->where('group_id', $this->group->id)
            ->where('status', 'active')
            ->get();
    }

    #[Computed]
    public function paymentStats(): array
    {
        $enrollments = $this->enrollmentsWithPayments;
        $totalStudents = $enrollments->count();
        $paidStudents = 0;
        $totalAmount = 0;

        foreach ($enrollments as $enrollment) {
            $paymentAmount = $enrollment->payments->sum('amount');
            if ($paymentAmount > 0) {
                $paidStudents++;
                $totalAmount += $paymentAmount;
            }
        }

        return [
            'total_students' => $totalStudents,
            'paid_students' => $paidStudents,
            'unpaid_students' => $totalStudents - $paidStudents,
            'total_amount' => $totalAmount,
            'payment_rate' => $totalStudents > 0 ? round(($paidStudents / $totalStudents) * 100) : 0,
        ];
    }

    #[Computed]
    public function lessonDates(): array
    {
        $existingLessons = AttendanceModel::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group->id))
            ->select('lesson_number', 'date')
            ->distinct()
            ->orderBy('lesson_number')
            ->get()
            ->keyBy('lesson_number');

        $lessons = [];
        $maxLesson = max($existingLessons->keys()->max() ?? 0, $this->lesson_number);

        for ($i = 1; $i <= max($maxLesson, $this->group->total_lessons ?? 12); $i++) {
            $lessons[$i] = $existingLessons->has($i)
                ? $existingLessons[$i]->date->format('d.m.Y')
                : null;
        }

        return $lessons;
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

    public function updatedLessonNumber(): void
    {
        $this->loadAttendance();
    }

    public function loadAttendance(): void
    {
        $this->attendance = [];

        $existingAttendance = AttendanceModel::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group->id))
            ->where('lesson_number', $this->lesson_number)
            ->get()
            ->keyBy('enrollment_id');

        foreach ($this->enrollments as $enrollment) {
            $existing = $existingAttendance->get($enrollment->id);
            $this->attendance[$enrollment->id] = $existing ? $existing->present : false;
        }

        $existingRecord = $existingAttendance->first();
        if ($existingRecord) {
            $this->lesson_date = $existingRecord->date->format('Y-m-d');
        }
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

    public function saveAttendance(): void
    {
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
        return view('livewire.teacher.group-detail');
    }
}
