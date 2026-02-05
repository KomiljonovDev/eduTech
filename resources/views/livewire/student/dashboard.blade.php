<div class="space-y-6">
    <div>
        <flux:heading size="xl">Salom, {{ $this->student?->name ?? auth()->user()->name }}!</flux:heading>
        <flux:subheading>O'quvchi paneli</flux:subheading>
    </div>

    @if (!$this->student)
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>Profil topilmadi</flux:callout.heading>
            <flux:callout.text>Sizning o'quvchi profilingiz hali bog'lanmagan. Administrator bilan bog'laning.</flux:callout.text>
        </flux:callout>
    @else
        {{-- Stats Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500">Faol guruhlar</flux:text>
                <flux:heading size="xl">{{ $this->stats['active_groups'] ?? 0 }}</flux:heading>
                @if (($this->stats['completed_groups'] ?? 0) > 0)
                    <flux:text class="text-sm text-green-600">{{ $this->stats['completed_groups'] }} ta tugatilgan</flux:text>
                @endif
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500">Davomat</flux:text>
                <flux:heading size="xl" class="{{ ($this->stats['attendance_rate'] ?? 0) >= 80 ? 'text-green-600' : (($this->stats['attendance_rate'] ?? 0) >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $this->stats['attendance_rate'] ?? 0 }}%
                </flux:heading>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500">Bugungi darslar</flux:text>
                <flux:heading size="xl">{{ $this->todayGroups->count() }}</flux:heading>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Today's Classes --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="lg">Bugungi darslar</flux:heading>
                    <flux:text class="text-sm text-zinc-500">{{ now()->translatedFormat('l, d F') }}</flux:text>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->todayGroups as $enrollment)
                        <div class="flex items-center justify-between p-4">
                            <div>
                                <div class="font-medium">{{ $enrollment->group->course->name }}</div>
                                <div class="text-sm text-zinc-500">{{ $enrollment->group->name }} • {{ $enrollment->group->teacher->name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">{{ $enrollment->group->start_time->format('H:i') }} - {{ $enrollment->group->end_time->format('H:i') }}</div>
                                <div class="text-sm text-zinc-500">{{ $enrollment->group->room->name }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-zinc-500">
                            Bugun darslar yo'q
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- My Groups --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="lg">Mening guruhlarim</flux:heading>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->activeEnrollments as $enrollment)
                        <a
                            href="{{ route('student.groups.show', $enrollment->group) }}"
                            wire:navigate
                            class="flex items-center justify-between p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <div>
                                <div class="font-medium">{{ $enrollment->group->course->name }}</div>
                                <div class="text-sm text-zinc-500">{{ $enrollment->group->name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $enrollment->group->days_label }}</div>
                                <div class="text-sm text-zinc-500">{{ $enrollment->group->start_time->format('H:i') }} - {{ $enrollment->group->end_time->format('H:i') }}</div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-zinc-500">
                            Hozircha guruhlar yo'q
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Payments --}}
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                <flux:heading size="lg">Oxirgi to'lovlar</flux:heading>
            </div>
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->recentPayments as $payment)
                    <div class="flex items-center justify-between p-4">
                        <div>
                            <div class="font-medium">{{ $payment->enrollment->group->course->name }}</div>
                            <div class="text-sm text-zinc-500">{{ $payment->period }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium text-green-600">{{ number_format($payment->amount, 0, '', ' ') }} so'm</div>
                            <div class="text-sm text-zinc-500">{{ $payment->paid_at->format('d.m.Y') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-zinc-500">
                        Hozircha to'lovlar yo'q
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="flex flex-wrap gap-4">
            <flux:button :href="route('student.schedule')" variant="primary" icon="calendar-days" wire:navigate>
                Dars jadvali
            </flux:button>
            <flux:button :href="route('student.payments')" variant="ghost" icon="banknotes" wire:navigate>
                To'lovlar tarixi
            </flux:button>
        </div>
    @endif
</div>
