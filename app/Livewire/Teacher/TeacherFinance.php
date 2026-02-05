<?php

namespace App\Livewire\Teacher;

use App\Models\Payment;
use App\Models\Teacher;
use App\Models\TeacherPayment;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Hisobim')]
class TeacherFinance extends Component
{
    public string $period = '';

    public function mount(): void
    {
        $this->period = now()->format('Y-m');
    }

    #[Computed]
    public function teacher(): ?Teacher
    {
        return auth()->user()->teacher;
    }

    #[Computed]
    public function salaryStats(): array
    {
        $teacher = $this->teacher;

        if (! $teacher) {
            return [];
        }

        $earnings = $teacher->calculateMonthlyEarnings($this->period);
        $paid = $teacher->getPaidAmount($this->period);
        $debt = $teacher->getDebt($this->period);

        return [
            'earnings' => $earnings,
            'paid' => $paid,
            'debt' => $debt,
            'salary_type' => $teacher->salary_type,
            'fixed_salary' => $teacher->fixed_salary,
            'payment_percentage' => $teacher->payment_percentage,
        ];
    }

    #[Computed]
    public function studentPayments()
    {
        $teacher = $this->teacher;

        if (! $teacher) {
            return collect();
        }

        return Payment::query()
            ->whereHas('enrollment.group', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->where('period', $this->period)
            ->with('enrollment.student', 'enrollment.group')
            ->latest('paid_at')
            ->get();
    }

    #[Computed]
    public function paymentHistory()
    {
        $teacher = $this->teacher;

        if (! $teacher) {
            return collect();
        }

        return $teacher->teacherPayments()
            ->where('period', $this->period)
            ->with('user')
            ->latest('paid_at')
            ->get();
    }

    #[Computed]
    public function monthlyHistory()
    {
        $teacher = $this->teacher;

        if (! $teacher) {
            return collect();
        }

        // Oxirgi 6 oy
        $months = [];
        for ($i = 0; $i < 6; $i++) {
            $period = now()->subMonths($i)->format('Y-m');
            $earnings = $teacher->calculateMonthlyEarnings($period);
            $paid = $teacher->getPaidAmount($period);

            $months[] = [
                'period' => $period,
                'label' => now()->subMonths($i)->translatedFormat('F Y'),
                'earnings' => $earnings,
                'paid' => $paid,
                'debt' => $earnings - $paid,
            ];
        }

        return collect($months);
    }

    public function render()
    {
        return view('livewire.teacher.finance', [
            'salaryTypes' => Teacher::salaryTypes(),
            'paymentMethods' => TeacherPayment::methods(),
        ]);
    }
}
