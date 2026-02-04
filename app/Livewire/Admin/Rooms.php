<?php

namespace App\Livewire\Admin;

use App\Models\Room;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Xonalar')]
class Rooms extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $capacity = '10';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean',
        ];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'capacity', 'is_active']);
        $this->capacity = '10';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Room $room): void
    {
        $this->editingId = $room->id;
        $this->name = $room->name;
        $this->capacity = (string) $room->capacity;
        $this->is_active = $room->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Room::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'capacity' => $this->capacity,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'capacity', 'is_active']);
    }

    public function delete(Room $room): void
    {
        $room->delete();
    }

    public function render()
    {
        return view('livewire.admin.rooms', [
            'rooms' => Room::withCount('groups')->latest()->get(),
        ]);
    }
}
