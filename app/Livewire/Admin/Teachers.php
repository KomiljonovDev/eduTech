<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use App\Models\Teacher;
use App\Models\TeacherPayment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Ustozlar')]
class Teachers extends Component
{
    public bool $showModal = false;

    public bool $showAccountModal = false;

    public bool $showPaymentModal = false;

    public bool $showSalaryModal = false;

    public ?int $editingId = null;

    public ?int $accountTeacherId = null;

    public ?int $payingTeacherId = null;

    public ?int $salaryTeacherId = null;

    public string $name = '';

    public string $phone = '';

    public string $payment_percentage = '50';

    public string $salary_type = 'percent';

    public string $fixed_salary = '0';

    public bool $is_active = true;

    public string $email = '';

    public string $password = '';

    // Payment modal properties
    public string $paymentAmount = '';

    public string $paymentDate = '';

    public string $paymentPeriod = '';

    public string $paymentMethod = 'cash';

    public string $paymentNotes = '';

    // Salary modal properties
    public string $salaryPeriod = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'payment_percentage' => 'required|numeric|min:0|max:100',
            'salary_type' => 'required|in:fixed,percent,hybrid',
            'fixed_salary' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ];
    }

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'phone', 'payment_percentage', 'salary_type', 'fixed_salary', 'is_active']);
        $this->payment_percentage = '50';
        $this->salary_type = 'percent';
        $this->fixed_salary = '0';
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(Teacher $teacher): void
    {
        $this->editingId = $teacher->id;
        $this->name = $teacher->name;
        $this->phone = $teacher->phone ?? '';
        $this->payment_percentage = (string) $teacher->payment_percentage;
        $this->salary_type = $teacher->salary_type;
        $this->fixed_salary = (string) $teacher->fixed_salary;
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
                'salary_type' => $this->salary_type,
                'fixed_salary' => $this->fixed_salary,
                'is_active' => $this->is_active,
            ]
        );

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'phone', 'payment_percentage', 'salary_type', 'fixed_salary', 'is_active']);
    }

    public function delete(Teacher $teacher): void
    {
        $teacher->delete();
    }

    public function openAccountModal(Teacher $teacher): void
    {
        $this->accountTeacherId = $teacher->id;
        $this->email = '';
        $this->password = Str::random(10);
        $this->showAccountModal = true;
    }

    public function createAccount(): void
    {
        $this->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ], [
            'email.unique' => 'Bu email allaqachon ishlatilgan',
            'password.min' => 'Parol kamida 8 ta belgidan iborat bo\'lishi kerak',
        ]);

        $teacher = Teacher::find($this->accountTeacherId);

        if (! $teacher) {
            return;
        }

        $user = User::create([
            'name' => $teacher->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('teacher');

        $teacher->update(['user_id' => $user->id]);

        $this->showAccountModal = false;
        $this->dispatch('account-created', password: $this->password);
        $this->reset(['accountTeacherId', 'email', 'password']);
    }

    public function unlinkAccount(Teacher $teacher): void
    {
        if ($teacher->user) {
            $user = $teacher->user;
            $teacher->update(['user_id' => null]);
            $user->delete();
        }
    }

    public function openPaymentModal(Teacher $teacher): void
    {
        $this->payingTeacherId = $teacher->id;
        $this->paymentAmount = '';
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentPeriod = now()->format('Y-m');
        $this->paymentMethod = 'cash';
        $this->paymentNotes = '';
        $this->showPaymentModal = true;
    }

    public function makePayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0',
            'paymentDate' => 'required|date',
            'paymentPeriod' => 'required|string|size:7',
            'paymentMethod' => 'required|in:cash,card,transfer',
        ], [
            'paymentAmount.required' => 'Summa kiritilishi shart',
            'paymentAmount.min' => 'Summa 0 dan katta bo\'lishi kerak',
            'paymentDate.required' => 'Sana kiritilishi shart',
            'paymentPeriod.required' => 'Davr kiritilishi shart',
        ]);

        TeacherPayment::create([
            'teacher_id' => $this->payingTeacherId,
            'amount' => $this->paymentAmount,
            'paid_at' => $this->paymentDate,
            'period' => $this->paymentPeriod,
            'method' => $this->paymentMethod,
            'notes' => $this->paymentNotes ?: null,
            'user_id' => Auth::id(),
        ]);

        $this->showPaymentModal = false;
        $this->reset(['payingTeacherId', 'paymentAmount', 'paymentDate', 'paymentPeriod', 'paymentMethod', 'paymentNotes']);
        $this->dispatch('payment-created');
    }

    public function openSalaryModal(Teacher $teacher): void
    {
        $this->salaryTeacherId = $teacher->id;
        $this->salaryPeriod = now()->format('Y-m');
        $this->showSalaryModal = true;
    }

    #[Computed]
    public function salaryDetails(): array
    {
        if (! $this->salaryTeacherId) {
            return [];
        }

        $teacher = Teacher::with('groups.enrollments.payments')->find($this->salaryTeacherId);

        if (! $teacher) {
            return [];
        }

        $earnings = $teacher->calculateMonthlyEarnings($this->salaryPeriod);
        $paid = $teacher->getPaidAmount($this->salaryPeriod);
        $debt = $teacher->getDebt($this->salaryPeriod);

        // O'quvchi to'lovlari
        $studentPayments = Payment::query()
            ->whereHas('enrollment.group', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->where('period', $this->salaryPeriod)
            ->with('enrollment.student', 'enrollment.group')
            ->get();

        // To'lov tarixi
        $paymentHistory = $teacher->teacherPayments()
            ->where('period', $this->salaryPeriod)
            ->with('user')
            ->latest('paid_at')
            ->get();

        return [
            'teacher' => $teacher,
            'earnings' => $earnings,
            'paid' => $paid,
            'debt' => $debt,
            'studentPayments' => $studentPayments,
            'paymentHistory' => $paymentHistory,
        ];
    }

    public function render()
    {
        $currentPeriod = now()->format('Y-m');
        $teachers = Teacher::with('user')->latest()->get()->map(function ($teacher) use ($currentPeriod) {
            $teacher->currentMonthEarnings = $teacher->calculateMonthlyEarnings($currentPeriod);
            $teacher->currentMonthDebt = $teacher->getDebt($currentPeriod);

            return $teacher;
        });

        return view('livewire.admin.teachers', [
            'teachers' => $teachers,
            'salaryTypes' => Teacher::salaryTypes(),
            'paymentMethods' => TeacherPayment::methods(),
        ]);
    }
}
