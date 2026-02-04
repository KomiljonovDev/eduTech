<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use App\Models\Lead;
use App\Models\LeadActivity;
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

    /** @var array<int, array{number: string, owner: string}> */
    public array $phones = [];

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

    #[Url]
    public string $filterNextContact = 'all';

    // Activity Modal
    public bool $showActivityModal = false;

    public ?int $activityLeadId = null;

    public string $activityOutcome = 'answered';

    public string $activityNotes = '';

    public string $activityNextContactDate = '';

    public string $activityPhoneCalled = '';

    public string $activityPhoneOwner = '';

    // Detail Modal
    public bool $showDetailModal = false;

    public ?int $detailLeadId = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'home_phone' => 'nullable|string|max:20',
            'phones' => 'nullable|array|max:4',
            'phones.*.number' => 'nullable|string|max:20',
            'phones.*.owner' => 'nullable|string|max:50',
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

    #[Computed]
    public function detailLead(): ?Lead
    {
        if (! $this->detailLeadId) {
            return null;
        }

        return Lead::with(['activities.user', 'course', 'phones'])->find($this->detailLeadId);
    }

    #[Computed]
    public function outcomeLabels(): array
    {
        return LeadActivity::getOutcomeLabels();
    }

    #[Computed]
    public function outcomeColors(): array
    {
        return LeadActivity::getOutcomeColors();
    }

    #[Computed]
    public function activityLeadPhones(): array
    {
        if (! $this->activityLeadId) {
            return [];
        }

        $lead = Lead::with('phones')->find($this->activityLeadId);

        return $lead ? $lead->getAllPhones() : [];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'home_phone', 'phones', 'course_id', 'source', 'status', 'preferred_time', 'notes']);
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
        $this->phones = $lead->phones->map(fn ($p) => ['number' => $p->number, 'owner' => $p->owner ?? ''])->toArray();
        $this->course_id = (string) ($lead->course_id ?? '');
        $this->source = $lead->source;
        $this->status = $lead->status;
        $this->preferred_time = $lead->preferred_time ?? '';
        $this->notes = $lead->notes ?? '';
        $this->showModal = true;
    }

    public function addPhone(): void
    {
        if (count($this->phones) < 4) {
            $this->phones[] = ['number' => '', 'owner' => ''];
        }
    }

    public function removePhone(int $index): void
    {
        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
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

        $lead = Lead::updateOrCreate(['id' => $this->editingId], $data);

        // Sync phones
        $lead->phones()->delete();
        foreach ($this->phones as $phone) {
            if (! empty($phone['number'])) {
                $lead->phones()->create([
                    'number' => $phone['number'],
                    'owner' => $phone['owner'] ?: null,
                ]);
            }
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'phone', 'home_phone', 'phones', 'course_id', 'source', 'status', 'preferred_time', 'notes']);
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

    public function openActivityModal(Lead $lead): void
    {
        $this->activityLeadId = $lead->id;
        $this->activityOutcome = 'answered';
        $this->activityNotes = '';
        $this->activityNextContactDate = '';

        // Set first phone as default
        $phones = $lead->getAllPhones();
        $this->activityPhoneCalled = $phones[0]['number'] ?? '';
        $this->activityPhoneOwner = $phones[0]['owner'] ?? '';

        $this->showActivityModal = true;
    }

    public function saveActivity(): void
    {
        $this->validate([
            'activityOutcome' => 'required|in:answered,no_answer,busy,callback_requested,interested,not_interested,enrolled,other',
            'activityNotes' => 'nullable|string',
            'activityNextContactDate' => 'nullable|date',
            'activityPhoneCalled' => 'nullable|string|max:20',
            'activityPhoneOwner' => 'nullable|string|max:50',
        ]);

        $lead = Lead::findOrFail($this->activityLeadId);

        $lead->logActivity(
            $this->activityOutcome,
            $this->activityNotes ?: null,
            $this->activityNextContactDate ?: null,
            $this->activityPhoneCalled ?: null,
            $this->activityPhoneOwner ?: null
        );

        // Update lead status based on outcome
        $statusMapping = [
            'answered' => 'contacted',
            'no_answer' => 'no_answer',
            'busy' => 'contacted',
            'callback_requested' => 'contacted',
            'interested' => 'interested',
            'not_interested' => 'not_interested',
            'enrolled' => 'enrolled',
        ];

        if (isset($statusMapping[$this->activityOutcome])) {
            $lead->update(['status' => $statusMapping[$this->activityOutcome]]);
        }

        $this->showActivityModal = false;
        $this->reset(['activityLeadId', 'activityOutcome', 'activityNotes', 'activityNextContactDate', 'activityPhoneCalled', 'activityPhoneOwner']);
    }

    public function openDetailModal(Lead $lead): void
    {
        $this->detailLeadId = $lead->id;
        $this->showDetailModal = true;
    }

    public function render()
    {
        $query = Lead::query()->with(['course', 'phones'])->withCount('activities');

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

        if ($this->filterNextContact !== 'all') {
            $today = now()->toDateString();
            if ($this->filterNextContact === 'today') {
                $query->whereDate('next_contact_date', $today);
            } elseif ($this->filterNextContact === 'overdue') {
                $query->whereDate('next_contact_date', '<', $today);
            } elseif ($this->filterNextContact === 'upcoming') {
                $query->whereDate('next_contact_date', '>', $today);
            }
        }

        $todayFollowUpCount = Lead::query()->whereDate('next_contact_date', now()->toDateString())->count();
        $overdueCount = Lead::query()->whereDate('next_contact_date', '<', now()->toDateString())->count();

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
            'todayFollowUpCount' => $todayFollowUpCount,
            'overdueCount' => $overdueCount,
        ]);
    }
}
