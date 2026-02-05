<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" :href="route('teacher.dashboard')" icon="arrow-left" wire:navigate />
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ $group->name }}</flux:heading>
                <flux:badge :color="$group->status === 'active' ? 'green' : ($group->status === 'pending' ? 'yellow' : 'zinc')">
                    {{ $group->status === 'active' ? 'Faol' : ($group->status === 'pending' ? 'Kutilmoqda' : 'Tugagan') }}
                </flux:badge>
            </div>
            <flux:subheading>{{ $group->course->name }}</flux:subheading>
        </div>
    </div>

    {{-- Group Info Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Jadval</flux:text>
            <flux:heading>{{ $group->days_label }}</flux:heading>
            <flux:text class="text-sm">{{ $group->start_time?->format('H:i') }} - {{ $group->end_time?->format('H:i') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Xona</flux:text>
            <flux:heading>{{ $group->room->name }}</flux:heading>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Darslar</flux:text>
            @php $completedLessons = collect($this->lessonDates)->filter()->count(); @endphp
            <flux:heading>{{ $completedLessons }} / {{ $group->total_lessons }}</flux:heading>
            <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                <div class="h-full bg-blue-500" style="width: {{ $group->total_lessons > 0 ? ($completedLessons / $group->total_lessons * 100) : 0 }}%"></div>
            </div>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">O'quvchilar</flux:text>
            <flux:heading>{{ $this->enrollments->count() }} / {{ $group->room->capacity }}</flux:heading>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex gap-4">
            <button
                wire:click="$set('activeTab', 'students')"
                class="border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $activeTab === 'students' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                O'quvchilar
            </button>
            <button
                wire:click="$set('activeTab', 'payments')"
                class="border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $activeTab === 'payments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                To'lovlar
            </button>
            <button
                wire:click="$set('activeTab', 'attendance')"
                class="border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $activeTab === 'attendance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                Davomat
            </button>
        </nav>
    </div>

    {{-- Students Tab --}}
    @if ($activeTab === 'students')
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">#</th>
                        <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                        <th class="px-4 py-3 text-left font-medium">Telefon</th>
                        <th class="px-4 py-3 text-center font-medium">Davomat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->enrollments as $index => $enrollment)
                        @php $stats = $this->getAttendanceStats($enrollment); @endphp
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3 text-zinc-500">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $enrollment->student->name }}</div>
                            </td>
                            <td class="px-4 py-3 text-zinc-500">
                                {{ $enrollment->student->display_phone }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($stats['total'] > 0)
                                    <div class="inline-flex items-center gap-2">
                                        <div class="h-2 w-12 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                            <div class="h-full bg-green-500" style="width: {{ $stats['percentage'] }}%"></div>
                                        </div>
                                        <span class="text-xs {{ $stats['percentage'] >= 80 ? 'text-green-600' : ($stats['percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $stats['present'] }}/{{ $stats['total'] }} ({{ $stats['percentage'] }}%)
                                        </span>
                                    </div>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-zinc-500">
                                Bu guruhda o'quvchilar yo'q
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    {{-- Payments Tab --}}
    @if ($activeTab === 'payments')
        <div class="space-y-4">
            {{-- Period Selector --}}
            <div class="flex flex-wrap items-end gap-4">
                <flux:input wire:model.live="paymentPeriod" label="Davr" type="month" class="w-48" />
            </div>

            {{-- Payment Stats --}}
            <div class="grid gap-4 sm:grid-cols-4">
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <flux:text class="text-zinc-500">Jami o'quvchilar</flux:text>
                    <flux:heading size="lg">{{ $this->paymentStats['total_students'] }}</flux:heading>
                </div>
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-900/20">
                    <flux:text class="text-green-600">To'lagan</flux:text>
                    <flux:heading size="lg" class="text-green-700 dark:text-green-300">{{ $this->paymentStats['paid_students'] }}</flux:heading>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-900/20">
                    <flux:text class="text-red-600">To'lamagan</flux:text>
                    <flux:heading size="lg" class="text-red-700 dark:text-red-300">{{ $this->paymentStats['unpaid_students'] }}</flux:heading>
                </div>
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
                    <flux:text class="text-blue-600">To'lov foizi</flux:text>
                    <flux:heading size="lg" class="text-blue-700 dark:text-blue-300">{{ $this->paymentStats['payment_rate'] }}%</flux:heading>
                </div>
            </div>

            {{-- Info Callout --}}
            <flux:callout color="blue" icon="information-circle">
                <flux:callout.text>
                    Bu sahifa faqat ko'rish uchun. To'lovlarni qabul qilish faqat administrator tomonidan amalga oshiriladi.
                </flux:callout.text>
            </flux:callout>

            {{-- Payments Table --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">#</th>
                            <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                            <th class="px-4 py-3 text-left font-medium">Telefon</th>
                            <th class="px-4 py-3 text-center font-medium">Holat</th>
                            <th class="px-4 py-3 text-right font-medium">To'langan summa</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->enrollmentsWithPayments as $index => $enrollment)
                            @php
                                $paymentAmount = $enrollment->payments->sum('amount');
                                $hasPaid = $paymentAmount > 0;
                            @endphp
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3 text-zinc-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $enrollment->student->name }}</div>
                                </td>
                                <td class="px-4 py-3 text-zinc-500">
                                    {{ $enrollment->student->display_phone }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($hasPaid)
                                        <flux:badge color="green" size="sm">
                                            <flux:icon.check class="size-3" />
                                            To'lagan
                                        </flux:badge>
                                    @else
                                        <flux:badge color="red" size="sm">
                                            <flux:icon.x-mark class="size-3" />
                                            To'lamagan
                                        </flux:badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($hasPaid)
                                        <span class="font-medium text-green-600">{{ number_format($paymentAmount, 0, '', ' ') }} so'm</span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-zinc-500">
                                    Bu guruhda o'quvchilar yo'q
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($this->enrollmentsWithPayments->count() > 0)
                        <tfoot class="border-t border-zinc-300 bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800">
                            <tr>
                                <td colspan="4" class="px-4 py-3 font-medium">Jami to'langan</td>
                                <td class="px-4 py-3 text-right font-bold text-green-600">
                                    {{ number_format($this->paymentStats['total_amount'], 0, '', ' ') }} so'm
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif

    {{-- Attendance Tab --}}
    @if ($activeTab === 'attendance')
        <div class="space-y-4">
            {{-- Lesson progress indicators --}}
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="mb-2 flex items-center justify-between">
                    <flux:text class="text-sm text-zinc-500">Darslar holati</flux:text>
                    <flux:text class="text-xs text-zinc-400">
                        {{ collect($this->lessonDates)->filter()->count() }} / {{ $group->total_lessons }} dars o'tilgan
                    </flux:text>
                </div>
                <div class="flex flex-wrap gap-1">
                    @foreach ($this->lessonDates as $num => $date)
                        <button
                            wire:click="$set('lesson_number', {{ $num }})"
                            class="flex h-8 w-8 items-center justify-center rounded text-xs font-medium transition-colors
                                {{ $num == $lesson_number ? 'ring-2 ring-blue-500 ring-offset-1' : '' }}
                                {{ $date ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-zinc-200 text-zinc-600 hover:bg-zinc-300 dark:bg-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-600' }}"
                            title="{{ $date ? $num . '-dars: ' . $date : $num . '-dars: belgilanmagan' }}"
                        >
                            {{ $num }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Lesson selector --}}
            <div class="flex flex-wrap items-end gap-4">
                <flux:select wire:model.live="lesson_number" label="Dars raqami" class="w-48">
                    @foreach ($this->lessonDates as $num => $date)
                        <flux:select.option value="{{ $num }}">
                            {{ $num }}-dars {{ $date ? "✓ ($date)" : '' }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="lesson_date" type="date" label="Sana" class="w-48" />

                <div class="flex gap-2">
                    <flux:button wire:click="markAllPresent" variant="ghost" size="sm" icon="check-circle">
                        Hammasi keldi
                    </flux:button>
                    <flux:button wire:click="markAllAbsent" variant="ghost" size="sm" icon="x-circle">
                        Hammasi kelmadi
                    </flux:button>
                </div>

                <flux:button wire:click="saveAttendance" variant="primary" icon="check">
                    Saqlash
                </flux:button>
            </div>

            {{-- Attendance Table --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">#</th>
                            <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                            <th class="px-4 py-3 text-center font-medium">Statistika</th>
                            <th class="px-4 py-3 text-center font-medium">{{ $lesson_number }}-dars</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->enrollments as $index => $enrollment)
                            @php $stats = $this->getAttendanceStats($enrollment); @endphp
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50" wire:key="att-{{ $enrollment->id }}">
                                <td class="px-4 py-3 text-zinc-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium">{{ $enrollment->student->name }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($stats['total'] > 0)
                                        <div class="inline-flex items-center gap-2">
                                            <div class="h-2 w-16 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                                <div class="h-full bg-green-500" style="width: {{ $stats['percentage'] }}%"></div>
                                            </div>
                                            <span class="text-xs {{ $stats['percentage'] >= 80 ? 'text-green-600' : ($stats['percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $stats['present'] }}/{{ $stats['total'] }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        wire:click="toggleAttendance({{ $enrollment->id }})"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ ($attendance[$enrollment->id] ?? false) ? 'bg-green-100 text-green-600 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400' }}"
                                    >
                                        @if ($attendance[$enrollment->id] ?? false)
                                            <flux:icon.check class="size-5" />
                                        @else
                                            <flux:icon.x-mark class="size-5" />
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-zinc-500">
                                    Bu guruhda o'quvchilar yo'q
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Summary --}}
            @if ($this->enrollments->count() > 0)
                <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex gap-6">
                        <div>
                            <flux:text class="text-zinc-500">Jami</flux:text>
                            <flux:heading size="lg">{{ $this->enrollments->count() }}</flux:heading>
                        </div>
                        <div>
                            <flux:text class="text-green-600">Keldi</flux:text>
                            <flux:heading size="lg" class="text-green-600">{{ collect($attendance)->filter()->count() }}</flux:heading>
                        </div>
                        <div>
                            <flux:text class="text-red-600">Kelmadi</flux:text>
                            <flux:heading size="lg" class="text-red-600">{{ $this->enrollments->count() - collect($attendance)->filter()->count() }}</flux:heading>
                        </div>
                    </div>
                    <flux:button wire:click="saveAttendance" variant="primary" icon="check">
                        Davomatni saqlash
                    </flux:button>
                </div>
            @endif
        </div>
    @endif

    {{-- Toast notifications --}}
    <div
        x-data="{ show: false }"
        @attendance-saved.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 rounded-lg bg-green-600 px-4 py-2 text-white shadow-lg"
    >
        Davomat saqlandi!
    </div>
</div>
