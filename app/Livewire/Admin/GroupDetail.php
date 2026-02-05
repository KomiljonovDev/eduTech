<?php

namespace App\Livewire\Admin;

use App\Models\Attendance as AttendanceModel;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\OutstandingDebt;
use App\Models\Payment;
use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class GroupDetail extends Component
{
    public Group $group;

    public string $activeTab = 'students';

    // Payment modal
    public bool $showPaymentModal = false;

    public ?int $paymentEnrollmentId = null;

    public string $amount = '';

    public string $method = 'cash';

    public string $period = '';

    public string $payment_notes = '';

    // Add student modal
    public bool $showAddStudentModal = false;

    public string $student_id = '';

    public string $studentSearch = '';

    // Attendance
    public int $lesson_number = 1;

    public string $lesson_date = '';

    public array $attendance = [];

    public function mount(Group $group): void
    {
        $this->group = $group->load(['course', 'teacher', 'room']);
        $this->lesson_date = now()->format('Y-m-d');
        $this->period = now()->format('Y-m');

        // Avtomatik keyingi davomati yo'q bo'lgan darsni tanlash
        $this->selectNextUnmarkedLesson();
        $this->loadAttendance();
    }

    protected function selectNextUnmarkedLesson(): void
    {
        $markedLessons = AttendanceModel::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group->id))
            ->distinct('lesson_number')
            ->pluck('lesson_number')
            ->toArray();

        $maxLesson = $this->group->total_lessons ?? 24;

        for ($i = 1; $i <= $maxLesson; $i++) {
            if (! in_array($i, $markedLessons)) {
                $this->lesson_number = $i;

                return;
            }
        }

        // Agar hammasi qilingan bo'lsa, oxirgisini ko'rsatish
        $this->lesson_number = count($markedLessons) > 0 ? max($markedLessons) : 1;
    }

    public function getTitle(): string
    {
        return $this->group->name.' - '.$this->group->course->code;
    }

    #[Computed]
    public function enrollments()
    {
        return Enrollment::with(['student.activeDiscounts', 'payments', 'attendances'])
            ->where('group_id', $this->group->id)
            ->where('status', 'active')
            ->get();
    }

    #[Computed]
    public function allEnrollments()
    {
        return Enrollment::with(['student', 'payments'])
            ->where('group_id', $this->group->id)
            ->get();
    }

    #[Computed]
    public function paymentEnrollment()
    {
        return $this->paymentEnrollmentId
            ? Enrollment::with(['student.activeDiscounts', 'group.course'])->find($this->paymentEnrollmentId)
            : null;
    }

    #[Computed]
    public function lessonDates()
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

    #[Computed]
    public function existingAttendance()
    {
        return AttendanceModel::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group->id))
            ->where('lesson_number', $this->lesson_number)
            ->get()
            ->keyBy('enrollment_id');
    }

    #[Computed]
    public function availableStudents()
    {
        $enrolledStudentIds = $this->group->enrollments()
            ->whereIn('status', ['active', 'paused'])
            ->pluck('student_id');

        $query = Student::whereNotIn('id', $enrolledStudentIds);

        if ($this->studentSearch) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->studentSearch}%")
                    ->orWhere('phone', 'like', "%{$this->studentSearch}%");
            });
        }

        return $query->limit(20)->get();
    }

    public function getPaymentStatusForPeriod(Enrollment $enrollment, ?string $period = null): array
    {
        $period = $period ?? $this->period;
        $coursePrice = $this->group->course->monthly_price;
        $payments = $enrollment->payments->where('period', $period);
        $totalPaid = $payments->sum('amount');
        $discount = $enrollment->student->calculateTotalDiscount($coursePrice);
        $required = $coursePrice - $discount;

        return [
            'course_price' => $coursePrice,
            'discount' => $discount,
            'required' => $required,
            'paid' => $totalPaid,
            'remaining' => max(0, $required - $totalPaid),
            'status' => $totalPaid >= $required ? 'paid' : ($totalPaid > 0 ? 'partial' : 'unpaid'),
        ];
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

    public function getTotalStats(): array
    {
        $totalRequired = 0;
        $totalPaid = 0;
        $totalDiscount = 0;

        foreach ($this->enrollments as $enrollment) {
            $status = $this->getPaymentStatusForPeriod($enrollment);
            $totalRequired += $status['course_price'];
            $totalPaid += $status['paid'];
            $totalDiscount += $status['discount'];
        }

        return [
            'required' => $totalRequired,
            'paid' => $totalPaid,
            'discount' => $totalDiscount,
            'remaining' => $totalRequired - $totalDiscount - $totalPaid,
        ];
    }

    // Payment methods
    public function openPaymentModal(Enrollment $enrollment): void
    {
        $this->paymentEnrollmentId = $enrollment->id;
        $status = $this->getPaymentStatusForPeriod($enrollment);
        $this->amount = $status['remaining'] > 0 ? (string) $status['remaining'] : (string) $status['required'];
        $this->method = 'cash';
        $this->payment_notes = '';
        $this->showPaymentModal = true;
    }

    public function collectPayment(): void
    {
        $this->validate([
            'amount' => 'required|numeric|min:1000',
            'method' => 'required|in:cash,card,transfer',
            'period' => 'required|date_format:Y-m',
        ]);

        Payment::create([
            'enrollment_id' => $this->paymentEnrollmentId,
            'amount' => $this->amount,
            'paid_at' => now(),
            'period' => $this->period,
            'method' => $this->method,
            'notes' => $this->payment_notes ?: null,
        ]);

        $this->showPaymentModal = false;
        $this->reset(['paymentEnrollmentId', 'amount', 'payment_notes']);
        $this->dispatch('payment-collected');
    }

    // Attendance methods
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

        // Oxirgi dars bo'lsa, guruhni tugatish
        if ($this->isLastLesson()) {
            $this->completeGroup();

            return;
        }

        // Avtomatik keyingi darsga o'tish
        $this->goToNextLesson();
    }

    protected function isLastLesson(): bool
    {
        $markedLessons = AttendanceModel::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group->id))
            ->distinct('lesson_number')
            ->count('lesson_number');

        return $markedLessons >= $this->group->total_lessons;
    }

    public function completeGroup(): void
    {
        // Har bir enrollment uchun OutstandingDebt yaratish (agar qarz bo'lsa)
        foreach ($this->group->enrollments()->where('status', 'active')->get() as $enrollment) {
            // OutstandingDebt yaratish (ichida hisob-kitob qilinadi)
            $debt = OutstandingDebt::createFromEnrollment($enrollment, 'completed');

            $enrollment->update([
                'status' => 'completed',
                'final_balance' => $debt?->original_amount ?? 0,
            ]);
        }

        // Guruhni tugatish
        $this->group->update([
            'status' => 'completed',
            'end_date' => now(),
        ]);

        $this->dispatch('group-completed');
    }

    public function goToNextLesson(): void
    {
        // Keyingi davomati yo'q bo'lgan darsni topish
        $markedLessons = AttendanceModel::whereHas('enrollment', fn ($q) => $q->where('group_id', $this->group->id))
            ->distinct('lesson_number')
            ->pluck('lesson_number')
            ->toArray();

        $maxLesson = $this->group->total_lessons ?? 24;

        for ($i = 1; $i <= $maxLesson; $i++) {
            if (! in_array($i, $markedLessons)) {
                $this->lesson_number = $i;
                $this->lesson_date = now()->format('Y-m-d');
                $this->loadAttendance();

                return;
            }
        }

        // Agar hammasi qilingan bo'lsa, oxirgisini ko'rsatish
        $this->lesson_number = min($this->lesson_number + 1, $maxLesson);
        $this->lesson_date = now()->format('Y-m-d');
        $this->loadAttendance();
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

    // Student management methods
    public function openAddStudentModal(): void
    {
        $this->student_id = '';
        $this->studentSearch = '';
        $this->showAddStudentModal = true;
    }

    public function updatedStudentSearch(): void
    {
        unset($this->availableStudents);
    }

    public function addStudent(): void
    {
        $this->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $exists = Enrollment::where('student_id', $this->student_id)
            ->where('group_id', $this->group->id)
            ->whereIn('status', ['active', 'paused'])
            ->exists();

        if ($exists) {
            $this->addError('student_id', "Bu o'quvchi allaqachon ushbu guruhda");

            return;
        }

        Enrollment::create([
            'student_id' => $this->student_id,
            'group_id' => $this->group->id,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        $this->showAddStudentModal = false;
        $this->reset(['student_id', 'studentSearch']);
        $this->dispatch('student-added');
    }

    public function addStudentDirect(int $studentId): void
    {
        $exists = Enrollment::where('student_id', $studentId)
            ->where('group_id', $this->group->id)
            ->whereIn('status', ['active', 'paused'])
            ->exists();

        if ($exists) {
            return;
        }

        Enrollment::create([
            'student_id' => $studentId,
            'group_id' => $this->group->id,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        $this->dispatch('student-added');
    }

    public function unenrollStudent(Enrollment $enrollment): void
    {
        // OutstandingDebt yaratish (darslar bo'yicha hisob-kitob)
        $debt = OutstandingDebt::createFromEnrollment($enrollment, 'dropped');

        $enrollment->update([
            'status' => 'dropped',
            'dropped_at' => now(),
            'final_balance' => $debt?->original_amount ?? 0,
        ]);

        $this->dispatch('student-removed');
    }

    public function render()
    {
        return view('livewire.admin.group-detail')
            ->title($this->getTitle());
    }
}
