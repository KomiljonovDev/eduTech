<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Qarzdorliklar</flux:heading>
            <flux:subheading>O'tkazib yuborilgan to'lovlarni boshqarish</flux:subheading>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <flux:text class="text-red-600 dark:text-red-400">Ochiq qarzlar</flux:text>
            <flux:heading size="xl" class="text-red-700 dark:text-red-300">{{ number_format($this->stats['total_amount'], 0, '', ' ') }}</flux:heading>
            <flux:text class="text-sm text-red-500">{{ $this->stats['total_count'] }} ta o'quvchi</flux:text>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
            <flux:text class="text-amber-600 dark:text-amber-400">Kutilmoqda</flux:text>
            <flux:heading size="xl" class="text-amber-700 dark:text-amber-300">{{ number_format($this->stats['pending_amount'], 0, '', ' ') }}</flux:heading>
            <flux:text class="text-sm text-amber-500">{{ $this->stats['pending_count'] }} ta</flux:text>
        </div>
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <flux:text class="text-blue-600 dark:text-blue-400">Qisman to'langan</flux:text>
            <flux:heading size="xl" class="text-blue-700 dark:text-blue-300">{{ number_format($this->stats['partial_amount'], 0, '', ' ') }}</flux:heading>
            <flux:text class="text-sm text-blue-500">{{ $this->stats['partial_count'] }} ta</flux:text>
        </div>
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
            <flux:text class="text-green-600 dark:text-green-400">Yopilgan</flux:text>
            <flux:heading size="xl" class="text-green-700 dark:text-green-300">{{ $this->stats['paid_count'] }}</flux:heading>
            <flux:text class="text-sm text-zinc-500">+ {{ $this->stats['written_off_count'] }} kechirilgan</flux:text>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex flex-wrap items-end gap-4">
            <div class="min-w-48 flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="O'quvchi ismi yoki telefoni..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="filterStatus" class="w-40">
                <flux:select.option value="">Barcha holatlar</flux:select.option>
                <flux:select.option value="pending">Kutilmoqda</flux:select.option>
                <flux:select.option value="partial">Qisman to'langan</flux:select.option>
                <flux:select.option value="paid">To'langan</flux:select.option>
                <flux:select.option value="written_off">Kechirilgan</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="filterReason" class="w-40">
                <flux:select.option value="">Barcha sabablar</flux:select.option>
                <flux:select.option value="completed">Kurs tugadi</flux:select.option>
                <flux:select.option value="dropped">Chetlashtirildi</flux:select.option>
                <flux:select.option value="transferred">Ko'chirildi</flux:select.option>
            </flux:select>
            @if ($search || $filterStatus || $filterReason)
                <flux:button variant="ghost" wire:click="clearFilters" icon="x-mark">
                    Tozalash
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Debts Table --}}
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                    <th class="px-4 py-3 text-left font-medium">Guruh</th>
                    <th class="px-4 py-3 text-left font-medium">Sabab</th>
                    <th class="px-4 py-3 text-center font-medium">Davomat</th>
                    <th class="px-4 py-3 text-right font-medium">Qarz</th>
                    <th class="px-4 py-3 text-left font-medium">Holat</th>
                    <th class="px-4 py-3 text-left font-medium">Muddat</th>
                    <th class="px-4 py-3 text-right font-medium">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->debts as $debt)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $debt->enrollment->student->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $debt->enrollment->student->phone }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $debt->enrollment->group->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $debt->enrollment->group->course->code }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" :color="$debt->reason === 'dropped' ? 'red' : ($debt->reason === 'completed' ? 'blue' : 'purple')">
                                {{ $debt->reason_label }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex items-center gap-2">
                                <div class="h-2 w-12 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                    <div class="h-full bg-green-500" style="width: {{ $debt->attendance_percentage }}%"></div>
                                </div>
                                <span class="text-xs">{{ $debt->lessons_attended }}/{{ $debt->lessons_total }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="font-medium text-red-600">{{ number_format($debt->remaining_amount, 0, '', ' ') }}</div>
                            @if ($debt->paid_amount > 0)
                                <div class="text-xs text-green-600">-{{ number_format($debt->paid_amount, 0, '', ' ') }} to'langan</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @switch($debt->status)
                                @case('pending')
                                    <flux:badge color="amber" size="sm">Kutilmoqda</flux:badge>
                                    @break
                                @case('partial')
                                    <flux:badge color="blue" size="sm">Qisman</flux:badge>
                                    @break
                                @case('paid')
                                    <flux:badge color="green" size="sm">To'langan</flux:badge>
                                    @break
                                @case('written_off')
                                    <flux:badge color="zinc" size="sm">Kechirilgan</flux:badge>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-zinc-500">
                            @if ($debt->due_date)
                                <span class="{{ $debt->due_date->isPast() && $debt->status !== 'paid' ? 'text-red-600 font-medium' : '' }}">
                                    {{ $debt->due_date->format('d.m.Y') }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if (in_array($debt->status, ['pending', 'partial']))
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button variant="primary" size="sm" wire:click="openPaymentModal({{ $debt->id }})" icon="banknotes">
                                        To'lov
                                    </flux:button>
                                    <flux:button variant="ghost" size="sm" wire:click="openWriteOffModal({{ $debt->id }})" icon="x-circle" class="text-zinc-500" title="Kechirish" />
                                </div>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-zinc-500">
                            @if ($search || $filterStatus || $filterReason)
                                Qidiruv bo'yicha natija topilmadi
                            @else
                                Hozircha qarzdorliklar yo'q
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Payment Modal --}}
    <flux:modal wire:model="showPaymentModal" class="max-w-md">
        <form wire:submit="collectPayment" class="space-y-6">
            <div>
                <flux:heading size="lg">Qarz to'lovi</flux:heading>
                @if ($this->payingDebt)
                    <flux:subheading>{{ $this->payingDebt->enrollment->student->name }}</flux:subheading>
                @endif
            </div>

            @if ($this->payingDebt)
                <div class="rounded-lg bg-zinc-100 p-4 dark:bg-zinc-800">
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-zinc-500">Guruh:</span>
                            <span class="font-medium">{{ $this->payingDebt->enrollment->group->name }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500">Sabab:</span>
                            <span class="font-medium">{{ $this->payingDebt->reason_label }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500">Jami qarz:</span>
                            <span class="font-medium">{{ number_format($this->payingDebt->original_amount, 0, '', ' ') }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500">Qolgan:</span>
                            <span class="font-bold text-red-600">{{ number_format($this->payingDebt->remaining_amount, 0, '', ' ') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <flux:input wire:model="paymentAmount" type="number" label="To'lov summasi" min="1000" step="1000" suffix="so'm" required />

            <flux:textarea wire:model="paymentNotes" label="Izoh" placeholder="Qo'shimcha ma'lumot..." rows="2" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" icon="banknotes">
                    To'lovni qabul qilish
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Write-off Modal --}}
    <flux:modal wire:model="showWriteOffModal" class="max-w-md">
        <form wire:submit="writeOff" class="space-y-6">
            <div>
                <flux:heading size="lg">Qarzni kechirish</flux:heading>
                @if ($this->writingOffDebt)
                    <flux:subheading>{{ $this->writingOffDebt->enrollment->student->name }} - {{ number_format($this->writingOffDebt->remaining_amount, 0, '', ' ') }} so'm</flux:subheading>
                @endif
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20">
                <flux:text class="text-amber-800 dark:text-amber-200">
                    Bu amal qaytarilmaydi. Qarz "kechirilgan" deb belgilanadi va statistikada hisoblanmaydi.
                </flux:text>
            </div>

            <flux:textarea wire:model="writeOffReason" label="Sabab" placeholder="Nima uchun kechirilmoqda..." rows="2" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" type="submit" icon="x-circle">
                    Kechirish
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast notifications --}}
    <div
        x-data="{ show: false, message: '' }"
        @payment-collected.window="show = true; message = 'To\\'lov qabul qilindi!'; setTimeout(() => show = false, 3000)"
        @debt-written-off.window="show = true; message = 'Qarz kechirildi!'; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 rounded-lg bg-green-600 px-4 py-2 text-white shadow-lg"
    >
        <span x-text="message"></span>
    </div>
</div>
