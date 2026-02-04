<?php

namespace App\Livewire\Admin;

use App\Models\Teacher;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Ustozlar')]
class Teachers extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $phone = '';

    public string $payment_percentage = '50';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'payment_percentage' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'payment_percentage', 'is_active']);
        $this->payment_percentage = '50';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Teacher $teacher): void
    {
        $this->editingId = $teacher->id;
        $this->name = $teacher->name;
        $this->phone = $teacher->phone ?? '';
        $this->payment_percentage = (string) $teacher->payment_percentage;
        $this->is_active = $teacher->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Teacher::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'phone' => $this->phone ?: null,
                'payment_percentage' => $this->payment_percentage,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'phone', 'payment_percentage', 'is_active']);
    }

    public function delete(Teacher $teacher): void
    {
        $teacher->delete();
    }

    public function render()
    {
        return view('livewire.admin.teachers', [
            'teachers' => Teacher::latest()->get(),
        ]);
    }
}
