<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">O'quvchilar</flux:heading>
            <flux:subheading>O'quv markaz o'quvchilarini boshqarish</flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="create" icon="plus">
            Yangi o'quvchi
        </flux:button>
    </div>

    {{-- Flash Message --}}
    @if (session('message'))
        <flux:callout color="green" icon="check-circle" dismissible>
            <flux:callout.text>{{ session('message') }}</flux:callout.text>
        </flux:callout>
    @endif

    {{-- Bulk Actions Toolbar --}}
    @if ($this->selectedCount > 0)
        <div class="flex items-center justify-between rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <div class="flex items-center gap-3">
                <flux:badge color="blue" size="lg">{{ $this->selectedCount }} ta tanlangan</flux:badge>
                <flux:button variant="ghost" size="sm" wire:click="clearSelection" icon="x-mark">
                    Bekor qilish
                </flux:button>
            </div>
            <div class="flex items-center gap-2">
                <flux:dropdown>
                    <flux:button variant="primary" icon-trailing="chevron-down">
                        Amallar
                    </flux:button>
                    <flux:menu>
                        <flux:menu.item wire:click="openBulkSmsModal" icon="chat-bubble-left">
                            SMS yuborish
                        </flux:menu.item>
                        <flux:menu.item wire:click="openBulkEnrollModal" icon="user-plus">
                            Guruhga qo'shish
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Ism yoki telefon bo'yicha qidirish..." icon="magnifying-glass" />
        </div>
        <flux:select wire:model.live="filter" class="w-48">
            <flux:select.option value="all">Barchasi</flux:select.option>
            <flux:select.option value="waiting">Kutayotganlar</flux:select.option>
            <flux:select.option value="active">Faol o'qiyotganlar</flux:select.option>
            <flux:select.option value="completed_ks">KS tugatganlar</flux:select.option>
        </flux:select>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Jami</flux:text>
            <flux:heading size="xl">{{ $students->total() }}</flux:heading>
        </div>
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
            <flux:text class="text-yellow-600 dark:text-yellow-400">Kutayotganlar</flux:text>
            <flux:heading size="xl" class="text-yellow-700 dark:text-yellow-300">
                {{ \App\Models\Student::whereDoesntHave('enrollments', fn($q) => $q->where('status', 'active'))->count() }}
            </flux:heading>
        </div>
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
            <flux:text class="text-green-600 dark:text-green-400">Faol</flux:text>
            <flux:heading size="xl" class="text-green-700 dark:text-green-300">
                {{ \App\Models\Student::whereHas('enrollments', fn($q) => $q->where('status', 'active'))->count() }}
            </flux:heading>
        </div>
        <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
            <flux:text class="text-blue-600 dark:text-blue-400">KS tugatgan</flux:text>
            <flux:heading size="xl" class="text-blue-700 dark:text-blue-300">
                {{ \App\Models\Student::whereHas('enrollments', fn($q) => $q->where('status', 'completed')->whereHas('group.course', fn($cq) => $cq->where('code', 'KS')))->count() }}
            </flux:heading>
        </div>
    </div>

    {{-- Table --}}
    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                <tr>
                    <th class="w-12 px-4 py-3">
                        <flux:checkbox wire:model.live="selectAll" />
                    </th>
                    <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                    <th class="px-4 py-3 text-left font-medium">Telefon</th>
                    <th class="px-4 py-3 text-left font-medium">Manba</th>
                    <th class="px-4 py-3 text-left font-medium">Guruhlar</th>
                    <th class="px-4 py-3 text-left font-medium">Holat</th>
                    <th class="px-4 py-3 text-left font-medium">Akkaunt</th>
                    <th class="px-4 py-3 text-right font-medium">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($students as $student)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 {{ in_array($student->id, $selected) ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                        <td class="px-4 py-3">
                            <flux:checkbox wire:model.live="selected" value="{{ $student->id }}" />
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.students.show', $student) }}" wire:navigate class="font-medium text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-300">
                                {{ $student->name }}
                            </a>
                            @if ($student->address)
                                <div class="text-xs text-zinc-500">{{ $student->address }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @foreach ($student->phones->sortByDesc('is_primary') as $phoneItem)
                                @if ($phoneItem->is_primary)
                                    <div>{{ $phoneItem->number }}</div>
                                @else
                                    <div class="text-xs text-zinc-500">
                                        {{ $phoneItem->number }}
                                        @if ($phoneItem->owner)
                                            ({{ $phoneItem->owner }})
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="zinc">{{ $sources[$student->source] ?? $student->source }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            @if ($student->enrollments->count() > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($student->enrollments as $enrollment)
                                        <flux:badge size="sm" :color="$enrollment->status === 'active' ? 'green' : ($enrollment->status === 'completed' ? 'blue' : 'zinc')">
                                            {{ $enrollment->group->course->code }}
                                            @if ($enrollment->status === 'completed')
                                                ✓
                                            @endif
                                        </flux:badge>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-1">
                                @if ($student->active_enrollments_count > 0)
                                    <flux:badge color="green" size="sm">O'qiyapti</flux:badge>
                                @else
                                    <flux:badge color="yellow" size="sm">Kutayapti</flux:badge>
                                @endif
                                @if ($student->discounts_count > 0)
                                    <flux:badge color="purple" size="sm">{{ $student->discounts_count }} chegirma</flux:badge>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($student->user)
                                <div class="flex items-center gap-2">
                                    <flux:badge color="blue" size="sm">
                                        <flux:icon.check class="size-3" />
                                        {{ $student->user->email }}
                                    </flux:badge>
                                </div>
                            @else
                                <flux:badge color="zinc" size="sm">Yo'q</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button variant="ghost" size="sm" :href="route('admin.students.show', $student)" icon="eye" title="Ko'rish" wire:navigate />
                            <flux:button variant="ghost" size="sm" wire:click="openEnrollModal({{ $student->id }})" icon="plus" title="Guruhga qo'shish" />
                            <flux:button variant="ghost" size="sm" wire:click="openDiscountModal({{ $student->id }})" icon="receipt-percent" title="Chegirmalar" />
                            @if (!$student->user)
                                <flux:button variant="ghost" size="sm" wire:click="openAccountModal({{ $student->id }})" icon="user-plus" title="Akkaunt yaratish" />
                            @else
                                <flux:button variant="ghost" size="sm" wire:click="unlinkAccount({{ $student->id }})" wire:confirm="Bu akkauntni o'chirmoqchimisiz? O'quvchi tizimga kira olmay qoladi." icon="user-minus" title="Akkauntni o'chirish" class="text-red-600" />
                            @endif
                            <flux:button variant="ghost" size="sm" wire:click="edit({{ $student->id }})" icon="pencil" />
                            <flux:button variant="ghost" size="sm" wire:click="delete({{ $student->id }})" wire:confirm="Rostdan ham o'chirmoqchimisiz?" icon="trash" class="text-red-600 hover:text-red-700" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-zinc-500">
                            O'quvchilar topilmadi
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $students->links() }}
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? "O'quvchini tahrirlash" : "Yangi o'quvchi" }}</flux:heading>
                <flux:subheading>O'quvchi ma'lumotlarini kiriting</flux:subheading>
            </div>

            <flux:input wire:model="name" label="F.I.O" placeholder="Ism Familiya" required />

            <div class="grid grid-cols-2 gap-4">
                <x-phone-input wire:model="phone" label="Asosiy telefon" required />
                <x-phone-input wire:model="home_phone" label="Uy telefoni" />
            </div>

            {{-- Additional Phones --}}
            @if (count($phones) > 0)
                <div class="space-y-3">
                    <flux:text class="text-sm font-medium">Qo'shimcha telefonlar</flux:text>
                    @foreach ($phones as $index => $phoneItem)
                        <div class="grid grid-cols-12 items-end gap-2">
                            <div class="col-span-6">
                                <x-phone-input wire:model="phones.{{ $index }}.number" />
                            </div>
                            <div class="col-span-5">
                                <flux:input wire:model="phones.{{ $index }}.owner" placeholder="Egasi (ixtiyoriy)" />
                            </div>
                            <div class="col-span-1 pb-2">
                                <flux:button variant="ghost" size="sm" wire:click="removePhone({{ $index }})" icon="x-mark" class="text-red-600" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if (count($phones) < 4)
                <flux:button variant="ghost" size="sm" wire:click="addPhone" icon="plus" class="text-blue-600">
                    Telefon qo'shish
                </flux:button>
            @endif

            <flux:input wire:model="address" label="Manzil" placeholder="Tuman, mahalla..." />

            <flux:select wire:model="source" label="Qayerdan topgan">
                <flux:select.option value="walk_in">O'zi kelgan</flux:select.option>
                <flux:select.option value="instagram">Instagram</flux:select.option>
                <flux:select.option value="telegram">Telegram</flux:select.option>
                <flux:select.option value="referral">Tanish-bilish</flux:select.option>
                <flux:select.option value="grand">Grand</flux:select.option>
                <flux:select.option value="other">Boshqa</flux:select.option>
            </flux:select>

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

    {{-- Enroll Modal --}}
    <flux:modal wire:model="showEnrollModal" class="max-w-lg">
        <form wire:submit="enroll" class="space-y-6">
            <div>
                <flux:heading size="lg">Guruhga qo'shish</flux:heading>
                <flux:subheading>O'quvchini guruhga yozish</flux:subheading>
            </div>

            <flux:select wire:model="group_id" label="Guruhni tanlang" required>
                <flux:select.option value="">Tanlang...</flux:select.option>
                @foreach ($this->availableGroups as $group)
                    <flux:select.option value="{{ $group->id }}">
                        {{ $group->course->code }} | {{ $group->name }} | {{ $group->teacher->name }} | {{ $group->days_label }} {{ $group->start_time?->format('H:i') }}-{{ $group->end_time?->format('H:i') }} ({{ $group->enrollments_count }}/{{ $group->room->capacity }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            @if ($this->availableGroups->isEmpty())
                <flux:callout color="amber" icon="exclamation-triangle">
                    <flux:callout.heading>Bo'sh guruh yo'q</flux:callout.heading>
                    <flux:callout.text>Hozirda o'quvchi qo'shish mumkin bo'lgan guruhlar mavjud emas.</flux:callout.text>
                </flux:callout>
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" :disabled="$this->availableGroups->isEmpty()">
                    Guruhga qo'shish
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Discount Modal --}}
    <flux:modal wire:model="showDiscountModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Chegirmalar</flux:heading>
                <flux:subheading>{{ $this->discountStudent?->name }} uchun chegirmalar</flux:subheading>
            </div>

            {{-- Current Discounts --}}
            @if ($this->discountStudent?->discounts->count() > 0)
                <div class="space-y-2">
                    <flux:text class="font-medium">Mavjud chegirmalar</flux:text>
                    <div class="space-y-2">
                        @foreach ($this->discountStudent->discounts as $discount)
                            <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                <div>
                                    <div class="font-medium">{{ $discount->name }}</div>
                                    <div class="text-sm text-green-600 dark:text-green-400">{{ $discount->formatted_value }}</div>
                                </div>
                                <flux:button variant="ghost" size="sm" wire:click="removeDiscount({{ $discountStudentId }}, {{ $discount->id }})" icon="x-mark" class="text-red-600 hover:text-red-700" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <flux:callout color="zinc" icon="information-circle">
                    <flux:callout.text>Bu o'quvchida hozircha chegirma yo'q</flux:callout.text>
                </flux:callout>
            @endif

            {{-- Add New Discount --}}
            <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                <flux:text class="mb-3 font-medium">Yangi chegirma qo'shish</flux:text>
                <div class="flex gap-2">
                    <div class="flex-1">
                        <flux:select wire:model="discount_id">
                            <flux:select.option value="">Chegirmani tanlang...</flux:select.option>
                            @foreach ($this->availableDiscounts as $discount)
                                <flux:select.option value="{{ $discount->id }}">
                                    {{ $discount->name }} ({{ $discount->formatted_value }})
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <flux:button variant="primary" wire:click="addDiscount" icon="plus">
                        Qo'shish
                    </flux:button>
                </div>
                @error('discount_id')
                    <flux:text class="mt-1 text-sm text-red-600">{{ $message }}</flux:text>
                @enderror
            </div>

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Yopish</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- Bulk SMS Modal --}}
    <flux:modal wire:model="showBulkSmsModal" class="max-w-lg">
        <form wire:submit="sendBulkSms" class="space-y-6">
            <div>
                <flux:heading size="lg">SMS yuborish</flux:heading>
                <flux:subheading>{{ $this->selectedCount }} ta o'quvchiga SMS yuboriladi</flux:subheading>
            </div>

            <flux:textarea
                wire:model="bulkSmsMessage"
                label="Xabar matni"
                placeholder="SMS xabarini yozing..."
                rows="4"
                required
            />

            @error('bulkSmsMessage')
                <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
            @enderror

            <flux:callout color="amber" icon="exclamation-triangle">
                <flux:callout.text>
                    SMS navbatga qo'shiladi va ketma-ket yuboriladi. Test rejimida faqat "Bu Eskiz dan test" matni ishlaydi.
                </flux:callout.text>
            </flux:callout>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" icon="paper-airplane">
                    Yuborish
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Bulk Enroll Modal --}}
    <flux:modal wire:model="showBulkEnrollModal" class="max-w-lg">
        <form wire:submit="bulkEnroll" class="space-y-6">
            <div>
                <flux:heading size="lg">Guruhga qo'shish</flux:heading>
                <flux:subheading>{{ $this->selectedCount }} ta o'quvchini guruhga qo'shish</flux:subheading>
            </div>

            <flux:select wire:model="bulkGroupId" label="Guruhni tanlang" required>
                <flux:select.option value="">Tanlang...</flux:select.option>
                @foreach ($this->availableGroups as $group)
                    <flux:select.option value="{{ $group->id }}">
                        {{ $group->course->code }} | {{ $group->name }} | {{ $group->teacher->name }} | {{ $group->days_label }} {{ $group->start_time?->format('H:i') }}-{{ $group->end_time?->format('H:i') }} ({{ $group->enrollments_count }}/{{ $group->room->capacity }})
                    </flux:select.option>
                @endforeach
            </flux:select>

            @error('bulkGroupId')
                <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
            @enderror

            @if ($this->availableGroups->isEmpty())
                <flux:callout color="amber" icon="exclamation-triangle">
                    <flux:callout.heading>Bo'sh guruh yo'q</flux:callout.heading>
                    <flux:callout.text>Hozirda o'quvchi qo'shish mumkin bo'lgan guruhlar mavjud emas.</flux:callout.text>
                </flux:callout>
            @endif

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" icon="user-plus" :disabled="$this->availableGroups->isEmpty()">
                    Guruhga qo'shish
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Account Modal --}}
    <flux:modal wire:model="showAccountModal" class="max-w-lg">
        <form wire:submit="createAccount" class="space-y-6">
            <div>
                <flux:heading size="lg">Akkaunt yaratish</flux:heading>
                <flux:subheading>O'quvchi uchun tizimga kirish akkauntini yarating</flux:subheading>
            </div>

            <flux:callout color="blue" icon="information-circle">
                <flux:callout.text>
                    Akkaunt yaratilgach o'quvchi o'z email va paroli bilan tizimga kirib, dars jadvalini, davomati va to'lovlarini ko'ra oladi.
                </flux:callout.text>
            </flux:callout>

            <flux:input wire:model="email" label="Email" type="email" placeholder="oquvchi@example.com" required />

            @error('email')
                <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
            @enderror

            <div>
                <flux:input wire:model="password" label="Parol" required />
                <flux:text class="mt-1 text-xs text-zinc-500">
                    Avtomatik yaratilgan parol. O'zgartirishingiz mumkin.
                </flux:text>
            </div>

            @error('password')
                <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
            @enderror

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" icon="user-plus">
                    Akkaunt yaratish
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Password Display Toast --}}
    <div
        x-data="{ show: false, password: '' }"
        x-on:account-created.window="show = true; password = $event.detail.password"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 max-w-md rounded-lg bg-green-600 p-4 text-white shadow-lg"
    >
        <div class="flex items-start gap-3">
            <flux:icon.check-circle class="size-6 flex-shrink-0" />
            <div>
                <div class="font-medium">Akkaunt yaratildi!</div>
                <div class="mt-1 text-sm opacity-90">
                    Parolni saqlang: <code class="rounded bg-green-700 px-2 py-0.5" x-text="password"></code>
                </div>
                <button @click="show = false" class="mt-2 text-sm underline opacity-75 hover:opacity-100">
                    Yopish
                </button>
            </div>
        </div>
    </div>
</div>
