<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <flux:button variant="ghost" href="{{ route('admin.leads') }}" icon="arrow-left" wire:navigate />
            <div>
                <flux:heading size="xl">{{ $lead->name }}</flux:heading>
                <div class="mt-1 flex items-center gap-2">
                    <flux:badge :color="$this->statuses[$lead->status]['color']">
                        {{ $this->statuses[$lead->status]['label'] }}
                    </flux:badge>
                    <flux:text class="text-zinc-500">
                        {{ $lead->created_at->format('d.m.Y H:i') }} da qo'shilgan
                    </flux:text>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if (in_array($lead->status, ['contacted', 'interested']))
                <flux:button variant="primary" wire:click="convertToStudent" icon="user-plus">
                    O'quvchiga aylantirish
                </flux:button>
            @endif
            <flux:button variant="ghost" href="{{ route('admin.leads') }}" icon="x-mark" wire:navigate />
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Left Column - Lead Info --}}
        <div class="space-y-6 lg:col-span-1">
            {{-- Contact Info --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="sm" class="mb-4">Aloqa ma'lumotlari</flux:heading>

                <div class="space-y-4">
                    {{-- Main Phone --}}
                    <div>
                        <flux:text class="text-xs text-zinc-500">Asosiy telefon</flux:text>
                        <div class="mt-1 flex items-center gap-2">
                            <flux:text class="font-medium">{{ $lead->phone }}</flux:text>
                            <a href="tel:{{ $lead->phone }}" class="text-blue-600 hover:text-blue-700">
                                <flux:icon name="phone" class="size-4" />
                            </a>
                        </div>
                    </div>

                    {{-- Home Phone --}}
                    @if ($lead->home_phone)
                        <div>
                            <flux:text class="text-xs text-zinc-500">Uy telefoni</flux:text>
                            <div class="mt-1 flex items-center gap-2">
                                <flux:text class="font-medium">{{ $lead->home_phone }}</flux:text>
                                <a href="tel:{{ $lead->home_phone }}" class="text-blue-600 hover:text-blue-700">
                                    <flux:icon name="phone" class="size-4" />
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Additional Phones --}}
                    @foreach ($lead->phones as $phone)
                        <div>
                            <flux:text class="text-xs text-zinc-500">
                                {{ $phone->owner ?? "Qo'shimcha telefon" }}
                            </flux:text>
                            <div class="mt-1 flex items-center gap-2">
                                <flux:text class="font-medium">{{ $phone->number }}</flux:text>
                                <a href="tel:{{ $phone->number }}" class="text-blue-600 hover:text-blue-700">
                                    <flux:icon name="phone" class="size-4" />
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Lead Details --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="sm" class="mb-4">Lead ma'lumotlari</flux:heading>

                <div class="space-y-4">
                    <div>
                        <flux:text class="text-xs text-zinc-500">Qiziqgan yo'nalish</flux:text>
                        <flux:text class="mt-1 font-medium">
                            {{ $lead->course?->name ?? '—' }}
                        </flux:text>
                    </div>

                    <div>
                        <flux:text class="text-xs text-zinc-500">Manba</flux:text>
                        <flux:badge size="sm" color="zinc" class="mt-1">
                            {{ $this->sources[$lead->source] ?? $lead->source }}
                        </flux:badge>
                    </div>

                    @if ($lead->preferred_time)
                        <div>
                            <flux:text class="text-xs text-zinc-500">Ma'qul vaqt</flux:text>
                            <flux:text class="mt-1 font-medium">{{ $lead->preferred_time }}</flux:text>
                        </div>
                    @endif

                    @if ($lead->next_contact_date)
                        <div>
                            <flux:text class="text-xs text-zinc-500">Keyingi aloqa</flux:text>
                            @php
                                $isOverdue = $lead->next_contact_date->isPast() && !$lead->next_contact_date->isToday();
                                $isToday = $lead->next_contact_date->isToday();
                            @endphp
                            <flux:badge size="sm" :color="$isOverdue ? 'red' : ($isToday ? 'amber' : 'blue')" class="mt-1">
                                {{ $lead->next_contact_date->format('d.m.Y') }}
                                @if ($isToday)
                                    (Bugun)
                                @elseif ($isOverdue)
                                    (O'tib ketgan)
                                @endif
                            </flux:badge>
                        </div>
                    @endif

                    @if ($lead->contacted_at)
                        <div>
                            <flux:text class="text-xs text-zinc-500">Oxirgi aloqa</flux:text>
                            <flux:text class="mt-1 font-medium">
                                {{ $lead->contacted_at->format('d.m.Y H:i') }}
                            </flux:text>
                        </div>
                    @endif

                    @if ($lead->notes)
                        <div>
                            <flux:text class="text-xs text-zinc-500">Izoh</flux:text>
                            <flux:text class="mt-1">{{ $lead->notes }}</flux:text>
                        </div>
                    @endif

                    @if ($lead->convertedStudent)
                        <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                            <flux:text class="text-xs text-zinc-500">O'quvchiga aylangan</flux:text>
                            <flux:button variant="ghost" size="sm" href="{{ route('admin.students', ['search' => $lead->convertedStudent->phone]) }}" wire:navigate class="mt-1">
                                {{ $lead->convertedStudent->name }}
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Stats --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="sm" class="mb-4">Statistika</flux:heading>

                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <flux:heading size="xl">{{ $lead->activities->count() }}</flux:heading>
                        <flux:text class="text-xs text-zinc-500">Urinishlar</flux:text>
                    </div>
                    <div class="text-center">
                        <flux:heading size="xl">
                            {{ $lead->created_at->diffInDays(now()) }}
                        </flux:heading>
                        <flux:text class="text-xs text-zinc-500">Kun</flux:text>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Activity --}}
        <div class="space-y-6 lg:col-span-2">
            {{-- New Activity Form --}}
            @if ($lead->status !== 'enrolled')
                <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                    <flux:heading size="sm" class="mb-4">Yangi qo'ng'iroq</flux:heading>

                    <form wire:submit="saveActivity" class="space-y-4">
                        {{-- Phone Selection --}}
                        @php $allPhones = $lead->getAllPhones(); @endphp
                        @if (count($allPhones) > 1)
                            <div>
                                <flux:text class="mb-2 text-sm font-medium">Qaysi raqamga?</flux:text>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($allPhones as $phoneOption)
                                        <label class="cursor-pointer">
                                            <input
                                                type="radio"
                                                wire:model="activityPhoneCalled"
                                                value="{{ $phoneOption['number'] }}"
                                                wire:change="$set('activityPhoneOwner', '{{ $phoneOption['owner'] ?? '' }}')"
                                                class="peer sr-only"
                                            />
                                            <div class="rounded-lg border border-zinc-200 px-3 py-2 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:border-zinc-700 dark:peer-checked:bg-blue-900/20">
                                                <flux:text class="text-sm font-medium">{{ $phoneOption['number'] }}</flux:text>
                                                @if ($phoneOption['owner'])
                                                    <flux:text class="text-xs text-zinc-500">{{ $phoneOption['owner'] }}</flux:text>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:select wire:model="activityOutcome" label="Natija">
                                @foreach ($this->outcomeLabels as $key => $label)
                                    <flux:select.option value="{{ $key }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:input type="date" wire:model="activityNextContactDate" label="Keyingi aloqa sanasi" />
                        </div>

                        <flux:textarea wire:model="activityNotes" label="Izoh" placeholder="Qo'ng'iroq haqida izoh..." rows="2" />

                        <div class="flex justify-end">
                            <flux:button variant="primary" type="submit" icon="plus">
                                Saqlash
                            </flux:button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Activity History --}}
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="sm" class="mb-4">
                    Aloqa tarixi ({{ $lead->activities->count() }} ta)
                </flux:heading>

                @if ($lead->activities->count() > 0)
                    <div class="space-y-4">
                        @foreach ($lead->activities as $activity)
                            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                                <div class="flex items-start justify-between">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <flux:badge :color="$this->outcomeColors[$activity->outcome]">
                                            {{ $this->outcomeLabels[$activity->outcome] }}
                                        </flux:badge>
                                        @if ($activity->phone_called)
                                            <flux:badge color="zinc">
                                                {{ $activity->phone_called }}
                                                @if ($activity->phone_owner)
                                                    <span class="text-zinc-400">({{ $activity->phone_owner }})</span>
                                                @endif
                                            </flux:badge>
                                        @endif
                                    </div>
                                    <flux:text class="text-sm text-zinc-500">
                                        {{ $activity->contacted_at->format('d.m.Y H:i') }}
                                    </flux:text>
                                </div>

                                @if ($activity->notes)
                                    <flux:text class="mt-3">{{ $activity->notes }}</flux:text>
                                @endif

                                <div class="mt-3 flex items-center justify-between text-xs text-zinc-400">
                                    <span>{{ $activity->user?->name ?? 'Noma\'lum' }}</span>
                                    @if ($activity->next_contact_date)
                                        <span>Keyingi: {{ $activity->next_contact_date->format('d.m.Y') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <flux:icon name="phone-x-mark" class="mx-auto size-12 text-zinc-300" />
                        <flux:text class="mt-2 text-zinc-500">Hali aloqa qilinmagan</flux:text>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
