<div class="space-y-6">
    <div>
        <flux:heading size="xl">Dars jadvali</flux:heading>
        <flux:subheading>Sizning haftalik dars jadvalingiz</flux:subheading>
    </div>

    @if (!$this->teacher)
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>Profil topilmadi</flux:callout.heading>
            <flux:callout.text>Sizning ustoz profilingiz hali bog'lanmagan.</flux:callout.text>
        </flux:callout>
    @else
        {{-- Statistics --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500">Jami guruhlar</flux:text>
                <flux:heading size="xl">{{ $this->groups->count() }}</flux:heading>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500">Du-Chor-Jum</flux:text>
                <flux:heading size="xl">{{ $this->oddDayGroups->count() }}</flux:heading>
            </div>
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <flux:text class="text-zinc-500">Se-Pay-Shan</flux:text>
                <flux:heading size="xl">{{ $this->evenDayGroups->count() }}</flux:heading>
            </div>
        </div>

        {{-- Schedule Grid --}}
        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="w-full min-w-[600px] text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                    <tr>
                        <th class="w-20 px-4 py-3 text-left font-medium">Vaqt</th>
                        <th class="px-4 py-3 text-center font-medium">
                            <div class="flex items-center justify-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full bg-blue-500"></span>
                                Du-Chor-Jum
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center font-medium">
                            <div class="flex items-center justify-center gap-2">
                                <span class="inline-block h-3 w-3 rounded-full bg-purple-500"></span>
                                Se-Pay-Shan
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @foreach ($timeSlots as $time)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3 font-medium text-zinc-600 dark:text-zinc-400">
                                {{ $time }}
                            </td>
                            <td class="px-2 py-2">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($this->getGroupsForSlot($time, 'odd') as $group)
                                        <a
                                            href="{{ route('teacher.groups.show', $group) }}"
                                            wire:navigate
                                            class="block rounded-lg border p-2 transition-shadow hover:shadow-md {{ $this->getStatusColor($group->status) }}"
                                        >
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $group->course->code ?? $group->course->name }}
                                            </div>
                                            <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                                {{ $group->name }}
                                            </div>
                                            <div class="mt-1 text-xs text-zinc-500">
                                                {{ $group->start_time->format('H:i') }}-{{ $group->end_time->format('H:i') }} • {{ $group->room->name }}
                                            </div>
                                            <div class="mt-1 flex items-center gap-1 text-xs">
                                                <flux:icon.users class="size-3" />
                                                <span>{{ $group->enrollments_count }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-2 py-2">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($this->getGroupsForSlot($time, 'even') as $group)
                                        <a
                                            href="{{ route('teacher.groups.show', $group) }}"
                                            wire:navigate
                                            class="block rounded-lg border p-2 transition-shadow hover:shadow-md {{ $this->getStatusColor($group->status) }}"
                                        >
                                            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                                {{ $group->course->code ?? $group->course->name }}
                                            </div>
                                            <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                                {{ $group->name }}
                                            </div>
                                            <div class="mt-1 text-xs text-zinc-500">
                                                {{ $group->start_time->format('H:i') }}-{{ $group->end_time->format('H:i') }} • {{ $group->room->name }}
                                            </div>
                                            <div class="mt-1 flex items-center gap-1 text-xs">
                                                <flux:icon.users class="size-3" />
                                                <span>{{ $group->enrollments_count }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <span class="text-zinc-500">Status:</span>
            <div class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded border border-green-300 bg-green-100 dark:border-green-700 dark:bg-green-900/30"></span>
                <span>Faol</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-block h-3 w-3 rounded border border-yellow-300 bg-yellow-100 dark:border-yellow-700 dark:bg-yellow-900/30"></span>
                <span>Kutilmoqda</span>
            </div>
        </div>
    @endif
</div>
