<?php

namespace App\Livewire\Admin;

use App\Models\Expense;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
#[Title('Harajatlar')]
class Expenses extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $description = '';

    public string $amount = '';

    public string $category = 'other';

    public string $expense_date = '';

    public string $period = '';

    #[Url]
    public string $filterPeriod = '';

    #[Url]
    public string $filterCategory = '';

    public function mount(): void
    {
        $this->expense_date = now()->format('Y-m-d');
        $this->period = now()->format('Y-m');
        if (! $this->filterPeriod) {
            $this->filterPeriod = now()->format('Y-m');
        }
    }

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string',
            'expense_date' => 'required|date',
            'period' => 'required|date_format:Y-m',
        ];
    }

    #[Computed]
    public function categories()
    {
        return Expense::categories();
    }

    #[Computed]
    public function periodStats()
    {
        $query = Expense::query();

        if ($this->filterPeriod) {
            $query->where('period', $this->filterPeriod);
        }

        $expenses = $query->get();

        $byCategory = $expenses->groupBy('category')->map(fn ($items) => $items->sum('amount'));

        return [
            'total' => $expenses->sum('amount'),
            'count' => $expenses->count(),
            'by_category' => $byCategory,
        ];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'title', 'description', 'amount', 'category']);
        $this->expense_date = now()->format('Y-m-d');
        $this->period = $this->filterPeriod ?: now()->format('Y-m');
        $this->category = 'other';
        $this->showModal = true;
    }

    public function edit(Expense $expense): void
    {
        $this->editingId = $expense->id;
        $this->title = $expense->title;
        $this->description = $expense->description ?? '';
        $this->amount = (string) $expense->amount;
        $this->category = $expense->category;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->period = $expense->period ?? now()->format('Y-m');
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        Expense::updateOrCreate(
            ['id' => $this->editingId],
            [
                'title' => $this->title,
                'description' => $this->description ?: null,
                'amount' => $this->amount,
                'category' => $this->category,
                'expense_date' => $this->expense_date,
                'period' => $this->period,
                'user_id' => auth()->id(),
            ]
        );

        $this->showModal = false;
        $this->reset(['editingId', 'title', 'description', 'amount', 'category']);
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    public function render()
    {
        $query = Expense::query()->latest('expense_date');

        if ($this->filterPeriod) {
            $query->where('period', $this->filterPeriod);
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        return view('livewire.admin.expenses', [
            'expenses' => $query->paginate(20),
        ]);
    }
}
