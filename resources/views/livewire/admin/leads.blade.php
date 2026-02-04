<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Leadlar</flux:heading>
            <flux:subheading>Potensial o'quvchilarni boshqarish</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="create" icon="plus">
            Yangi lead
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Ism yoki telefon bo'yicha qidirish..." icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="filterStatus" class="w-40">
            <flux:select.option value="all">Barcha holatlar</flux:select.option>
            @foreach ($statuses as $key => $status)
                <flux:select.option value="{{ $key }}">{{ $status['label'] }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="filterCourse" class="w-40">
            <flux:select.option value="all">Barcha yo'nalishlar</flux:select.option>
            @foreach ($this->courses as $course)
                <flux:select.option value="{{ $course->id }}">{{ $course->code }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 sm:grid-cols-6">
        @foreach ($statuses as $key => $status)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700 {{ $filterStatus === $key ? 'ring-2 ring-blue-500' : '' }}">
                <flux:text class="text-xs text-zinc-500">{{ $status['label'] }}</flux:text>
                <flux:heading size="lg">{{ \App\Models\Lead::where('status', $key)->count() }}</flux:heading>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Ism</th>
                    <th class="px-4 py-3 text-left font-medium">Telefon</th>
                    <th class="px-4 py-3 text-left font-medium">Yo'nalish</th>
                    <th class="px-4 py-3 text-left font-medium">Manba</th>
                    <th class="px-4 py-3 text-left font-medium">Holat</th>
                    <th class="px-4 py-3 text-left font-medium">Sana</th>
                    <th class="px-4 py-3 text-right font-medium">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($leads as $lead)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $lead->name }}</div>
                            @if ($lead->preferred_time)
                                <div class="text-xs text-zinc-500">{{ $lead->preferred_time }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $lead->phone }}</div>
                            @if ($lead->home_phone)
                                <div class="text-xs text-zinc-500">{{ $lead->home_phone }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($lead->course)
                                <flux:badge size="sm">{{ $lead->course->code }}</flux:badge>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="zinc">{{ $sources[$lead->source] ?? $lead->source }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" :color="$statuses[$lead->status]['color']">
                                {{ $statuses[$lead->status]['label'] }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3 text-zinc-500">
                            <div>{{ $lead->created_at->format('d.m.Y') }}</div>
                            @if ($lead->contacted_at)
                                <div class="text-xs">{{ $lead->contacted_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($lead->status === 'new')
                                <flux:button variant="ghost" size="sm" wire:click="markContacted({{ $lead->id }})" icon="phone" title="Bog'lanildi" />
                            @endif
                            @if (in_array($lead->status, ['contacted', 'interested']))
                                <flux:button variant="ghost" size="sm" wire:click="convertToStudent({{ $lead->id }})" icon="user-plus" title="O'quvchiga aylantirish" class="text-green-600 hover:text-green-700" />
                            @endif
                            <flux:button variant="ghost" size="sm" wire:click="edit({{ $lead->id }})" icon="pencil" />
                            <flux:button variant="ghost" size="sm" wire:click="delete({{ $lead->id }})" wire:confirm="Rostdan ham o'chirmoqchimisiz?" icon="trash" class="text-red-600 hover:text-red-700" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-zinc-500">
                            Leadlar topilmadi
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $leads->links() }}
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Leadni tahrirlash' : 'Yangi lead' }}</flux:heading>
                <flux:subheading>Lead ma'lumotlarini kiriting</flux:subheading>
            </div>

            <flux:input wire:model="name" label="Ism" placeholder="Ism Familiya" required />

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="phone" label="Telefon" placeholder="+998 90 123 45 67" required />
                <flux:input wire:model="home_phone" label="Uy telefoni" placeholder="+998 90 123 45 67" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="course_id" label="Qiziqgan yo'nalish">
                    <flux:select.option value="">Tanlanmagan</flux:select.option>
                    @foreach ($this->courses as $course)
                        <flux:select.option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="source" label="Manba">
                    <flux:select.option value="instagram">Instagram</flux:select.option>
                    <flux:select.option value="telegram">Telegram</flux:select.option>
                    <flux:select.option value="google_form">Google Form</flux:select.option>
                    <flux:select.option value="referral">Tanish-bilish</flux:select.option>
                    <flux:select.option value="walk_in">O'zi kelgan</flux:select.option>
                    <flux:select.option value="other">Boshqa</flux:select.option>
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="preferred_time" label="Ma'qul vaqt" placeholder="Ertalab, 10:00 dan keyin..." />

                <flux:select wire:model="status" label="Holat">
                    @foreach ($statuses as $key => $status)
                        <flux:select.option value="{{ $key }}">{{ $status['label'] }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <flux:textarea wire:model="notes" label="Izoh" placeholder="Qo'shimcha ma'lumot..." rows="2" />

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
