<?php

namespace App\Livewire;

use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Lead;
use App\Models\OutstandingDebt;
use App\Models\Payment;
use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public bool $showNetIncome = false;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->hasRole('teacher')) {
            $this->redirect(route('teacher.dashboard'), navigate: true);
        }

        if ($user->hasRole('student')) {
            $this->redirect(route('student.dashboard'), navigate: true);
        }
    }

    #[Computed]
    public function stats()
    {
        $currentMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');

        // Students
        $totalStudents = Student::count();
        $activeStudents = Student::whereHas('enrollments', fn ($q) => $q->where('status', 'active'))->count();
        $waitingStudents = $totalStudents - $activeStudents;

        // Groups
        $activeGroups = Group::where('status', 'active')->count();
        $pendingGroups = Group::where('status', 'pending')->count();

        // Leads
        $newLeads = Lead::where('status', 'new')->count();
        $totalLeads = Lead::whereIn('status', ['new', 'contacted', 'interested'])->count();

        // Revenue
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $currentMonth = now()->format('Y-m');
        $lastMonthPeriod = now()->subMonth()->format('Y-m');

        // Gross revenue
        $currentMonthRevenue = Payment::whereBetween('paid_at', [$currentMonthStart, $currentMonthEnd])->sum('amount');
        $lastMonthRevenue = Payment::whereBetween('paid_at', [$lastMonthStart, $lastMonthEnd])->sum('amount');

        // Teacher shares
        $currentTeacherShare = Payment::whereBetween('paid_at', [$currentMonthStart, $currentMonthEnd])->sum('teacher_share');
        $lastTeacherShare = Payment::whereBetween('paid_at', [$lastMonthStart, $lastMonthEnd])->sum('teacher_share');

        // Expenses
        $currentExpenses = Expense::where('period', $currentMonth)->sum('amount');
        $lastExpenses = Expense::where('period', $lastMonthPeriod)->sum('amount');

        // Net income (gross - teacher share - expenses)
        $currentNetIncome = $currentMonthRevenue - $currentTeacherShare - $currentExpenses;
        $lastNetIncome = $lastMonthRevenue - $lastTeacherShare - $lastExpenses;

        $revenueChange = $lastMonthRevenue > 0
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : 0;

        $netIncomeChange = $lastNetIncome > 0
            ? round((($currentNetIncome - $lastNetIncome) / $lastNetIncome) * 100)
            : 0;

        // Outstanding
        $outstandingCount = 0;
        $outstandingAmount = 0;
        $enrollments = Enrollment::with(['student.activeDiscounts', 'group.course', 'payments'])
            ->where('status', 'active')
            ->get();

        foreach ($enrollments as $enrollment) {
            $coursePrice = $enrollment->group->course->monthly_price;
            $discount = $enrollment->student->calculateTotalDiscount($coursePrice);
            $required = $coursePrice - $discount;
            $paid = $enrollment->payments->where('period', $currentMonth)->sum('amount');
            if ($paid < $required) {
                $outstandingCount++;
                $outstandingAmount += ($required - $paid);
            }
        }

        // O'tkazib yuborilgan qarzlar (completed/dropped)
        $overdueDebts = OutstandingDebt::outstanding();
        $overdueCount = $overdueDebts->count();
        $overdueAmount = $overdueDebts->sum('remaining_amount');

        return [
            'total_students' => $totalStudents,
            'active_students' => $activeStudents,
            'waiting_students' => $waitingStudents,
            'active_groups' => $activeGroups,
            'pending_groups' => $pendingGroups,
            'new_leads' => $newLeads,
            'total_leads' => $totalLeads,
            'current_revenue' => $currentMonthRevenue,
            'last_revenue' => $lastMonthRevenue,
            'revenue_change' => $revenueChange,
            'current_teacher_share' => $currentTeacherShare,
            'current_expenses' => $currentExpenses,
            'current_net_income' => $currentNetIncome,
            'last_net_income' => $lastNetIncome,
            'net_income_change' => $netIncomeChange,
            'outstanding_count' => $outstandingCount,
            'outstanding_amount' => $outstandingAmount,
            'overdue_count' => $overdueCount,
            'overdue_amount' => $overdueAmount,
        ];
    }

    #[Computed]
    public function recentPayments()
    {
        return Payment::with(['enrollment.student', 'enrollment.group.course'])
            ->latest('paid_at')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function recentLeads()
    {
        return Lead::with('course')
            ->whereIn('status', ['new', 'contacted', 'interested'])
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function todayGroups()
    {
        $today = now()->dayOfWeek; // 0=Sunday, 1=Monday, etc.
        $isOddDay = in_array($today, [1, 3, 5]); // Mon, Wed, Fri

        return Group::with(['course', 'teacher', 'room'])
            ->where('status', 'active')
            ->where('days', $isOddDay ? 'odd' : 'even')
            ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')])
            ->get();
    }

    #[Computed]
    public function weeklyRevenue()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $amount = Payment::whereDate('paid_at', $date)->sum('amount');
            $data[] = [
                'day' => $date->format('D'),
                'date' => $date->format('d.m'),
                'amount' => $amount,
            ];
        }

        return $data;
    }

    #[Computed]
    public function attendanceToday()
    {
        $today = now()->format('Y-m-d');

        return [
            'marked' => \App\Models\Attendance::whereDate('date', $today)->distinct('enrollment_id')->count('enrollment_id'),
            'total' => Enrollment::where('status', 'active')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
