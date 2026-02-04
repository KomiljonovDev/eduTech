<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Harajatlar</flux:heading>
            <flux:subheading>O'quv markaz xarajatlarini boshqarish</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="create" icon="plus">
            Yangi harajat
        </flux:button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-4">
        <flux:input type="month" wire:model.live="filterPeriod" label="Davr" class="w-48" />
        <flux:select wire:model.live="filterCategory" label="Kategoriya" class="w-48">
            <flux:select.option value="">Barchasi</flux:select.option>
            @foreach ($this->categories as $key => $label)
                <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @php $stats = $this->periodStats; @endphp

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <flux:text class="text-red-600 dark:text-red-400">Jami harajat</flux:text>
            <flux:heading size="xl" class="text-red-700 dark:text-red-300">
                {{ number_format($stats['total'], 0, '', ' ') }}
            </flux:heading>
            <flux:text class="text-sm text-red-600">{{ $stats['count'] }} ta yozuv</flux:text>
        </div>
        @foreach ($stats['by_category']->take(3) as $category => $amount)
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <flux:text class="text-zinc-500">{{ $this->categories[$category] ?? $category }}</flux:text>
                <flux:heading size="lg">{{ number_format($amount, 0, '', ' ') }}</flux:heading>
            </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Sana</th>
                    <th class="px-4 py-3 text-left font-medium">Sarlavha</th>
                    <th class="px-4 py-3 text-left font-medium">Kategoriya</th>
                    <th class="px-4 py-3 text-left font-medium">Davr</th>
                    <th class="px-4 py-3 text-right font-medium">Summa</th>
                    <th class="px-4 py-3 text-right font-medium">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($expenses as $expense)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">{{ $expense->expense_date->format('d.m.Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $expense->title }}</div>
                            @if ($expense->description)
                                <div class="text-xs text-zinc-500">{{ Str::limit($expense->description, 50) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm">{{ $expense->category_label }}</flux:badge>
                        </td>
                        <td class="px-4 py-3 text-zinc-500">
                            {{ $expense->period ? \Carbon\Carbon::parse($expense->period.'-01')->format('F Y') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-red-600">
                            -{{ number_format($expense->amount, 0, '', ' ') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button variant="ghost" size="sm" wire:click="edit({{ $expense->id }})" icon="pencil" />
                            <flux:button variant="ghost" size="sm" wire:click="delete({{ $expense->id }})" wire:confirm="Rostdan ham o'chirmoqchimisiz?" icon="trash" class="text-red-600 hover:text-red-700" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-zinc-500">
                            Bu davrda harajatlar yo'q
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $expenses->links() }}
    </div>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Harajatni tahrirlash' : 'Yangi harajat' }}</flux:heading>
                <flux:subheading>Harajat ma'lumotlarini kiriting</flux:subheading>
            </div>

            <flux:input wire:model="title" label="Sarlavha" placeholder="Ijara to'lovi, elektr energiya..." required />

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="category" label="Kategoriya" required>
                    @foreach ($this->categories as $key => $label)
                        <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="amount" type="number" label="Summa" min="0" step="1000" suffix="so'm" required />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="expense_date" type="date" label="Sana" required />
                <flux:input wire:model="period" type="month" label="Davr (oy)" required />
            </div>

            <flux:textarea wire:model="description" label="Tavsif" placeholder="Qo'shimcha ma'lumot..." rows="2" />

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
