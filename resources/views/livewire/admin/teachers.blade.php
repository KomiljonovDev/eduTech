<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Ustozlar</flux:heading>
            <flux:subheading>O'quv markaz ustozlarini boshqarish</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="create" icon="plus">
            Yangi ustoz
        </flux:button>
    </div>

    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Ism</th>
                    <th class="px-4 py-3 text-left font-medium">Telefon</th>
                    <th class="px-4 py-3 text-left font-medium">To'lov %</th>
                    <th class="px-4 py-3 text-left font-medium">Holat</th>
                    <th class="px-4 py-3 text-right font-medium">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($teachers as $teacher)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3 font-medium">{{ $teacher->name }}</td>
                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $teacher->phone ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $teacher->payment_percentage }}%</td>
                        <td class="px-4 py-3">
                            @if ($teacher->is_active)
                                <flux:badge color="green" size="sm">Faol</flux:badge>
                            @else
                                <flux:badge color="zinc" size="sm">Nofaol</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button variant="ghost" size="sm" wire:click="edit({{ $teacher->id }})" icon="pencil" />
                            <flux:button variant="ghost" size="sm" wire:click="delete({{ $teacher->id }})" wire:confirm="Rostdan ham o'chirmoqchimisiz?" icon="trash" class="text-red-600 hover:text-red-700" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-zinc-500">
                            Hozircha ustozlar yo'q
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <flux:modal wire:model="showModal" class="max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Ustozni tahrirlash' : 'Yangi ustoz' }}</flux:heading>
                <flux:subheading>Ustoz ma'lumotlarini kiriting</flux:subheading>
            </div>

            <flux:input wire:model="name" label="Ism" placeholder="Ustoz ismi" required />

            <flux:input wire:model="phone" label="Telefon" placeholder="+998 90 123 45 67" />

            <flux:input wire:model="payment_percentage" label="To'lov foizi" type="number" min="0" max="100" suffix="%" required />

            <flux:checkbox wire:model="is_active" label="Faol" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    {{ $editingId ? 'Saqlash' : 'Qo\'shish' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
