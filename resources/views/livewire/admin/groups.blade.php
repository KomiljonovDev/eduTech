<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Guruhlar</flux:heading>
            <flux:subheading>O'quv guruhlarini boshqarish</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="create" icon="plus">
            Yangi guruh
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
        <div class="flex flex-wrap items-end gap-4">
            <div class="min-w-48 flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Guruh nomi bo'yicha qidirish..." icon="magnifying-glass" />
            </div>
            <flux:select wire:model.live="filterCourse" class="w-40">
                <flux:select.option value="">Barcha yo'nalishlar</flux:select.option>
                @foreach ($this->courses as $course)
                    <flux:select.option value="{{ $course->id }}">{{ $course->code }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="filterTeacher" class="w-40">
                <flux:select.option value="">Barcha ustozlar</flux:select.option>
                @foreach ($this->teachers as $teacher)
                    <flux:select.option value="{{ $teacher->id }}">{{ $teacher->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="filterDays" class="w-36">
                <flux:select.option value="">Barcha kunlar</flux:select.option>
                <flux:select.option value="odd">Du-Chor-Jum</flux:select.option>
                <flux:select.option value="even">Se-Pay-Shan</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="filterStatus" class="w-36">
                <flux:select.option value="">Barcha holatlar</flux:select.option>
                <flux:select.option value="pending">Kutilmoqda</flux:select.option>
                <flux:select.option value="active">Faol</flux:select.option>
                <flux:select.option value="completed">Tugallangan</flux:select.option>
                <flux:select.option value="cancelled">Bekor qilingan</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="filterRoom" class="w-32">
                <flux:select.option value="">Barcha xonalar</flux:select.option>
                @foreach ($this->rooms as $room)
                    <flux:select.option value="{{ $room->id }}">{{ $room->name }}</flux:select.option>
                @endforeach
            </flux:select>
            @if ($search || $filterCourse || $filterTeacher || $filterDays || $filterStatus || $filterRoom)
                <flux:button variant="ghost" wire:click="clearFilters" icon="x-mark">
                    Tozalash
                </flux:button>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="flex items-center gap-4 text-sm">
        <span class="text-zinc-500">Jami: <strong>{{ $groups->count() }}</strong> ta guruh</span>
        @if ($filterStatus || $filterCourse || $filterTeacher || $filterDays || $filterRoom || $search)
            <flux:badge color="blue">Filterlangan</flux:badge>
        @endif
    </div>

    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Guruh</th>
                    <th class="px-4 py-3 text-left font-medium">Yo'nalish</th>
                    <th class="px-4 py-3 text-left font-medium">Ustoz</th>
                    <th class="px-4 py-3 text-left font-medium">Jadval</th>
                    <th class="px-4 py-3 text-left font-medium">Xona</th>
                    <th class="px-4 py-3 text-left font-medium">O'quvchilar</th>
                    <th class="px-4 py-3 text-left font-medium">Holat</th>
                    <th class="px-4 py-3 text-right font-medium">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($groups as $group)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.groups.show', $group) }}" wire:navigate class="font-medium text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400">
                                {{ $group->name }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm">{{ $group->course->code }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">{{ $group->teacher->name }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                            <div>{{ $group->days_label }}</div>
                            <div class="text-xs">{{ $group->start_time->format('H:i') }} - {{ $group->end_time->format('H:i') }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $group->room->name }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium">{{ $group->active_enrollments_count }}</span>
                            <span class="text-zinc-400">/{{ $group->room->capacity }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @switch($group->status)
                                @case('pending')
                                    <flux:badge color="yellow" size="sm">Kutilmoqda</flux:badge>
                                    @break
                                @case('active')
                                    <flux:badge color="green" size="sm">Faol</flux:badge>
                                    @break
                                @case('completed')
                                    <flux:badge color="blue" size="sm">Tugallangan</flux:badge>
                                    @break
                                @case('cancelled')
                                    <flux:badge color="red" size="sm">Bekor qilingan</flux:badge>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button variant="ghost" size="sm" :href="route('admin.groups.show', $group)" icon="eye" wire:navigate title="Ko'rish" />
                            <flux:button variant="ghost" size="sm" wire:click="edit({{ $group->id }})" icon="pencil" title="Tahrirlash" />
                            <flux:button variant="ghost" size="sm" wire:click="delete({{ $group->id }})" wire:confirm="Rostdan ham o'chirmoqchimisiz?" icon="trash" class="text-red-600 hover:text-red-700" title="O'chirish" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-zinc-500">
                            Hozircha guruhlar yo'q
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <flux:modal wire:model="showModal" class="max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Guruhni tahrirlash' : 'Yangi guruh' }}</flux:heading>
                <flux:subheading>Guruh ma'lumotlarini kiriting</flux:subheading>
            </div>

            <flux:input wire:model="name" label="Guruh nomi" placeholder="WEB-001" required />

            <div class="grid grid-cols-3 gap-4">
                <flux:select wire:model="course_id" label="Yo'nalish" required>
                    <flux:select.option value="">Tanlang...</flux:select.option>
                    @foreach ($this->courses as $course)
                        <flux:select.option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="teacher_id" label="Ustoz" required>
                    <flux:select.option value="">Tanlang...</flux:select.option>
                    @foreach ($this->teachers as $teacher)
                        <flux:select.option value="{{ $teacher->id }}">{{ $teacher->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="room_id" label="Xona" required>
                    <flux:select.option value="">Tanlang...</flux:select.option>
                    @foreach ($this->rooms as $room)
                        <flux:select.option value="{{ $room->id }}">{{ $room->name }} ({{ $room->capacity }})</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="days" label="Kunlar" required>
                    <flux:select.option value="odd">Du-Chor-Jum (toq)</flux:select.option>
                    <flux:select.option value="even">Se-Pay-Shan (juft)</flux:select.option>
                </flux:select>

                <flux:input wire:model="total_lessons" label="Jami darslar soni" type="number" min="1" max="100" required />
            </div>

            <div class="grid grid-cols-3 gap-4">
                <flux:input wire:model="start_time" label="Boshlanish vaqti" type="time" required />
                <flux:input wire:model="end_time" label="Tugash vaqti" type="time" required />
                <flux:input wire:model="start_date" label="Boshlanish sanasi" type="date" required />
            </div>

            <flux:select wire:model="status" label="Holat" required>
                <flux:select.option value="pending">Kutilmoqda</flux:select.option>
                <flux:select.option value="active">Faol</flux:select.option>
                <flux:select.option value="completed">Tugallangan</flux:select.option>
                <flux:select.option value="cancelled">Bekor qilingan</flux:select.option>
            </flux:select>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    {{ $editingId ? 'Saqlash' : "Qo'shish" }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
