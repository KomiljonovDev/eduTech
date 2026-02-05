<div class="space-y-6">
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" :href="route('student.dashboard')" icon="arrow-left" wire:navigate />
        <div>
            <flux:heading size="xl">Dars jadvali</flux:heading>
            <flux:subheading>Haftalik dars jadvali</flux:subheading>
        </div>
    </div>

    @if (!$this->student)
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>Profil topilmadi</flux:callout.heading>
            <flux:callout.text>Sizning o'quvchi profilingiz hali bog'lanmagan. Administrator bilan bog'laning.</flux:callout.text>
        </flux:callout>
    @else
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Odd Days (Mon, Wed, Fri) --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="border-b border-zinc-200 bg-blue-50 px-4 py-3 dark:border-zinc-700 dark:bg-blue-900/20">
                    <flux:heading size="lg">Du - Chor - Jum</flux:heading>
                    <flux:text class="text-sm text-zinc-500">Toq kunlar</flux:text>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->oddDayGroups as $enrollment)
                        <a
                            href="{{ route('student.groups.show', $enrollment->group) }}"
                            wire:navigate
                            class="block p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $enrollment->group->course->name }}</div>
                                    <div class="text-sm text-zinc-500">{{ $enrollment->group->name }}</div>
                                    <div class="mt-1 text-sm text-zinc-400">{{ $enrollment->group->teacher->name }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-blue-600">{{ $enrollment->group->start_time->format('H:i') }}</div>
                                    <div class="text-sm text-zinc-500">{{ $enrollment->group->end_time->format('H:i') }}</div>
                                    <div class="mt-1 text-sm text-zinc-400">{{ $enrollment->group->room->name }}</div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-zinc-500">
                            Bu kunlarda darslar yo'q
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Even Days (Tue, Thu, Sat) --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <div class="border-b border-zinc-200 bg-green-50 px-4 py-3 dark:border-zinc-700 dark:bg-green-900/20">
                    <flux:heading size="lg">Se - Pay - Shan</flux:heading>
                    <flux:text class="text-sm text-zinc-500">Juft kunlar</flux:text>
                </div>
                <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->evenDayGroups as $enrollment)
                        <a
                            href="{{ route('student.groups.show', $enrollment->group) }}"
                            wire:navigate
                            class="block p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $enrollment->group->course->name }}</div>
                                    <div class="text-sm text-zinc-500">{{ $enrollment->group->name }}</div>
                                    <div class="mt-1 text-sm text-zinc-400">{{ $enrollment->group->teacher->name }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-green-600">{{ $enrollment->group->start_time->format('H:i') }}</div>
                                    <div class="text-sm text-zinc-500">{{ $enrollment->group->end_time->format('H:i') }}</div>
                                    <div class="mt-1 text-sm text-zinc-400">{{ $enrollment->group->room->name }}</div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-zinc-500">
                            Bu kunlarda darslar yo'q
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
