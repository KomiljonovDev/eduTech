<?php

namespace App\Livewire\Admin;

use App\Models\Group;
use App\Models\Room;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Dars jadvali')]
class Schedule extends Component
{
    #[Url]
    public string $days = '';

    #[Url]
    public string $room_id = '';

    public array $timeSlots = [
        '08:00', '09:00', '10:00', '11:00', '12:00',
        '13:00', '14:00', '15:00', '16:00', '17:00',
        '18:00', '19:00', '20:00',
    ];

    #[Computed]
    public function rooms()
    {
        return Room::orderBy('name')->get();
    }

    #[Computed]
    public function groups()
    {
        return Group::query()
            ->with(['course', 'teacher', 'room'])
            ->withCount(['enrollments' => fn ($q) => $q->where('status', 'active')])
            ->whereIn('status', ['active', 'pending'])
            ->when($this->days, fn ($q) => $q->where('days', $this->days))
            ->when($this->room_id, fn ($q) => $q->where('room_id', $this->room_id))
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

    #[Computed]
    public function groupedByRoom()
    {
        return $this->groups->groupBy('room_id');
    }

    public function getGroupsForSlot(string $time, string $days): \Illuminate\Support\Collection
    {
        $targetTime = \Carbon\Carbon::createFromFormat('H:i', $time);

        return $this->groups->filter(function ($group) use ($targetTime, $days) {
            if ($group->days !== $days) {
                return false;
            }

            $startTime = $group->start_time;
            $endTime = $group->end_time;

            if (! $startTime || ! $endTime) {
                return false;
            }

            return $targetTime->gte($startTime->copy()->startOfHour())
                && $targetTime->lt($endTime);
        });
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'active' => 'bg-green-100 border-green-300 dark:bg-green-900/30 dark:border-green-700',
            'pending' => 'bg-yellow-100 border-yellow-300 dark:bg-yellow-900/30 dark:border-yellow-700',
            'completed' => 'bg-zinc-100 border-zinc-300 dark:bg-zinc-800 dark:border-zinc-600',
            default => 'bg-zinc-100 border-zinc-300 dark:bg-zinc-800 dark:border-zinc-600',
        };
    }

    public function render()
    {
        return view('livewire.admin.schedule');
    }
}
