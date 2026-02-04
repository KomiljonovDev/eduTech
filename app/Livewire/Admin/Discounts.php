<?php

namespace App\Livewire\Admin;

use App\Models\Discount;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Chegirmalar')]
class Discounts extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'percentage';

    public string $value = '';

    public string $description = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'type', 'value', 'description', 'is_active']);
        $this->type = 'percentage';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Discount $discount): void
    {
        $this->editingId = $discount->id;
        $this->name = $discount->name;
        $this->type = $discount->type;
        $this->value = (string) $discount->value;
        $this->description = $discount->description ?? '';
        $this->is_active = $discount->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Discount::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'type' => $this->type,
                'value' => $this->value,
                'description' => $this->description ?: null,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'type', 'value', 'description', 'is_active']);
    }

    public function delete(Discount $discount): void
    {
        $discount->delete();
    }

    public function render()
    {
        return view('livewire.admin.discounts', [
            'discounts' => Discount::withCount('students')->latest()->get(),
        ]);
    }
}
