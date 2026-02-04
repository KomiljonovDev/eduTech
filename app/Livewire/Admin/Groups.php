<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\Group;
use App\Models\Room;
use App\Models\Teacher;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Guruhlar')]
class Groups extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $course_id = '';

    public string $teacher_id = '';

    public string $room_id = '';

    public string $days = 'odd';

    public string $start_time = '09:00';

    public string $end_time = '11:00';

    public string $total_lessons = '12';

    public string $start_date = '';

    public string $status = 'pending';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'teacher_id' => 'required|exists:teachers,id',
            'room_id' => 'required|exists:rooms,id',
            'days' => 'required|in:odd,even',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'total_lessons' => 'required|integer|min:1|max:100',
            'start_date' => 'required|date',
            'status' => 'required|in:pending,active,completed,cancelled',
        ];
    }

    #[Computed]
    public function courses()
    {
        return Course::where('is_active', true)->get();
    }

    #[Computed]
    public function teachers()
    {
        return Teacher::where('is_active', true)->get();
    }

    #[Computed]
    public function rooms()
    {
        return Room::where('is_active', true)->get();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'course_id', 'teacher_id', 'room_id', 'days', 'start_time', 'end_time', 'total_lessons', 'start_date', 'status']);
        $this->days = 'odd';
        $this->start_time = '09:00';
        $this->end_time = '11:00';
        $this->total_lessons = '12';
        $this->start_date = now()->format('Y-m-d');
        $this->status = 'pending';
        $this->showModal = true;
    }

    public function edit(Group $group): void
    {
        $this->editingId = $group->id;
        $this->name = $group->name;
        $this->course_id = (string) $group->course_id;
        $this->teacher_id = (string) $group->teacher_id;
        $this->room_id = (string) $group->room_id;
        $this->days = $group->days;
        $this->start_time = $group->start_time->format('H:i');
        $this->end_time = $group->end_time->format('H:i');
        $this->total_lessons = (string) $group->total_lessons;
        $this->start_date = $group->start_date->format('Y-m-d');
        $this->status = $group->status;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Group::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'course_id' => $this->course_id,
                'teacher_id' => $this->teacher_id,
                'room_id' => $this->room_id,
                'days' => $this->days,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'total_lessons' => $this->total_lessons,
                'start_date' => $this->start_date,
                'status' => $this->status,
            ]
        );

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'course_id', 'teacher_id', 'room_id', 'days', 'start_time', 'end_time', 'total_lessons', 'start_date', 'status']);
    }

    public function delete(Group $group): void
    {
        $group->delete();
    }

    public function render()
    {
        return view('livewire.admin.groups', [
            'groups' => Group::with(['course', 'teacher', 'room'])
                ->withCount('enrollments')
                ->latest()
                ->get(),
        ]);
    }
}
