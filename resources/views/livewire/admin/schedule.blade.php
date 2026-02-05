<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">Dars jadvali</flux:heading>
            <flux:subheading>Barcha guruhlarning haftalik jadvali</flux:subheading>
        </div>
        <div class="flex gap-2">
            <flux:button :href="route('admin.groups')" variant="ghost" icon="academic-cap" wire:navigate>
                Guruhlar
            </flux:button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-4">
        <flux:select wire:model.live="days" class="w-48">
            <flux:select.option value="">Barcha kunlar</flux:select.option>
            <flux:select.option value="odd">Du-Chor-Jum</flux:select.option>
            <flux:select.option value="even">Se-Pay-Shan</flux:select.option>
        </flux:select>

        <flux:select wire:model.live="room_id" class="w-48">
            <flux:select.option value="">Barcha xonalar</flux:select.option>
            @foreach ($this->rooms as $room)
                <flux:select.option value="{{ $room->id }}">{{ $room->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Statistics --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text class="text-zinc-500">Jami guruhlar</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->groups->count() }}</flux:heading>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text class="text-zinc-500">Du-Chor-Jum</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->oddDayGroups->count() }}</flux:heading>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text class="text-zinc-500">Se-Pay-Shan</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->evenDayGroups->count() }}</flux:heading>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <flux:text class="text-zinc-500">Jami o'quvchilar</flux:text>
            <flux:heading size="xl" class="mt-1">{{ $this->groups->sum('enrollments_count') }}</flux:heading>
        </div>
    </div>

    {{-- Schedule Grid --}}
    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full min-w-[800px] text-sm">
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
                                        href="{{ route('admin.groups.show', $group) }}"
                                        wire:navigate
                                        class="block rounded-lg border p-2 transition-shadow hover:shadow-md {{ $this->getStatusColor($group->status) }}"
                                    >
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $group->course->code ?? $group->course->name }}
                                        </div>
                                        <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                            {{ $group->name }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-2 text-xs text-zinc-500">
                                            <span>{{ $group->start_time->format('H:i') }}-{{ $group->end_time->format('H:i') }}</span>
                                            <span>•</span>
                                            <span>{{ $group->room->name ?? '—' }}</span>
                                        </div>
                                        <div class="mt-1 text-xs text-zinc-500">
                                            {{ $group->teacher->name ?? '—' }}
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
                                        href="{{ route('admin.groups.show', $group) }}"
                                        wire:navigate
                                        class="block rounded-lg border p-2 transition-shadow hover:shadow-md {{ $this->getStatusColor($group->status) }}"
                                    >
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $group->course->code ?? $group->course->name }}
                                        </div>
                                        <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                            {{ $group->name }}
                                        </div>
                                        <div class="mt-1 flex items-center gap-2 text-xs text-zinc-500">
                                            <span>{{ $group->start_time->format('H:i') }}-{{ $group->end_time->format('H:i') }}</span>
                                            <span>•</span>
                                            <span>{{ $group->room->name ?? '—' }}</span>
                                        </div>
                                        <div class="mt-1 text-xs text-zinc-500">
                                            {{ $group->teacher->name ?? '—' }}
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

    {{-- Room-based view --}}
    @if ($this->rooms->count() > 0)
        <div class="mt-8">
            <flux:heading size="lg" class="mb-4">Xonalar bo'yicha</flux:heading>
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($this->rooms as $room)
                    @php
                        $roomGroups = $this->groups->where('room_id', $room->id);
                    @endphp
                    @if ($roomGroups->count() > 0)
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800">
                                <div class="flex items-center justify-between">
                                    <flux:heading>{{ $room->name }}</flux:heading>
                                    <flux:badge size="sm">{{ $roomGroups->count() }} guruh</flux:badge>
                                </div>
                                @if ($room->capacity)
                                    <flux:text class="text-xs text-zinc-500">Sig'imi: {{ $room->capacity }} kishi</flux:text>
                                @endif
                            </div>
                            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($roomGroups->sortBy('start_time') as $group)
                                    <a
                                        href="{{ route('admin.groups.show', $group) }}"
                                        wire:navigate
                                        class="flex items-center justify-between px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                                    >
                                        <div>
                                            <div class="font-medium">{{ $group->course->name }}</div>
                                            <div class="text-sm text-zinc-500">
                                                {{ $group->days_label }} | {{ $group->start_time->format('H:i') }}-{{ $group->end_time->format('H:i') }}
                                            </div>
                                        </div>
                                        <div class="text-right text-sm">
                                            <div class="text-zinc-600 dark:text-zinc-400">{{ $group->teacher->name ?? '—' }}</div>
                                            <div class="flex items-center justify-end gap-1 text-zinc-500">
                                                <flux:icon.users class="size-3" />
                                                {{ $group->enrollments_count }}
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
</div>
