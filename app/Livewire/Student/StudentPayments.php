<?php

namespace App\Livewire\Student;

use App\Models\Payment;
use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title("To'lovlar tarixi")]
class StudentPayments extends Component
{
    #[Computed]
    public function student(): ?Student
    {
        return auth()->user()->student;
    }

    #[Computed]
    public function payments()
    {
        $student = $this->student;

        if (! $student) {
            return collect();
        }

        return Payment::whereHas('enrollment', fn ($q) => $q->where('student_id', $student->id))
            ->with('enrollment.group.course')
            ->latest('paid_at')
            ->get();
    }

    #[Computed]
    public function totalPaid(): float
    {
        return $this->payments->sum('amount');
    }

    #[Computed]
    public function paymentsByMonth()
    {
        return $this->payments->groupBy(fn ($p) => $p->paid_at->format('Y-m'));
    }

    public function render()
    {
        return view('livewire.student.payments');
    }
}
