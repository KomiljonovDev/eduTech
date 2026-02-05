<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" :href="route('student.dashboard')" icon="arrow-left" wire:navigate />
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
            <flux:text class="text-zinc-500">Ustoz</flux:text>
            <flux:heading>{{ $group->teacher->name }}</flux:heading>
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
    </div>

    {{-- Attendance Stats --}}
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <flux:heading size="lg">Mening davomatim</flux:heading>
        </div>
        <div class="p-4">
            <div class="grid gap-4 sm:grid-cols-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $this->attendanceStats['total'] }}</div>
                    <div class="text-sm text-zinc-500">Jami dars</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $this->attendanceStats['present'] }}</div>
                    <div class="text-sm text-zinc-500">Kelgan</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $this->attendanceStats['absent'] }}</div>
                    <div class="text-sm text-zinc-500">Kelmagan</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold {{ $this->attendanceStats['percentage'] >= 80 ? 'text-green-600' : ($this->attendanceStats['percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $this->attendanceStats['percentage'] }}%
                    </div>
                    <div class="text-sm text-zinc-500">Davomat</div>
                </div>
            </div>

            {{-- Attendance visual --}}
            @if ($this->attendanceHistory->count() > 0)
                <div class="mt-4">
                    <div class="flex flex-wrap gap-1">
                        @foreach ($this->attendanceHistory as $attendance)
                            <div
                                class="flex h-8 w-8 items-center justify-center rounded text-xs font-medium {{ $attendance->present ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}"
                                title="{{ $attendance->lesson_number }}-dars: {{ $attendance->date->format('d.m.Y') }} - {{ $attendance->present ? 'Kelgan' : 'Kelmagan' }}"
                            >
                                {{ $attendance->lesson_number }}
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-2 flex items-center gap-4 text-xs text-zinc-500">
                        <div class="flex items-center gap-1">
                            <div class="h-3 w-3 rounded bg-green-100 dark:bg-green-900/30"></div>
                            <span>Kelgan</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="h-3 w-3 rounded bg-red-100 dark:bg-red-900/30"></div>
                            <span>Kelmagan</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="mt-4 text-center text-zinc-500">
                    Hali davomat ma'lumotlari yo'q
                </div>
            @endif
        </div>
    </div>

    {{-- Payments --}}
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
        <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
            <flux:heading size="lg">To'lovlar tarixi</flux:heading>
        </div>
        @if ($this->payments->count() > 0)
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($this->payments as $payment)
                    <div class="flex items-center justify-between p-4">
                        <div>
                            <div class="font-medium">{{ $payment->period }}</div>
                            <div class="text-sm text-zinc-500">{{ $payment->paid_at->format('d.m.Y') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium text-green-600">{{ number_format($payment->amount, 0, '', ' ') }} so'm</div>
                            <flux:badge size="sm" color="zinc">
                                {{ $payment->method === 'cash' ? 'Naqd' : ($payment->method === 'card' ? 'Karta' : 'O\'tkazma') }}
                            </flux:badge>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-zinc-300 bg-zinc-100 px-4 py-3 dark:border-zinc-600 dark:bg-zinc-800">
                <div class="flex justify-between">
                    <span class="font-medium">Jami to'langan</span>
                    <span class="font-bold text-green-600">{{ number_format($this->payments->sum('amount'), 0, '', ' ') }} so'm</span>
                </div>
            </div>
        @else
            <div class="p-8 text-center text-zinc-500">
                Hozircha to'lovlar yo'q
            </div>
        @endif
    </div>
</div>
