<?php

namespace App\Livewire\Admin;

use App\Models\Discount;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::app')]
class StudentShow extends Component
{
    public Student $student;

    public bool $showEditModal = false;

    public bool $showDiscountModal = false;

    public bool $showEnrollModal = false;

    public string $name = '';

    public string $phone = '';

    public string $home_phone = '';

    /** @var array<int, array{number: string, owner: string}> */
    public array $phones = [];

    public string $address = '';

    public string $source = '';

    public string $notes = '';

    public string $discount_id = '';

    public string $group_id = '';

    public function mount(Student $student): void
    {
        $this->student = $student->load([
            'phones',
            'discounts',
            'enrollments.group.course',
            'enrollments.group.teacher',
            'enrollments.payments',
            'enrollments.attendances',
            'lead.course',
            'lead.activities.user',
        ]);
    }

    public function getTitle(): string
    {
        return $this->student->name;
    }

    #[Computed]
    public function availableDiscounts()
    {
        $existingDiscountIds = $this->student->discounts->pluck('id')->toArray();

        return Discount::where('is_active', true)
            ->whereNotIn('id', $existingDiscountIds)
            ->get();
    }

    #[Computed]
    public function availableGroups()
    {
        $enrolledGroupIds = $this->student->enrollments->pluck('group_id')->toArray();

        return Group::with(['course', 'teacher', 'room'])
            ->withCount('enrollments')
            ->whereIn('status', ['pending', 'active'])
            ->whereNotIn('id', $enrolledGroupIds)
            ->get()
            ->filter(fn ($group) => $group->enrollments_count < $group->room->capacity);
    }

    #[Computed]
    public function totalPaid(): float
    {
        return $this->student->enrollments->sum(fn ($e) => $e->payments->sum('amount'));
    }

    #[Computed]
    public function activeEnrollmentsCount(): int
    {
        return $this->student->enrollments->where('status', 'active')->count();
    }

    #[Computed]
    public function completedEnrollmentsCount(): int
    {
        return $this->student->enrollments->where('status', 'completed')->count();
    }

    public function openEditModal(): void
    {
        $this->name = $this->student->name;
        $this->phone = $this->student->primaryPhone?->number ?? '';
        $this->home_phone = $this->student->homePhone?->number ?? '';
        $this->phones = $this->student->extraPhones->map(fn ($p) => [
            'number' => $p->number,
            'owner' => $p->owner ?? '',
        ])->toArray();
        $this->address = $this->student->address ?? '';
        $this->source = $this->student->source;
        $this->notes = $this->student->notes ?? '';
        $this->showEditModal = true;
    }

    public function saveStudent(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'home_phone' => 'nullable|string|max:20',
            'phones' => 'nullable|array|max:4',
            'phones.*.number' => 'nullable|string|max:20',
            'phones.*.owner' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'source' => 'required|in:instagram,telegram,referral,walk_in,grand,other',
            'notes' => 'nullable|string',
        ]);

        $this->student->update([
            'name' => $this->name,
            'address' => $this->address ?: null,
            'source' => $this->source,
            'notes' => $this->notes ?: null,
        ]);

        $this->student->phones()->delete();

        if (! empty($this->phone)) {
            $this->student->phones()->create([
                'number' => $this->phone,
                'owner' => null,
                'is_primary' => true,
            ]);
        }

        if (! empty($this->home_phone)) {
            $this->student->phones()->create([
                'number' => $this->home_phone,
                'owner' => 'Uy',
                'is_primary' => false,
            ]);
        }

        foreach ($this->phones as $phoneData) {
            if (! empty($phoneData['number'])) {
                $this->student->phones()->create([
                    'number' => $phoneData['number'],
                    'owner' => $phoneData['owner'] ?: null,
                    'is_primary' => false,
                ]);
            }
        }

        $this->student->load('phones');
        $this->showEditModal = false;
        $this->dispatch('student-updated');
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

    public function openDiscountModal(): void
    {
        $this->discount_id = '';
        $this->showDiscountModal = true;
    }

    public function addDiscount(): void
    {
        $this->validate([
            'discount_id' => 'required|exists:discounts,id',
        ]);

        if ($this->student->discounts()->where('discount_id', $this->discount_id)->exists()) {
            $this->addError('discount_id', "Bu chegirma allaqachon qo'shilgan");

            return;
        }

        $this->student->discounts()->attach($this->discount_id, [
            'valid_from' => now(),
        ]);

        $this->student->load('discounts');
        $this->discount_id = '';
        $this->dispatch('discount-added');
    }

    public function removeDiscount(int $discountId): void
    {
        $this->student->discounts()->detach($discountId);
        $this->student->load('discounts');
        $this->dispatch('discount-removed');
    }

    public function openEnrollModal(): void
    {
        $this->group_id = '';
        $this->showEnrollModal = true;
    }

    public function enrollStudent(): void
    {
        $this->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $exists = Enrollment::where('student_id', $this->student->id)
            ->where('group_id', $this->group_id)
            ->exists();

        if ($exists) {
            $this->addError('group_id', "Bu o'quvchi allaqachon ushbu guruhda");

            return;
        }

        Enrollment::create([
            'student_id' => $this->student->id,
            'group_id' => $this->group_id,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        $this->student->load('enrollments.group.course', 'enrollments.group.teacher');
        $this->showEnrollModal = false;
        $this->group_id = '';
        $this->dispatch('student-enrolled');
    }

    public function getAttendanceStats(Enrollment $enrollment): array
    {
        $total = $enrollment->attendances->count();
        $present = $enrollment->attendances->where('present', true)->count();

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $total - $present,
            'percentage' => $total > 0 ? round(($present / $total) * 100) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.admin.student-show', [
            'sources' => [
                'instagram' => 'Instagram',
                'telegram' => 'Telegram',
                'referral' => 'Tanish-bilish',
                'walk_in' => "O'zi kelgan",
                'grand' => 'Grand',
                'other' => 'Boshqa',
            ],
        ]);
    }
}
