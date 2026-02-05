<?php

namespace App\Livewire\Teacher;

use App\Models\Teacher;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dars jadvali')]
class TeacherSchedule extends Component
{
    public array $timeSlots = [
        '08:00', '09:00', '10:00', '11:00', '12:00',
        '13:00', '14:00', '15:00', '16:00', '17:00',
        '18:00', '19:00', '20:00',
    ];

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
            ->with(['course', 'room'])
            ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')])
            ->whereIn('status', ['active', 'pending'])
            ->orderBy('start_time')
            ->get();
    }

    #[Computed]
    public function oddDayGroups()
    {
        return $this->groups->where('days', 'odd');
    }

    #[Computed]
    public function evenDayGroups()
    {
        return $this->groups->where('days', 'even');
    }

    public function getGroupsForSlot(string $time, string $days)
    {
        return $this->groups->filter(function ($group) use ($time, $days) {
            if ($group->days !== $days) {
                return false;
            }

            $startTime = $group->start_time;

            if (! $startTime) {
                return false;
            }

            // Faqat boshlanish soatida ko'rsatish
            return $startTime->format('H:00') === $time || $startTime->format('H:i') === $time;
        });
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'active' => 'bg-green-100 border-green-300 dark:bg-green-900/30 dark:border-green-700',
            'pending' => 'bg-yellow-100 border-yellow-300 dark:bg-yellow-900/30 dark:border-yellow-700',
            default => 'bg-zinc-100 border-zinc-300 dark:bg-zinc-800 dark:border-zinc-600',
        };
    }

    public function render()
    {
        return view('livewire.teacher.schedule');
    }
}
