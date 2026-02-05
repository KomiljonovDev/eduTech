<?php

namespace App\Livewire\Student;

use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dars jadvali')]
class StudentSchedule extends Component
{
    #[Computed]
    public function student(): ?Student
    {
        return auth()->user()->student;
    }

    #[Computed]
    public function oddDayGroups()
    {
        $student = $this->student;

        if (! $student) {
            return collect();
        }

        return $student->enrollments()
            ->where('status', 'active')
            ->whereHas('group', fn ($q) => $q->where('days', 'odd')->whereIn('status', ['active', 'pending']))
            ->with(['group.course', 'group.teacher', 'group.room'])
            ->get()
            ->sortBy('group.start_time');
    }

    #[Computed]
    public function evenDayGroups()
    {
        $student = $this->student;

        if (! $student) {
            return collect();
        }

        return $student->enrollments()
            ->where('status', 'active')
            ->whereHas('group', fn ($q) => $q->where('days', 'even')->whereIn('status', ['active', 'pending']))
            ->with(['group.course', 'group.teacher', 'group.room'])
            ->get()
            ->sortBy('group.start_time');
    }

    public function render()
    {
        return view('livewire.student.schedule');
    }
}
