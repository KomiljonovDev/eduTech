<?php

namespace App\Livewire\Admin;

use App\Models\Lead;
use App\Models\LeadActivity;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class LeadShow extends Component
{
    public Lead $lead;

    // Activity Form
    public string $activityOutcome = 'answered';

    public string $activityPhoneCalled = '';

    public string $activityPhoneOwner = '';

    public string $activityNotes = '';

    public string $activityNextContactDate = '';

    public function mount(Lead $lead): void
    {
        $this->lead = $lead->load(['course', 'phones', 'activities.user', 'convertedStudent']);

        // Set first phone as default
        $phones = $this->lead->getAllPhones();
        $this->activityPhoneCalled = $phones[0]['number'] ?? '';
        $this->activityPhoneOwner = $phones[0]['owner'] ?? '';
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
    public function statuses(): array
    {
        return [
            'new' => ['label' => 'Yangi', 'color' => 'blue'],
            'contacted' => ['label' => "Bog'lanildi", 'color' => 'yellow'],
            'interested' => ['label' => 'Qiziqdi', 'color' => 'green'],
            'enrolled' => ['label' => 'Yozildi', 'color' => 'emerald'],
            'not_interested' => ['label' => 'Qiziqmadi', 'color' => 'red'],
            'no_answer' => ['label' => 'Javob bermadi', 'color' => 'zinc'],
        ];
    }

    #[Computed]
    public function sources(): array
    {
        return [
            'instagram' => 'Instagram',
            'telegram' => 'Telegram',
            'google_form' => 'Google Form',
            'referral' => 'Tanish-bilish',
            'walk_in' => "O'zi kelgan",
            'other' => 'Boshqa',
        ];
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

        $this->lead->logActivity(
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
            $this->lead->update(['status' => $statusMapping[$this->activityOutcome]]);
        }

        // Refresh lead data
        $this->lead = $this->lead->fresh(['course', 'phones', 'activities.user', 'convertedStudent']);

        // Reset form
        $this->activityNotes = '';
        $this->activityNextContactDate = '';

        // Set first phone again
        $phones = $this->lead->getAllPhones();
        $this->activityPhoneCalled = $phones[0]['number'] ?? '';
        $this->activityPhoneOwner = $phones[0]['owner'] ?? '';
    }

    public function convertToStudent(): void
    {
        $student = $this->lead->convertToStudent();

        $this->redirect(route('admin.students', ['search' => $student->phone]), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.lead-show')
            ->title($this->lead->name.' - Lead');
    }
}
