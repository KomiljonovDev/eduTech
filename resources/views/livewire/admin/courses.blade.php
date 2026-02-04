<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Yo'nalishlar</flux:heading>
            <flux:subheading>O'quv yo'nalishlarini boshqarish</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="create" icon="plus">
            Yangi yo'nalish
        </flux:button>
    </div>

    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Kod</th>
                    <th class="px-4 py-3 text-left font-medium">Nomi</th>
                    <th class="px-4 py-3 text-left font-medium">Oylik narx</th>
                    <th class="px-4 py-3 text-left font-medium">Guruhlar</th>
                    <th class="px-4 py-3 text-left font-medium">Holat</th>
                    <th class="px-4 py-3 text-right font-medium">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($courses as $course)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <flux:badge size="sm">{{ $course->code }}</flux:badge>
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $course->name }}</td>
                        <td class="px-4 py-3">{{ number_format($course->monthly_price, 0, '', ' ') }} so'm</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $course->groups_count }}</td>
                        <td class="px-4 py-3">
                            @if ($course->is_active)
                                <flux:badge color="green" size="sm">Faol</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">Nofaol</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button variant="ghost" size="sm" wire:click="edit({{ $course->id }})" icon="pencil" />
                            <flux:button variant="ghost" size="sm" wire:click="delete({{ $course->id }})" wire:confirm="Rostdan ham o'chirmoqchimisiz?" icon="trash" class="text-red-600 hover:text-red-700" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-zinc-500">
                            Hozircha yo'nalishlar yo'q
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <flux:modal wire:model="showModal" class="max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? "Yo'nalishni tahrirlash" : "Yangi yo'nalish" }}</flux:heading>
                <flux:subheading>Yo'nalish ma'lumotlarini kiriting</flux:subheading>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <flux:input wire:model="code" label="Kod" placeholder="WEB" class="col-span-1" required />
                <flux:input wire:model="name" label="Nomi" placeholder="Web Dasturlash" class="col-span-2" required />
            </div>

            <flux:textarea wire:model="description" label="Tavsif" placeholder="Yo'nalish haqida qisqacha..." rows="2" />

            <flux:input wire:model="monthly_price" label="Oylik narx" type="number" min="0" step="1000" suffix="so'm" required />

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
