<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Chegirmalar</flux:heading>
            <flux:subheading>Chegirma turlarini boshqarish</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="create" icon="plus">
            Yangi chegirma
        </flux:button>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($discounts as $discount)
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 {{ !$discount->is_active ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between">
                    <div>
                        <flux:heading>{{ $discount->name }}</flux:heading>
                        <div class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ $discount->formatted_value }}
                        </div>
                    </div>
                    @if ($discount->is_active)
                        <flux:badge color="green" size="sm">Faol</flux:badge>
                    @else
                        <flux:badge color="zinc" size="sm">Nofaol</flux:badge>
                    @endif
                </div>

                @if ($discount->description)
                    <flux:text class="mt-2 text-sm text-zinc-500">{{ $discount->description }}</flux:text>
                @endif

                <div class="mt-4 flex items-center justify-between border-t border-zinc-200 pt-3 dark:border-zinc-700">
                    <flux:text size="sm" class="text-zinc-500">
                        {{ $discount->students_count }} ta o'quvchi
                    </flux:text>
                    <div class="flex gap-1">
                        <flux:button variant="ghost" size="sm" wire:click="edit({{ $discount->id }})" icon="pencil" />
                        <flux:button variant="ghost" size="sm" wire:click="delete({{ $discount->id }})" wire:confirm="Rostdan ham o'chirmoqchimisiz?" icon="trash" class="text-red-600 hover:text-red-700" />
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                <flux:text class="text-zinc-500">Hozircha chegirmalar yo'q</flux:text>
            </div>
        @endforelse
    </div>

    <flux:modal wire:model="showModal" class="max-w-md">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Chegirmani tahrirlash' : 'Yangi chegirma' }}</flux:heading>
                <flux:subheading>Chegirma ma'lumotlarini kiriting</flux:subheading>
            </div>

            <flux:input wire:model="name" label="Nomi" placeholder="Grand yutgan, Ikkinchi farzand..." required />

            <flux:select wire:model.live="type" label="Turi">
                <flux:select.option value="percentage">Foiz (%)</flux:select.option>
                <flux:select.option value="fixed">Summa (so'm)</flux:select.option>
            </flux:select>

            @if ($type === 'percentage')
                <flux:input
                    wire:model="value"
                    label="Qiymati"
                    type="number"
                    min="0"
                    max="100"
                    step="1"
                    suffix="%"
                    placeholder="10"
                    required
                />
            @else
                <flux:input
                    wire:model="value"
                    label="Qiymati"
                    type="number"
                    min="0"
                    max="10000000"
                    step="1000"
                    suffix="so'm"
                    placeholder="100000"
                    required
                />
            @endif

            <flux:textarea wire:model="description" label="Tavsif" placeholder="Chegirma haqida..." rows="2" />

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
