<?php

namespace App\Livewire\Admin;

use App\Models\OutstandingDebt;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::app')]
#[Title('Qarzdorliklar')]
class Debts extends Component
{
    public string $search = '';

    public string $filterStatus = '';

    public string $filterReason = '';

    // Payment modal
    public bool $showPaymentModal = false;

    public ?int $payingDebtId = null;

    public string $paymentAmount = '';

    public string $paymentNotes = '';

    // Write-off modal
    public bool $showWriteOffModal = false;

    public ?int $writingOffDebtId = null;

    public string $writeOffReason = '';

    #[Computed]
    public function debts()
    {
        return OutstandingDebt::with(['enrollment.student', 'enrollment.group.course'])
            ->when($this->search, function ($query) {
                $query->whereHas('enrollment.student', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterReason, fn ($q) => $q->where('reason', $this->filterReason))
            ->latest()
            ->get();
    }

    #[Computed]
    public function stats()
    {
        $debts = OutstandingDebt::query();

        return [
            'total_count' => $debts->clone()->outstanding()->count(),
            'total_amount' => $debts->clone()->outstanding()->sum('remaining_amount'),
            'pending_count' => $debts->clone()->where('status', 'pending')->count(),
            'pending_amount' => $debts->clone()->where('status', 'pending')->sum('remaining_amount'),
            'partial_count' => $debts->clone()->where('status', 'partial')->count(),
            'partial_amount' => $debts->clone()->where('status', 'partial')->sum('remaining_amount'),
            'paid_count' => OutstandingDebt::where('status', 'paid')->count(),
            'written_off_count' => OutstandingDebt::where('status', 'written_off')->count(),
        ];
    }

    #[Computed]
    public function payingDebt()
    {
        return $this->payingDebtId
            ? OutstandingDebt::with(['enrollment.student', 'enrollment.group.course'])->find($this->payingDebtId)
            : null;
    }

    #[Computed]
    public function writingOffDebt()
    {
        return $this->writingOffDebtId
            ? OutstandingDebt::with(['enrollment.student', 'enrollment.group.course'])->find($this->writingOffDebtId)
            : null;
    }

    public function openPaymentModal(int $debtId): void
    {
        $this->payingDebtId = $debtId;
        $debt = OutstandingDebt::find($debtId);
        $this->paymentAmount = (string) $debt->remaining_amount;
        $this->paymentNotes = '';
        $this->showPaymentModal = true;
    }

    public function collectPayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:1000',
        ]);

        $debt = OutstandingDebt::find($this->payingDebtId);
        $debt->recordPayment((float) $this->paymentAmount, $this->paymentNotes ?: null);

        $this->showPaymentModal = false;
        $this->reset(['payingDebtId', 'paymentAmount', 'paymentNotes']);
        $this->dispatch('payment-collected');
    }

    public function openWriteOffModal(int $debtId): void
    {
        $this->writingOffDebtId = $debtId;
        $this->writeOffReason = '';
        $this->showWriteOffModal = true;
    }

    public function writeOff(): void
    {
        $debt = OutstandingDebt::find($this->writingOffDebtId);
        $debt->writeOff($this->writeOffReason ?: null);

        $this->showWriteOffModal = false;
        $this->reset(['writingOffDebtId', 'writeOffReason']);
        $this->dispatch('debt-written-off');
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterStatus', 'filterReason']);
    }

    public function render()
    {
        return view('livewire.admin.debts');
    }
}
