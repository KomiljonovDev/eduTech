<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Xonalar</flux:heading>
            <flux:subheading>O'quv xonalarini boshqarish</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="create" icon="plus">
            Yangi xona
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($rooms as $room)
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 {{ !$room->is_active ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading size="lg">{{ $room->name }}</flux:heading>
                        <flux:text class="mt-1">Sig'imi: {{ $room->capacity }} o'rin</flux:text>
                    </div>
                    @if ($room->is_active)
                        <flux:badge color="green" size="sm">Faol</flux:badge>
                    @else
                        <flux:badge color="zinc" size="sm">Nofaol</flux:badge>
                    @endif
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <flux:text size="sm" class="text-zinc-500">{{ $room->groups_count }} ta guruh</flux:text>
                    <div class="flex gap-1">
                        <flux:button variant="ghost" size="sm" wire:click="edit({{ $room->id }})" icon="pencil" />
                        <flux:button variant="ghost" size="sm" wire:click="delete({{ $room->id }})" wire:confirm="Rostdan ham o'chirmoqchimisiz?" icon="trash" class="text-red-600 hover:text-red-700" />
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                <flux:text class="text-zinc-500">Hozircha xonalar yo'q</flux:text>
            </div>
        @endforelse
    </div>

    <flux:modal wire:model="showModal" class="max-w-md">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Xonani tahrirlash' : 'Yangi xona' }}</flux:heading>
                <flux:subheading>Xona ma'lumotlarini kiriting</flux:subheading>
            </div>

            <flux:input wire:model="name" label="Xona nomi" placeholder="1-xona" required />

            <flux:input wire:model="capacity" label="Sig'imi" type="number" min="1" max="100" suffix="o'rin" required />

            <flux:checkbox wire:model="is_active" label="Faol" />

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
