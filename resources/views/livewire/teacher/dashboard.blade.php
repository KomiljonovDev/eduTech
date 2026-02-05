<div class="space-y-6">
    <div>
        <flux:heading size="xl">Salom, {{ $this->teacher?->name ?? auth()->user()->name }}!</flux:heading>
        <flux:subheading>Ustoz paneli</flux:subheading>
    </div>

    @if (!$this->teacher)
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>Profil topilmadi</flux:callout.heading>
            <flux:callout.text>Sizning ustoz profilingiz hali bog'lanmagan. Administrator bilan bog'laning.</flux:callout.text>
        </flux:callout>
    @else
        {{-- Stats Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500">Faol guruhlar</flux:text>
                <flux:heading size="xl">{{ $this->stats['active_groups'] ?? 0 }}</flux:heading>
                @if (($this->stats['pending_groups'] ?? 0) > 0)
                    <flux:text class="text-sm text-yellow-600">+{{ $this->stats['pending_groups'] }} kutilmoqda</flux:text>
                @endif
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500">Jami o'quvchilar</flux:text>
                <flux:heading size="xl">{{ $this->stats['total_students'] ?? 0 }}</flux:heading>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500">Bugungi davomat</flux:text>
                <flux:heading size="xl">{{ $this->attendanceToday['marked'] }} / {{ $this->attendanceToday['total'] }}</flux:heading>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Today's Groups --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="border-b border-zinc-200 px-4 py-3 dark:border-zinc-700">
                    <flux:heading size="lg">Bugungi darslar</flux:heading>
                    <flux:text class="text-sm text-zinc-500">{{ now()->format('l, d F') }}</flux:text>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->todayGroups as $group)
                        <a
                            href="{{ route('teacher.groups.show', $group) }}"
                            wire:navigate
                            class="flex items-center justify-between p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <div>
                                <div class="font-medium">{{ $group->course->name }}</div>
                                <div class="text-sm text-zinc-500">{{ $group->name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">{{ $group->start_time->format('H:i') }} - {{ $group->end_time->format('H:i') }}</div>
                                <div class="text-sm text-zinc-500">{{ $group->room->name }} • {{ $group->enrollments_count }} o'quvchi</div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-zinc-500">
                            Bugun darslar yo'q
                        </div>
                    @endforelse
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
                                <div class="font-medium">{{ $payment->enrollment->student->name }}</div>
                                <div class="text-sm text-zinc-500">{{ $payment->enrollment->group->course->name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-green-600">+{{ number_format($payment->teacher_share, 0, '', ' ') }}</div>
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
        </div>

        {{-- Quick Actions --}}
        <div class="flex flex-wrap gap-4">
            <flux:button :href="route('teacher.schedule')" variant="primary" icon="calendar-days" wire:navigate>
                Dars jadvali
            </flux:button>
            <flux:button :href="route('teacher.attendance')" variant="ghost" icon="clipboard-document-check" wire:navigate>
                Davomat belgilash
            </flux:button>
        </div>
    @endif
</div>
