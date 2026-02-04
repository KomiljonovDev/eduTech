<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title("Yo'nalishlar")]
class Courses extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $code = '';

    public string $description = '';

    public string $monthly_price = '0';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'code', 'description', 'monthly_price', 'is_active']);
        $this->monthly_price = '0';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Course $course): void
    {
        $this->editingId = $course->id;
        $this->name = $course->name;
        $this->code = $course->code;
        $this->description = $course->description ?? '';
        $this->monthly_price = (string) $course->monthly_price;
        $this->is_active = $course->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Course::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'code' => strtoupper($this->code),
                'description' => $this->description ?: null,
                'monthly_price' => $this->monthly_price,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'code', 'description', 'monthly_price', 'is_active']);
    }

    public function delete(Course $course): void
    {
        $course->delete();
    }

    public function render()
    {
        return view('livewire.admin.courses', [
            'courses' => Course::withCount('groups')->latest()->get(),
        ]);
    }
}
