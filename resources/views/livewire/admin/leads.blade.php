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

    {{-- Follow-up Alerts --}}
    @if ($todayFollowUpCount > 0 || $overdueCount > 0)
        <div class="flex flex-wrap gap-4">
            @if ($overdueCount > 0)
                <div class="flex-1 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <div class="flex items-center gap-3">
                        <flux:icon name="exclamation-triangle" class="size-5 text-red-600" />
                        <div>
                            <flux:text class="font-medium text-red-800 dark:text-red-200">
                                {{ $overdueCount }} ta o'tib ketgan qo'ng'iroq
                            </flux:text>
                            <flux:text class="text-sm text-red-600 dark:text-red-300">
                                Ushbu leadlarga qayta bog'lanish kerak
                            </flux:text>
                        </div>
                        <flux:button wire:click="$set('filterNextContact', 'overdue')" size="sm" variant="ghost" class="ml-auto text-red-600">
                            Ko'rish
                        </flux:button>
                    </div>
                </div>
            @endif
            @if ($todayFollowUpCount > 0)
                <div class="flex-1 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                    <div class="flex items-center gap-3">
                        <flux:icon name="bell" class="size-5 text-amber-600" />
                        <div>
                            <flux:text class="font-medium text-amber-800 dark:text-amber-200">
                                Bugungi qo'ng'iroqlar: {{ $todayFollowUpCount }}
                            </flux:text>
                            <flux:text class="text-sm text-amber-600 dark:text-amber-300">
                                Bugun bog'lanish rejalashtirilgan
                            </flux:text>
                        </div>
                        <flux:button wire:click="$set('filterNextContact', 'today')" size="sm" variant="ghost" class="ml-auto text-amber-600">
                            Ko'rish
                        </flux:button>
                    </div>
                </div>
            @endif
        </div>
    @endif

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
        <flux:select wire:model.live="filterNextContact" class="w-44">
            <flux:select.option value="all">Barcha sanalar</flux:select.option>
            <flux:select.option value="today">Bugun</flux:select.option>
            <flux:select.option value="overdue">O'tib ketgan</flux:select.option>
            <flux:select.option value="upcoming">Kelgusi</flux:select.option>
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
                    <th class="px-4 py-3 text-left font-medium">Urinishlar</th>
                    <th class="px-4 py-3 text-left font-medium">Sana</th>
                    <th class="px-4 py-3 text-right font-medium">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($leads as $lead)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.leads.show', $lead) }}" wire:navigate class="font-medium text-blue-600 hover:text-blue-700 hover:underline">
                                {{ $lead->name }}
                            </a>
                            @if ($lead->preferred_time)
                                <div class="text-xs text-zinc-500">{{ $lead->preferred_time }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $lead->phone }}</div>
                            @if ($lead->home_phone)
                                <div class="text-xs text-zinc-500">{{ $lead->home_phone }} (Uy)</div>
                            @endif
                            @foreach ($lead->phones as $extraPhone)
                                <div class="text-xs text-zinc-500">
                                    {{ $extraPhone->number }}
                                    @if ($extraPhone->owner)
                                        ({{ $extraPhone->owner }})
                                    @endif
                                </div>
                            @endforeach
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
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <flux:badge size="sm" color="zinc">{{ $lead->activities_count }}</flux:badge>
                                @if ($lead->next_contact_date)
                                    @php
                                        $isOverdue = $lead->next_contact_date->isPast() && !$lead->next_contact_date->isToday();
                                        $isToday = $lead->next_contact_date->isToday();
                                    @endphp
                                    <flux:badge size="sm" :color="$isOverdue ? 'red' : ($isToday ? 'amber' : 'blue')">
                                        {{ $lead->next_contact_date->format('d.m') }}
                                    </flux:badge>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-zinc-500">
                            <div>{{ $lead->created_at->format('d.m.Y') }}</div>
                            @if ($lead->contacted_at)
                                <div class="text-xs">{{ $lead->contacted_at->diffForHumans() }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button variant="ghost" size="sm" wire:click="openDetailModal({{ $lead->id }})" icon="clock" title="Tarix" />
                            @if ($lead->status !== 'enrolled')
                                <flux:button variant="ghost" size="sm" wire:click="openActivityModal({{ $lead->id }})" icon="phone" title="Qo'ng'iroq qilish" class="text-blue-600 hover:text-blue-700" />
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
                        <td colspan="8" class="px-4 py-8 text-center text-zinc-500">
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

    {{-- Activity Modal --}}
    <flux:modal wire:model="showActivityModal" class="max-w-md">
        <form wire:submit="saveActivity" class="space-y-6">
            <div>
                <flux:heading size="lg">Qo'ng'iroq natijasi</flux:heading>
                <flux:subheading>Qo'ng'iroq natijasini kiriting</flux:subheading>
            </div>

            {{-- Phone Selection --}}
            @if (count($this->activityLeadPhones) > 0)
                <div>
                    <flux:text class="mb-2 text-sm font-medium">Qaysi raqamga qo'ng'iroq qilindi?</flux:text>
                    <div class="space-y-2">
                        @foreach ($this->activityLeadPhones as $phoneOption)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 p-3 hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800 {{ $activityPhoneCalled === $phoneOption['number'] ? 'ring-2 ring-blue-500' : '' }}">
                                <input
                                    type="radio"
                                    wire:model="activityPhoneCalled"
                                    value="{{ $phoneOption['number'] }}"
                                    wire:change="$set('activityPhoneOwner', '{{ $phoneOption['owner'] ?? '' }}')"
                                    class="text-blue-600"
                                />
                                <div>
                                    <flux:text class="font-medium">{{ $phoneOption['number'] }}</flux:text>
                                    @if ($phoneOption['owner'])
                                        <flux:text class="text-xs text-zinc-500">{{ $phoneOption['owner'] }}</flux:text>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <flux:select wire:model="activityOutcome" label="Natija">
                @foreach ($this->outcomeLabels as $key => $label)
                    <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:textarea wire:model="activityNotes" label="Izoh" placeholder="Qo'shimcha ma'lumot..." rows="3" />

            <flux:input type="date" wire:model="activityNextContactDate" label="Keyingi aloqa sanasi" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit">
                    Saqlash
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Detail Modal --}}
    <flux:modal wire:model="showDetailModal" class="max-w-2xl">
        @if ($this->detailLead)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $this->detailLead->name }}</flux:heading>
                    <flux:subheading>Lead ma'lumotlari va aloqa tarixi</flux:subheading>
                </div>

                {{-- Lead Info --}}
                <div class="grid grid-cols-2 gap-4 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <div class="col-span-2">
                        <flux:text class="text-xs text-zinc-500">Telefon raqamlar</flux:text>
                        <div class="mt-1 flex flex-wrap gap-2">
                            @foreach ($this->detailLead->getAllPhones() as $phoneInfo)
                                <flux:badge size="sm" color="zinc">
                                    {{ $phoneInfo['number'] }}
                                    @if ($phoneInfo['owner'])
                                        <span class="ml-1 text-zinc-400">({{ $phoneInfo['owner'] }})</span>
                                    @endif
                                </flux:badge>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500">Yo'nalish</flux:text>
                        <flux:text class="font-medium">{{ $this->detailLead->course?->name ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-xs text-zinc-500">Holat</flux:text>
                        <flux:badge size="sm" :color="$statuses[$this->detailLead->status]['color']">
                            {{ $statuses[$this->detailLead->status]['label'] }}
                        </flux:badge>
                    </div>
                    @if ($this->detailLead->preferred_time)
                        <div>
                            <flux:text class="text-xs text-zinc-500">Ma'qul vaqt</flux:text>
                            <flux:text class="font-medium">{{ $this->detailLead->preferred_time }}</flux:text>
                        </div>
                    @endif
                    @if ($this->detailLead->notes)
                        <div class="col-span-2">
                            <flux:text class="text-xs text-zinc-500">Izoh</flux:text>
                            <flux:text class="font-medium">{{ $this->detailLead->notes }}</flux:text>
                        </div>
                    @endif
                </div>

                {{-- Activity History --}}
                <div>
                    <flux:heading size="sm" class="mb-3">Aloqa tarixi ({{ $this->detailLead->activities->count() }} ta urinish)</flux:heading>

                    @if ($this->detailLead->activities->count() > 0)
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @foreach ($this->detailLead->activities as $activity)
                                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                    <div class="flex items-start justify-between">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <flux:badge size="sm" :color="$this->outcomeColors[$activity->outcome]">
                                                {{ $this->outcomeLabels[$activity->outcome] }}
                                            </flux:badge>
                                            @if ($activity->phone_called)
                                                <flux:badge size="sm" color="zinc">
                                                    {{ $activity->phone_called }}
                                                    @if ($activity->phone_owner)
                                                        <span class="text-zinc-400">({{ $activity->phone_owner }})</span>
                                                    @endif
                                                </flux:badge>
                                            @endif
                                            @if ($activity->next_contact_date)
                                                <flux:text class="text-xs text-zinc-500">
                                                    Keyingi: {{ $activity->next_contact_date->format('d.m.Y') }}
                                                </flux:text>
                                            @endif
                                        </div>
                                        <flux:text class="text-xs text-zinc-500">
                                            {{ $activity->contacted_at->format('d.m.Y H:i') }}
                                        </flux:text>
                                    </div>
                                    @if ($activity->notes)
                                        <flux:text class="mt-2 text-sm">{{ $activity->notes }}</flux:text>
                                    @endif
                                    @if ($activity->user)
                                        <flux:text class="mt-1 text-xs text-zinc-400">
                                            {{ $activity->user->name }}
                                        </flux:text>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-lg border border-zinc-200 p-6 text-center dark:border-zinc-700">
                            <flux:icon name="phone-x-mark" class="mx-auto size-8 text-zinc-400" />
                            <flux:text class="mt-2 text-zinc-500">Hali aloqa qilinmagan</flux:text>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Yopish</flux:button>
                    </flux:modal.close>
                    @if ($this->detailLead->status !== 'enrolled')
                        <flux:button variant="primary" wire:click="openActivityModal({{ $this->detailLead->id }})">
                            Qo'ng'iroq qilish
                        </flux:button>
                    @endif
                </div>
            </div>
        @endif
    </flux:modal>
</div>
