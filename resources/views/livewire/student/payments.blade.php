<div class="space-y-6">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" :href="route('student.dashboard')" icon="arrow-left" wire:navigate />
        <div>
            <flux:heading size="xl">To'lovlar tarixi</flux:heading>
            <flux:subheading>Barcha to'lovlaringiz</flux:subheading>
        </div>
    </div>

    @if (!$this->student)
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>Profil topilmadi</flux:callout.heading>
            <flux:callout.text>Sizning o'quvchi profilingiz hali bog'lanmagan. Administrator bilan bog'laning.</flux:callout.text>
        </flux:callout>
    @else
        {{-- Summary Card --}}
        <div class="rounded-lg border border-green-200 bg-green-50 p-6 dark:border-green-900 dark:bg-green-900/20">
            <flux:text class="text-green-600">Jami to'langan</flux:text>
            <flux:heading size="xl" class="text-green-700 dark:text-green-300">{{ number_format($this->totalPaid, 0, '', ' ') }} so'm</flux:heading>
            <flux:text class="text-sm text-green-600">{{ $this->payments->count() }} ta to'lov</flux:text>
        </div>

        {{-- Payments List --}}
        @if ($this->payments->count() > 0)
            @foreach ($this->paymentsByMonth as $month => $monthPayments)
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="sm">{{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</flux:heading>
                        <flux:text class="text-sm text-zinc-500">{{ number_format($monthPayments->sum('amount'), 0, '', ' ') }} so'm</flux:text>
                    </div>
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($monthPayments as $payment)
                            <div class="flex items-center justify-between p-4">
                                <div>
                                    <div class="font-medium">{{ $payment->enrollment->group->course->name }}</div>
                                    <div class="text-sm text-zinc-500">{{ $payment->enrollment->group->name }} • {{ $payment->period }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-green-600">{{ number_format($payment->amount, 0, '', ' ') }} so'm</div>
                                    <div class="flex items-center justify-end gap-2">
                                        <flux:badge size="sm" color="zinc">
                                            {{ $payment->method === 'cash' ? 'Naqd' : ($payment->method === 'card' ? 'Karta' : 'O\'tkazma') }}
                                        </flux:badge>
                                        <span class="text-sm text-zinc-500">{{ $payment->paid_at->format('d.m.Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="rounded-lg border border-zinc-200 p-8 text-center text-zinc-500 dark:border-zinc-700">
                Hozircha to'lovlar yo'q
            </div>
        @endif
    @endif
</div>
