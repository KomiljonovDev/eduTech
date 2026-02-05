<div class="space-y-6">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" :href="route('teacher.dashboard')" icon="arrow-left" wire:navigate />
        <div>
            <flux:heading size="xl">Hisobim</flux:heading>
            <flux:subheading>Oylik va to'lovlar</flux:subheading>
        </div>
    </div>

    @if (!$this->teacher)
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>Profil topilmadi</flux:callout.heading>
            <flux:callout.text>Sizning ustoz profilingiz hali bog'lanmagan. Administrator bilan bog'laning.</flux:callout.text>
        </flux:callout>
    @else
        {{-- Period Selector --}}
        <div class="flex flex-wrap items-end gap-4">
            <flux:input wire:model.live="period" label="Davr" type="month" class="w-48" />
        </div>

        {{-- Stats Cards --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-6 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500">Hisoblangan</flux:text>
                <flux:heading size="xl" class="mt-1">{{ number_format($this->salaryStats['earnings'] ?? 0, 0, '', ' ') }} so'm</flux:heading>
                @if (($this->salaryStats['salary_type'] ?? '') === 'fixed')
                    <flux:text class="mt-1 text-sm text-zinc-400">Belgilangan oylik</flux:text>
                @elseif (($this->salaryStats['salary_type'] ?? '') === 'hybrid')
                    <flux:text class="mt-1 text-sm text-zinc-400">
                        {{ number_format($this->salaryStats['fixed_salary'] ?? 0, 0, '', ' ') }} + {{ $this->salaryStats['payment_percentage'] ?? 0 }}%
                    </flux:text>
                @else
                    <flux:text class="mt-1 text-sm text-zinc-400">{{ $this->salaryStats['payment_percentage'] ?? 0 }}% foiz</flux:text>
                @endif
            </div>
            <div class="rounded-lg border border-green-200 bg-green-50 p-6 dark:border-green-900 dark:bg-green-900/20">
                <flux:text class="text-green-600">To'langan</flux:text>
                <flux:heading size="xl" class="mt-1 text-green-700 dark:text-green-300">{{ number_format($this->salaryStats['paid'] ?? 0, 0, '', ' ') }} so'm</flux:heading>
            </div>
            <div class="rounded-lg border {{ ($this->salaryStats['debt'] ?? 0) > 0 ? 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20' : 'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-900/20' }} p-6">
                <flux:text class="{{ ($this->salaryStats['debt'] ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">Qoldiq</flux:text>
                <flux:heading size="xl" class="mt-1 {{ ($this->salaryStats['debt'] ?? 0) > 0 ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300' }}">
                    @if (($this->salaryStats['debt'] ?? 0) > 0)
                        {{ number_format($this->salaryStats['debt'], 0, '', ' ') }} so'm
                    @else
                        To'langan
                    @endif
                </flux:heading>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- O'quvchi to'lovlari --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="lg">O'quvchi to'lovlari</flux:heading>
                    <flux:text class="text-sm text-zinc-500">Sizning ulushingiz</flux:text>
                </div>
                @if ($this->studentPayments->count() > 0)
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->studentPayments as $payment)
                            <div class="flex items-center justify-between p-4">
                                <div>
                                    <div class="font-medium">{{ $payment->enrollment->student->name }}</div>
                                    <div class="text-sm text-zinc-500">{{ $payment->enrollment->group->name }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-green-600">+{{ number_format($payment->teacher_share, 0, '', ' ') }}</div>
                                    <div class="text-sm text-zinc-500">{{ $payment->paid_at->format('d.m.Y') }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-600 dark:bg-zinc-800">
                        <div class="flex justify-between">
                            <span class="font-medium">Jami</span>
                            <span class="font-bold text-green-600">{{ number_format($this->studentPayments->sum('teacher_share'), 0, '', ' ') }} so'm</span>
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center text-zinc-500">
                        Bu davrda to'lovlar yo'q
                    </div>
                @endif
            </div>

            {{-- To'lov tarixi --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="lg">Oylik to'lovi tarixi</flux:heading>
                    <flux:text class="text-sm text-zinc-500">Sizga to'langan summalar</flux:text>
                </div>
                @if ($this->paymentHistory->count() > 0)
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->paymentHistory as $payment)
                            <div class="flex items-center justify-between p-4">
                                <div>
                                    <div class="font-medium text-green-600">{{ number_format($payment->amount, 0, '', ' ') }} so'm</div>
                                    <div class="text-sm text-zinc-500">{{ $payment->paid_at->format('d.m.Y') }}</div>
                                </div>
                                <div class="text-right">
                                    <flux:badge size="sm" color="zinc">
                                        {{ $paymentMethods[$payment->method] ?? $payment->method }}
                                    </flux:badge>
                                    @if ($payment->notes)
                                        <div class="mt-1 text-xs text-zinc-400">{{ $payment->notes }}</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-600 dark:bg-zinc-800">
                        <div class="flex justify-between">
                            <span class="font-medium">Jami to'langan</span>
                            <span class="font-bold text-green-600">{{ number_format($this->paymentHistory->sum('amount'), 0, '', ' ') }} so'm</span>
                        </div>
                    </div>
                @else
                    <div class="p-8 text-center text-zinc-500">
                        Bu davrda to'lovlar yo'q
                    </div>
                @endif
            </div>
        </div>

        {{-- Oylik tarix --}}
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <flux:heading size="lg">Oylik tarix</flux:heading>
                <flux:text class="text-sm text-zinc-500">Oxirgi 6 oy</flux:text>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Davr</th>
                            <th class="px-4 py-3 text-right font-medium">Hisoblangan</th>
                            <th class="px-4 py-3 text-right font-medium">To'langan</th>
                            <th class="px-4 py-3 text-right font-medium">Qoldiq</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->monthlyHistory as $month)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ $month['period'] === $period ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                                <td class="px-4 py-3 font-medium">{{ $month['label'] }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($month['earnings'], 0, '', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-green-600">{{ number_format($month['paid'], 0, '', ' ') }}</td>
                                <td class="px-4 py-3 text-right {{ $month['debt'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    @if ($month['debt'] > 0)
                                        {{ number_format($month['debt'], 0, '', ' ') }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
