<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" :href="route('admin.students')" icon="arrow-left" wire:navigate />
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ $student->name }}</flux:heading>
                @if ($this->activeEnrollmentsCount > 0)
                    <flux:badge color="green">O'qiyapti</flux:badge>
                @else
                    <flux:badge color="yellow">Kutayapti</flux:badge>
                @endif
            </div>
            <flux:subheading>
                {{ $student->display_phone }}
                @if ($student->address)
                    • {{ $student->address }}
                @endif
            </flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="openEditModal" icon="pencil">
            Tahrirlash
        </flux:button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Faol guruhlar</flux:text>
            <flux:heading size="xl">{{ $this->activeEnrollmentsCount }}</flux:heading>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Tugatgan kurslar</flux:text>
            <flux:heading size="xl">{{ $this->completedEnrollmentsCount }}</flux:heading>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Jami to'langan</flux:text>
            <flux:heading size="xl">{{ number_format($this->totalPaid, 0, '', ' ') }}</flux:heading>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Chegirmalar</flux:text>
            <flux:heading size="xl">{{ $student->discounts->count() }}</flux:heading>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Left Column --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- Enrollments --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="lg">Guruhlar</flux:heading>
                    <flux:button variant="primary" size="sm" wire:click="openEnrollModal" icon="plus">
                        Guruhga qo'shish
                    </flux:button>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($student->enrollments->sortByDesc('created_at') as $enrollment)
                        @php $stats = $this->getAttendanceStats($enrollment); @endphp
                        <a
                            href="{{ route('admin.groups.show', $enrollment->group) }}"
                            wire:navigate
                            class="flex items-center justify-between p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ $enrollment->group->course->name }}</span>
                                    <flux:badge
                                        size="sm"
                                        :color="$enrollment->status === 'active' ? 'green' : ($enrollment->status === 'completed' ? 'blue' : 'zinc')"
                                    >
                                        {{ $enrollment->status === 'active' ? 'Faol' : ($enrollment->status === 'completed' ? 'Tugatgan' : 'Chiqarilgan') }}
                                    </flux:badge>
                                </div>
                                <div class="mt-1 text-sm text-zinc-500">
                                    {{ $enrollment->group->name }} • {{ $enrollment->group->teacher->name }}
                                </div>
                                <div class="mt-1 text-sm text-zinc-400">
                                    {{ $enrollment->group->days_label }} | {{ $enrollment->group->start_time?->format('H:i') }}-{{ $enrollment->group->end_time?->format('H:i') }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm">
                                    <span class="text-zinc-500">To'langan:</span>
                                    <span class="font-medium">{{ number_format($enrollment->payments->sum('amount'), 0, '', ' ') }}</span>
                                </div>
                                @if ($stats['total'] > 0)
                                    <div class="mt-1 flex items-center justify-end gap-2">
                                        <div class="h-2 w-12 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                            <div class="h-full bg-green-500" style="width: {{ $stats['percentage'] }}%"></div>
                                        </div>
                                        <span class="text-xs text-zinc-500">{{ $stats['present'] }}/{{ $stats['total'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-zinc-500">
                            Hozircha guruhlar yo'q
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Payment History --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="lg">To'lovlar tarixi</flux:heading>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @php
                        $allPayments = $student->enrollments->flatMap->payments->sortByDesc('paid_at');
                    @endphp
                    @forelse ($allPayments->take(10) as $payment)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">{{ number_format($payment->amount, 0, '', ' ') }} so'm</div>
                                <div class="text-sm text-zinc-500">
                                    {{ $payment->enrollment->group->course->name }} •
                                    @if ($payment->period)
                                        {{ \Carbon\Carbon::parse($payment->period.'-01')->format('F Y') }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <flux:badge
                                    size="sm"
                                    :color="$payment->method === 'cash' ? 'green' : ($payment->method === 'card' ? 'blue' : 'purple')"
                                >
                                    {{ $payment->method === 'cash' ? 'Naqd' : ($payment->method === 'card' ? 'Karta' : "O'tkazma") }}
                                </flux:badge>
                                <div class="mt-1 text-sm text-zinc-400">{{ $payment->paid_at->format('d.m.Y') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-zinc-500">
                            Hozircha to'lovlar yo'q
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Lead History (if exists) --}}
            @if ($student->lead->count() > 0)
                @foreach ($student->lead as $lead)
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="border-b border-zinc-200 bg-amber-50 px-4 py-3 dark:border-zinc-700 dark:bg-amber-900/20">
                            <div class="flex items-center gap-2">
                                <flux:icon.clock class="size-5 text-amber-600" />
                                <flux:heading size="lg">Lead tarixi</flux:heading>
                            </div>
                            <flux:text class="text-sm text-zinc-500">
                                Bu o'quvchi {{ $lead->created_at->format('d.m.Y') }} da lead sifatida kiritilgan
                                @if ($lead->course)
                                    ({{ $lead->course->name }})
                                @endif
                            </flux:text>
                        </div>

                        @if ($lead->activities->count() > 0)
                            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($lead->activities as $activity)
                                    <div class="p-4">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <flux:badge
                                                        size="sm"
                                                        :color="\App\Models\LeadActivity::getOutcomeColors()[$activity->outcome] ?? 'zinc'"
                                                    >
                                                        {{ \App\Models\LeadActivity::getOutcomeLabels()[$activity->outcome] ?? $activity->outcome }}
                                                    </flux:badge>
                                                    @if ($activity->phone_called)
                                                        <span class="text-sm text-zinc-500">
                                                            {{ $activity->phone_called }}
                                                            @if ($activity->phone_owner)
                                                                ({{ $activity->phone_owner }})
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                                @if ($activity->notes)
                                                    <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                                                        {{ $activity->notes }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-right text-sm">
                                                <div class="text-zinc-500">{{ $activity->contacted_at->format('d.m.Y H:i') }}</div>
                                                @if ($activity->user)
                                                    <div class="text-zinc-400">{{ $activity->user->name }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center text-zinc-500">
                                Aloqa tarixi yo'q
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            {{-- Contact Info --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="lg">Kontakt ma'lumotlari</flux:heading>
                </div>
                <div class="space-y-3 p-4">
                    @foreach ($student->phones->sortByDesc('is_primary') as $phoneItem)
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium">{{ $phoneItem->number }}</div>
                                <div class="text-sm text-zinc-500">
                                    @if ($phoneItem->is_primary)
                                        Asosiy telefon
                                    @elseif ($phoneItem->owner)
                                        {{ $phoneItem->owner }}
                                    @else
                                        Qo'shimcha
                                    @endif
                                </div>
                            </div>
                            <a
                                href="tel:{{ $phoneItem->number }}"
                                class="rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                            >
                                <flux:icon.phone class="size-5" />
                            </a>
                        </div>
                    @endforeach

                    @if ($student->address)
                        <div class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
                            <div class="text-sm text-zinc-500">Manzil</div>
                            <div>{{ $student->address }}</div>
                        </div>
                    @endif

                    <div class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
                        <div class="text-sm text-zinc-500">Qayerdan topgan</div>
                        <flux:badge>{{ $sources[$student->source] ?? $student->source }}</flux:badge>
                    </div>

                    @if ($student->notes)
                        <div class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
                            <div class="text-sm text-zinc-500">Izoh</div>
                            <div class="text-sm">{{ $student->notes }}</div>
                        </div>
                    @endif

                    <div class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
                        <div class="text-sm text-zinc-500">Ro'yxatdan o'tgan</div>
                        <div>{{ $student->created_at->format('d.m.Y') }}</div>
                    </div>
                </div>
            </div>

            {{-- Discounts --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="lg">Chegirmalar</flux:heading>
                    <flux:button variant="ghost" size="sm" wire:click="openDiscountModal" icon="plus" />
                </div>
                <div class="p-4">
                    @if ($student->discounts->count() > 0)
                        <div class="space-y-2">
                            @foreach ($student->discounts as $discount)
                                <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                    <div>
                                        <div class="font-medium">{{ $discount->name }}</div>
                                        <div class="text-sm text-green-600 dark:text-green-400">
                                            {{ $discount->formatted_value }}
                                        </div>
                                    </div>
                                    <flux:button
                                        variant="ghost"
                                        size="sm"
                                        wire:click="removeDiscount({{ $discount->id }})"
                                        wire:confirm="Bu chegirmani olib tashlamoqchimisiz?"
                                        icon="x-mark"
                                        class="text-red-600 hover:text-red-700"
                                    />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-4 text-center text-zinc-500">
                            Chegirmalar yo'q
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <flux:modal wire:model="showEditModal" class="max-w-lg">
        <form wire:submit="saveStudent" class="space-y-6">
            <div>
                <flux:heading size="lg">O'quvchini tahrirlash</flux:heading>
            </div>

            <flux:input wire:model="name" label="F.I.O" placeholder="Ism Familiya" required />

            <div class="grid grid-cols-2 gap-4">
                <x-phone-input wire:model="phone" label="Asosiy telefon" required />
                <x-phone-input wire:model="home_phone" label="Uy telefoni" />
            </div>

            @if (count($phones) > 0)
                <div class="space-y-3">
                    <flux:text class="text-sm font-medium">Qo'shimcha telefonlar</flux:text>
                    @foreach ($phones as $index => $phoneItem)
                        <div class="grid grid-cols-12 items-end gap-2">
                            <div class="col-span-6">
                                <x-phone-input wire:model="phones.{{ $index }}.number" />
                            </div>
                            <div class="col-span-5">
                                <flux:input wire:model="phones.{{ $index }}.owner" placeholder="Egasi" />
                            </div>
                            <div class="col-span-1 pb-2">
                                <flux:button variant="ghost" size="sm" wire:click="removePhone({{ $index }})" icon="x-mark" class="text-red-600" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (count($phones) < 4)
                <flux:button variant="ghost" size="sm" wire:click="addPhone" icon="plus" class="text-blue-600">
                    Telefon qo'shish
                </flux:button>
            @endif

            <flux:input wire:model="address" label="Manzil" placeholder="Tuman, mahalla..." />

            <flux:select wire:model="source" label="Qayerdan topgan">
                <flux:select.option value="walk_in">O'zi kelgan</flux:select.option>
                <flux:select.option value="instagram">Instagram</flux:select.option>
                <flux:select.option value="telegram">Telegram</flux:select.option>
                <flux:select.option value="referral">Tanish-bilish</flux:select.option>
                <flux:select.option value="grand">Grand</flux:select.option>
                <flux:select.option value="other">Boshqa</flux:select.option>
            </flux:select>

            <flux:textarea wire:model="notes" label="Izoh" placeholder="Qo'shimcha ma'lumot..." rows="2" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">Saqlash</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Discount Modal --}}
    <flux:modal wire:model="showDiscountModal" class="max-w-md">
        <form wire:submit="addDiscount" class="space-y-6">
            <div>
                <flux:heading size="lg">Chegirma qo'shish</flux:heading>
            </div>

            <flux:select wire:model="discount_id" label="Chegirmani tanlang" required>
                <flux:select.option value="">Tanlang...</flux:select.option>
                @foreach ($this->availableDiscounts as $discount)
                    <flux:select.option value="{{ $discount->id }}">
                        {{ $discount->name }} ({{ $discount->formatted_value }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            @error('discount_id')
                <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
            @enderror

            @if ($this->availableDiscounts->isEmpty())
                <flux:callout color="amber" icon="exclamation-triangle">
                    <flux:callout.text>Qo'shish mumkin bo'lgan chegirmalar yo'q</flux:callout.text>
                </flux:callout>
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" :disabled="$this->availableDiscounts->isEmpty()">
                    Qo'shish
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Enroll Modal --}}
    <flux:modal wire:model="showEnrollModal" class="max-w-lg">
        <form wire:submit="enrollStudent" class="space-y-6">
            <div>
                <flux:heading size="lg">Guruhga qo'shish</flux:heading>
            </div>

            <flux:select wire:model="group_id" label="Guruhni tanlang" required>
                <flux:select.option value="">Tanlang...</flux:select.option>
                @foreach ($this->availableGroups as $group)
                    <flux:select.option value="{{ $group->id }}">
                        {{ $group->course->code }} | {{ $group->name }} | {{ $group->teacher->name }} | {{ $group->days_label }} {{ $group->start_time?->format('H:i') }}-{{ $group->end_time?->format('H:i') }} ({{ $group->enrollments_count }}/{{ $group->room->capacity }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            @error('group_id')
                <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
            @enderror

            @if ($this->availableGroups->isEmpty())
                <flux:callout color="amber" icon="exclamation-triangle">
                    <flux:callout.text>Qo'shish mumkin bo'lgan guruhlar yo'q</flux:callout.text>
                </flux:callout>
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" :disabled="$this->availableGroups->isEmpty()">
                    Guruhga qo'shish
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast notifications --}}
    <div
        x-data="{ show: false, message: '' }"
        @student-updated.window="show = true; message = 'Saqlandi!'; setTimeout(() => show = false, 3000)"
        @discount-added.window="show = true; message = 'Chegirma qo\'shildi!'; setTimeout(() => show = false, 3000)"
        @discount-removed.window="show = true; message = 'Chegirma olib tashlandi!'; setTimeout(() => show = false, 3000)"
        @student-enrolled.window="show = true; message = 'O\'quvchi guruhga qo\'shildi!'; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 rounded-lg bg-green-600 px-4 py-2 text-white shadow-lg"
    >
        <span x-text="message"></span>
    </div>
</div>
