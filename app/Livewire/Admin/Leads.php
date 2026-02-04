<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\Lead;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
#[Title('Leadlar')]
class Leads extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $phone = '';

    public string $home_phone = '';

    public string $course_id = '';

    public string $source = 'instagram';

    public string $status = 'new';

    public string $preferred_time = '';

    public string $notes = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterStatus = 'all';

    #[Url]
    public string $filterCourse = 'all';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'home_phone' => 'nullable|string|max:20',
            'course_id' => 'nullable|exists:courses,id',
            'source' => 'required|in:instagram,telegram,google_form,referral,walk_in,other',
            'status' => 'required|in:new,contacted,interested,enrolled,not_interested,no_answer',
            'preferred_time' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ];
    }

    #[Computed]
    public function courses()
    {
        return Course::where('is_active', true)->get();
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'home_phone', 'course_id', 'source', 'status', 'preferred_time', 'notes']);
        $this->source = 'instagram';
        $this->status = 'new';
        $this->showModal = true;
    }

    public function edit(Lead $lead): void
    {
        $this->editingId = $lead->id;
        $this->name = $lead->name;
        $this->phone = $lead->phone;
        $this->home_phone = $lead->home_phone ?? '';
        $this->course_id = (string) ($lead->course_id ?? '');
        $this->source = $lead->source;
        $this->status = $lead->status;
        $this->preferred_time = $lead->preferred_time ?? '';
        $this->notes = $lead->notes ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
            'home_phone' => $this->home_phone ?: null,
            'course_id' => $this->course_id ?: null,
            'source' => $this->source,
            'status' => $this->status,
            'preferred_time' => $this->preferred_time ?: null,
            'notes' => $this->notes ?: null,
        ];

        if (! $this->editingId && $this->status !== 'new') {
            $data['contacted_at'] = now();
        }

        Lead::updateOrCreate(['id' => $this->editingId], $data);

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'phone', 'home_phone', 'course_id', 'source', 'status', 'preferred_time', 'notes']);
    }

    public function markContacted(Lead $lead): void
    {
        $lead->update([
            'status' => 'contacted',
            'contacted_at' => now(),
        ]);
    }

    public function convertToStudent(Lead $lead): void
    {
        $student = $lead->convertToStudent();

        $this->redirect(route('admin.students', ['search' => $student->phone]), navigate: true);
    }

    public function delete(Lead $lead): void
    {
        $lead->delete();
    }

    public function render()
    {
        $query = Lead::query()->with('course');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterCourse !== 'all') {
            $query->where('course_id', $this->filterCourse);
        }

        return view('livewire.admin.leads', [
            'leads' => $query->latest()->paginate(20),
            'statuses' => [
                'new' => ['label' => 'Yangi', 'color' => 'blue'],
                'contacted' => ['label' => "Bog'lanildi", 'color' => 'yellow'],
                'interested' => ['label' => 'Qiziqdi', 'color' => 'green'],
                'enrolled' => ['label' => 'Yozildi', 'color' => 'emerald'],
                'not_interested' => ['label' => 'Qiziqmadi', 'color' => 'red'],
                'no_answer' => ['label' => 'Javob bermadi', 'color' => 'zinc'],
            ],
            'sources' => [
                'instagram' => 'Instagram',
                'telegram' => 'Telegram',
                'google_form' => 'Google Form',
                'referral' => 'Tanish-bilish',
                'walk_in' => "O'zi kelgan",
                'other' => 'Boshqa',
            ],
        ]);
    }
}
