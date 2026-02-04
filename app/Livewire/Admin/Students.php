<?php

namespace App\Livewire\Admin;

use App\Jobs\SendSms;
use App\Models\Discount;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::app')]
#[Title("O'quvchilar")]
class Students extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public bool $showEnrollModal = false;

    public bool $showDiscountModal = false;

    public bool $showBulkSmsModal = false;

    public bool $showBulkEnrollModal = false;

    public ?int $editingId = null;

    public ?int $enrollingStudentId = null;

    public ?int $discountStudentId = null;

    public string $name = '';

    public string $phone = '';

    public string $home_phone = '';

    /** @var array<int, array{number: string, owner: string}> */
    public array $phones = [];

    public string $address = '';

    public string $source = 'walk_in';

    public string $notes = '';

    public string $group_id = '';

    public string $discount_id = '';

    /** @var array<int> */
    public array $selected = [];

    public bool $selectAll = false;

    public string $bulkSmsMessage = '';

    public string $bulkGroupId = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $filter = 'all';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'home_phone' => 'nullable|string|max:20',
            'phones' => 'nullable|array|max:4',
            'phones.*.number' => 'nullable|string|max:20',
            'phones.*.owner' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'source' => 'required|in:instagram,telegram,referral,walk_in,grand,other',
            'notes' => 'nullable|string',
        ];
    }

    #[Computed]
    public function availableGroups()
    {
        return Group::with(['course', 'teacher', 'room'])
            ->withCount('enrollments')
            ->whereIn('status', ['pending', 'active'])
            ->get()
            ->filter(fn ($group) => $group->enrollments_count < $group->room->capacity);
    }

    #[Computed]
    public function availableDiscounts()
    {
        return Discount::where('is_active', true)->get();
    }

    #[Computed]
    public function discountStudent()
    {
        return $this->discountStudentId ? Student::with('discounts')->find($this->discountStudentId) : null;
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'home_phone', 'phones', 'address', 'source', 'notes']);
        $this->source = 'walk_in';
        $this->showModal = true;
    }

    public function edit(Student $student): void
    {
        $this->editingId = $student->id;
        $this->name = $student->name;
        $this->phone = $student->phone;
        $this->home_phone = $student->home_phone ?? '';
        $this->phones = $student->phones->map(fn ($p) => ['number' => $p->number, 'owner' => $p->owner ?? ''])->toArray();
        $this->address = $student->address ?? '';
        $this->source = $student->source;
        $this->notes = $student->notes ?? '';
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

        $student = Student::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->name,
                'phone' => $this->phone,
                'home_phone' => $this->home_phone ?: null,
                'address' => $this->address ?: null,
                'source' => $this->source,
                'notes' => $this->notes ?: null,
            ]
        );

        // Sync phones
        $student->phones()->delete();
        foreach ($this->phones as $phone) {
            if (! empty($phone['number'])) {
                $student->phones()->create([
                    'number' => $phone['number'],
                    'owner' => $phone['owner'] ?: null,
                ]);
            }
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'phone', 'home_phone', 'phones', 'address', 'source', 'notes']);
    }

    public function delete(Student $student): void
    {
        $student->delete();
    }

    public function openEnrollModal(Student $student): void
    {
        $this->enrollingStudentId = $student->id;
        $this->group_id = '';
        $this->showEnrollModal = true;
    }

    public function enroll(): void
    {
        $this->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $student = Student::find($this->enrollingStudentId);
        $group = Group::find($this->group_id);

        $exists = Enrollment::where('student_id', $student->id)
            ->where('group_id', $group->id)
            ->exists();

        if ($exists) {
            $this->addError('group_id', "Bu o'quvchi allaqachon ushbu guruhda");

            return;
        }

        Enrollment::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);

        $this->showEnrollModal = false;
        $this->reset(['enrollingStudentId', 'group_id']);
    }

    public function openDiscountModal(Student $student): void
    {
        $this->discountStudentId = $student->id;
        $this->discount_id = '';
        $this->showDiscountModal = true;
    }

    public function addDiscount(): void
    {
        $this->validate([
            'discount_id' => 'required|exists:discounts,id',
        ]);

        $student = Student::find($this->discountStudentId);

        if ($student->discounts()->where('discount_id', $this->discount_id)->exists()) {
            $this->addError('discount_id', "Bu chegirma allaqachon qo'shilgan");

            return;
        }

        $student->discounts()->attach($this->discount_id, [
            'valid_from' => now(),
        ]);

        $this->discount_id = '';
    }

    public function removeDiscount(int $studentId, int $discountId): void
    {
        $student = Student::find($studentId);
        $student->discounts()->detach($discountId);
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selected = $this->getStudentsQuery()->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function updatedSelected(): void
    {
        $this->selectAll = false;
    }

    #[Computed]
    public function selectedCount(): int
    {
        return count($this->selected);
    }

    public function openBulkSmsModal(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->bulkSmsMessage = '';
        $this->showBulkSmsModal = true;
    }

    public function sendBulkSms(): void
    {
        $this->validate([
            'bulkSmsMessage' => 'required|string|min:3|max:500',
        ]);

        $students = Student::whereIn('id', $this->selected)->get();

        foreach ($students as $student) {
            SendSms::dispatch($student->phone, $this->bulkSmsMessage);
        }

        $this->showBulkSmsModal = false;
        $this->bulkSmsMessage = '';
        $this->selected = [];
        $this->selectAll = false;

        session()->flash('message', count($students)." ta o'quvchiga SMS yuborildi (navbatga qo'shildi)");
    }

    public function openBulkEnrollModal(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $this->bulkGroupId = '';
        $this->showBulkEnrollModal = true;
    }

    public function bulkEnroll(): void
    {
        $this->validate([
            'bulkGroupId' => 'required|exists:groups,id',
        ]);

        $students = Student::whereIn('id', $this->selected)->get();
        $group = Group::find($this->bulkGroupId);
        $enrolled = 0;
        $skipped = 0;

        foreach ($students as $student) {
            $exists = Enrollment::where('student_id', $student->id)
                ->where('group_id', $group->id)
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            Enrollment::create([
                'student_id' => $student->id,
                'group_id' => $group->id,
                'enrolled_at' => now(),
                'status' => 'active',
            ]);

            $enrolled++;
        }

        $this->showBulkEnrollModal = false;
        $this->bulkGroupId = '';
        $this->selected = [];
        $this->selectAll = false;

        $message = "{$enrolled} ta o'quvchi guruhga qo'shildi";
        if ($skipped > 0) {
            $message .= " ({$skipped} tasi allaqachon guruhda)";
        }

        session()->flash('message', $message);
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    protected function getStudentsQuery()
    {
        $query = Student::query()
            ->withCount(['enrollments', 'enrollments as active_enrollments_count' => function ($q) {
                $q->where('status', 'active');
            }, 'discounts'])
            ->with(['enrollments.group.course', 'discounts', 'phones']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }

        if ($this->filter === 'waiting') {
            $query->whereDoesntHave('enrollments', function ($q) {
                $q->where('status', 'active');
            });
        } elseif ($this->filter === 'active') {
            $query->whereHas('enrollments', function ($q) {
                $q->where('status', 'active');
            });
        } elseif ($this->filter === 'completed_ks') {
            $query->whereHas('enrollments', function ($q) {
                $q->where('status', 'completed')
                    ->whereHas('group.course', function ($cq) {
                        $cq->where('code', 'KS');
                    });
            });
        }

        return $query;
    }

    public function render()
    {
        return view('livewire.admin.students', [
            'students' => $this->getStudentsQuery()->latest()->paginate(20),
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
