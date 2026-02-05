<div class="space-y-6">
    <div>
        <flux:heading size="xl">Davomat</flux:heading>
        <flux:subheading>Guruhlar bo'yicha davomatni belgilash</flux:subheading>
    </div>

    @if (!$this->teacher)
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>Profil topilmadi</flux:callout.heading>
            <flux:callout.text>Sizning ustoz profilingiz hali bog'lanmagan.</flux:callout.text>
        </flux:callout>
    @else
        {{-- Group Selection --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:select wire:model.live="group_id" label="Guruhni tanlang">
                <flux:select.option value="">Guruh tanlang...</flux:select.option>
                @foreach ($this->groups as $group)
                    <flux:select.option value="{{ $group->id }}">
                        {{ $group->course->code }} - {{ $group->name }} ({{ $group->enrollments_count }} ta)
                    </flux:select.option>
                @endforeach
            </flux:select>

            @if ($group_id)
                <flux:select wire:model.live="lesson_number" label="Dars raqami">
                    @foreach ($this->lessonDates as $num => $date)
                        <flux:select.option value="{{ $num }}">
                            {{ $num }}-dars {{ $date ? "($date)" : '' }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="lesson_date" type="date" label="Sana" />

                <div class="flex items-end gap-2">
                    <flux:button wire:click="saveAttendance" variant="primary" icon="check">
                        Saqlash
                    </flux:button>
                </div>
            @endif
        </div>

        @if ($this->selectedGroup)
            {{-- Group Info --}}
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <div>
                        <span class="text-zinc-500">Kurs:</span>
                        <span class="font-medium">{{ $this->selectedGroup->course->name }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500">Jadval:</span>
                        <span class="font-medium">{{ $this->selectedGroup->schedule_label }}</span>
                    </div>
                    <div>
                        <span class="text-zinc-500">Xona:</span>
                        <span class="font-medium">{{ $this->selectedGroup->room->name }}</span>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="flex gap-2">
                <flux:button wire:click="markAllPresent" variant="ghost" size="sm" icon="check-circle">
                    Hammasini keldi
                </flux:button>
                <flux:button wire:click="markAllAbsent" variant="ghost" size="sm" icon="x-circle">
                    Hammasini kelmadi
                </flux:button>
            </div>

            {{-- Attendance Table --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">#</th>
                            <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                            <th class="px-4 py-3 text-left font-medium">Telefon</th>
                            <th class="px-4 py-3 text-center font-medium">Statistika</th>
                            <th class="px-4 py-3 text-center font-medium">{{ $lesson_number }}-dars</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->enrollments as $index => $enrollment)
                            @php $stats = $this->getAttendanceStats($enrollment); @endphp
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50" wire:key="enrollment-{{ $enrollment->id }}">
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
                                <td colspan="5" class="px-4 py-8 text-center text-zinc-500">
                                    Bu guruhda faol o'quvchilar yo'q
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
                            <flux:text class="text-green-600 dark:text-green-400">Keldi</flux:text>
                            <flux:heading size="lg" class="text-green-600 dark:text-green-400">
                                {{ collect($attendance)->filter()->count() }}
                            </flux:heading>
                        </div>
                        <div>
                            <flux:text class="text-red-600 dark:text-red-400">Kelmadi</flux:text>
                            <flux:heading size="lg" class="text-red-600 dark:text-red-400">
                                {{ $this->enrollments->count() - collect($attendance)->filter()->count() }}
                            </flux:heading>
                        </div>
                    </div>
                    <flux:button wire:click="saveAttendance" variant="primary" icon="check">
                        Davomatni saqlash
                    </flux:button>
                </div>
            @endif

            {{-- Toast notification --}}
            <div
                x-data="{ show: false }"
                x-on:attendance-saved.window="show = true; setTimeout(() => show = false, 3000)"
                x-show="show"
                x-transition
                class="fixed bottom-4 right-4 rounded-lg bg-green-600 px-4 py-2 text-white shadow-lg"
            >
                Davomat saqlandi!
            </div>
        @else
            <div class="rounded-lg border border-dashed border-zinc-300 p-12 text-center dark:border-zinc-700">
                <flux:icon.clipboard-document-list class="mx-auto size-12 text-zinc-400" />
                <flux:heading size="lg" class="mt-4">Guruhni tanlang</flux:heading>
                <flux:text class="mt-2 text-zinc-500">Davomatni belgilash uchun avval guruhni tanlang</flux:text>
            </div>
        @endif
    @endif
</div>
