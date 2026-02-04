<?php

namespace App\Livewire\Admin;

use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Hisobotlar')]
class Reports extends Component
{
    #[Url]
    public string $report = 'financial';

    #[Url]
    public string $period = '';

    #[Url]
    public string $group_id = '';

    public function mount(): void
    {
        if (! $this->period) {
            $this->period = now()->format('Y-m');
        }
    }

    #[Computed]
    public function groups()
    {
        return Group::with('course')
            ->whereIn('status', ['active', 'pending', 'completed'])
            ->get();
    }

    #[Computed]
    public function financialReport()
    {
        $startDate = Carbon::parse($this->period.'-01')->startOfMonth();
        $endDate = Carbon::parse($this->period.'-01')->endOfMonth();

        $payments = Payment::with(['enrollment.student', 'enrollment.group.course', 'enrollment.group.teacher'])
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->get();

        $byMethod = $payments->groupBy('method')->map(fn ($items) => $items->sum('amount'));
        $byGroup = $payments->groupBy('enrollment.group_id')->map(function ($items) {
            $group = $items->first()->enrollment->group;

            return [
                'name' => $group->name,
                'course' => $group->course->code,
                'amount' => $items->sum('amount'),
                'teacher_share' => $items->sum('teacher_share'),
                'school_share' => $items->sum('school_share'),
                'count' => $items->count(),
            ];
        });

        $byTeacher = $payments->groupBy('enrollment.group.teacher_id')->map(function ($items) {
            $teacher = $items->first()->enrollment->group->teacher;

            return [
                'name' => $teacher->name,
                'total' => $items->sum('amount'),
                'share' => $items->sum('teacher_share'),
                'percentage' => $teacher->payment_percentage,
            ];
        });

        return [
            'total' => $payments->sum('amount'),
            'teacher_total' => $payments->sum('teacher_share'),
            'school_total' => $payments->sum('school_share'),
            'count' => $payments->count(),
            'by_method' => $byMethod,
            'by_group' => $byGroup->sortByDesc('amount'),
            'by_teacher' => $byTeacher->sortByDesc('share'),
            'payments' => $payments->sortByDesc('paid_at'),
        ];
    }

    #[Computed]
    public function outstandingPayments()
    {
        $period = $this->period;

        return Enrollment::with(['student.activeDiscounts', 'group.course', 'payments'])
            ->where('status', 'active')
            ->get()
            ->map(function ($enrollment) use ($period) {
                $coursePrice = $enrollment->group->course->monthly_price;
                $discount = $enrollment->student->calculateTotalDiscount($coursePrice);
                $required = $coursePrice - $discount;
                $paid = $enrollment->payments->where('period', $period)->sum('amount');
                $remaining = max(0, $required - $paid);

                return [
                    'enrollment' => $enrollment,
                    'student' => $enrollment->student,
                    'group' => $enrollment->group,
                    'required' => $required,
                    'paid' => $paid,
                    'remaining' => $remaining,
                    'status' => $paid >= $required ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                ];
            })
            ->filter(fn ($item) => $item['remaining'] > 0)
            ->sortByDesc('remaining');
    }

    #[Computed]
    public function attendanceReport()
    {
        $query = Enrollment::with(['student', 'group.course', 'attendances'])
            ->where('status', 'active');

        if ($this->group_id) {
            $query->where('group_id', $this->group_id);
        }

        return $query->get()->map(function ($enrollment) {
            $total = $enrollment->attendances->count();
            $present = $enrollment->attendances->where('present', true)->count();

            return [
                'enrollment' => $enrollment,
                'student' => $enrollment->student,
                'group' => $enrollment->group,
                'total' => $total,
                'present' => $present,
                'absent' => $total - $present,
                'percentage' => $total > 0 ? round(($present / $total) * 100) : 0,
            ];
        })->sortBy('percentage');
    }

    #[Computed]
    public function studentStats()
    {
        $totalStudents = Student::count();
        $activeStudents = Student::whereHas('enrollments', fn ($q) => $q->where('status', 'active'))->count();
        $waitingStudents = Student::whereDoesntHave('enrollments', fn ($q) => $q->where('status', 'active'))->count();

        $bySource = Student::select('source', DB::raw('count(*) as count'))
            ->groupBy('source')
            ->pluck('count', 'source');

        $completedKS = Student::whereHas('enrollments', function ($q) {
            $q->where('status', 'completed')
                ->whereHas('group.course', fn ($cq) => $cq->where('code', 'KS'));
        })->count();

        $enrollmentsByMonth = Enrollment::where('enrolled_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn ($e) => $e->enrolled_at->format('Y-m'))
            ->map(fn ($items) => $items->count())
            ->sortKeys();

        return [
            'total' => $totalStudents,
            'active' => $activeStudents,
            'waiting' => $waitingStudents,
            'completed_ks' => $completedKS,
            'by_source' => $bySource,
            'enrollments_by_month' => $enrollmentsByMonth,
        ];
    }

    #[Computed]
    public function droppedWithDebt()
    {
        return Enrollment::with(['student', 'group.course'])
            ->where('status', 'dropped')
            ->where('final_balance', '>', 0)
            ->orderByDesc('dropped_at')
            ->get();
    }

    #[Computed]
    public function groupStats()
    {
        return Group::with(['course', 'teacher', 'room'])
            ->withCount([
                'enrollments',
                'enrollments as active_count' => fn ($q) => $q->where('status', 'active'),
            ])
            ->whereIn('status', ['active', 'pending'])
            ->get()
            ->map(function ($group) {
                $totalLessons = $group->total_lessons ?? 0;
                $completedLessons = Attendance::whereHas('enrollment', fn ($q) => $q->where('group_id', $group->id))
                    ->distinct('lesson_number')
                    ->count('lesson_number');

                return [
                    'group' => $group,
                    'total_students' => $group->enrollments_count,
                    'active_students' => $group->active_count,
                    'capacity' => $group->room->capacity,
                    'fill_rate' => $group->room->capacity > 0
                        ? round(($group->active_count / $group->room->capacity) * 100)
                        : 0,
                    'total_lessons' => $totalLessons,
                    'completed_lessons' => $completedLessons,
                    'progress' => $totalLessons > 0
                        ? round(($completedLessons / $totalLessons) * 100)
                        : 0,
                ];
            });
    }

    public function render()
    {
        return view('livewire.admin.reports');
    }
}
