<div class="space-y-6">
    {{-- Header --}}
    <div>
        <flux:heading size="xl">Dashboard</flux:heading>
        <flux:subheading>O'quv markaz umumiy ko'rinishi</flux:subheading>
    </div>

    @php $stats = $this->stats; @endphp

    {{-- Stats Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Students --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                    <flux:icon.user-group class="size-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <flux:text class="text-zinc-500">O'quvchilar</flux:text>
                    <flux:heading size="xl">{{ $stats['total_students'] }}</flux:heading>
                </div>
            </div>
            <div class="mt-4 flex gap-4 text-sm">
                <span class="text-green-600">{{ $stats['active_students'] }} faol</span>
                <span class="text-yellow-600">{{ $stats['waiting_students'] }} kutmoqda</span>
            </div>
        </div>

        {{-- Groups --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                    <flux:icon.academic-cap class="size-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div>
                    <flux:text class="text-zinc-500">Guruhlar</flux:text>
                    <flux:heading size="xl">{{ $stats['active_groups'] }}</flux:heading>
                </div>
            </div>
            <div class="mt-4 flex gap-4 text-sm">
                <span class="text-green-600">{{ $stats['active_groups'] }} faol</span>
                <span class="text-yellow-600">{{ $stats['pending_groups'] }} kutmoqda</span>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg {{ $showNetIncome ? 'bg-emerald-100 dark:bg-emerald-900/30' : 'bg-green-100 dark:bg-green-900/30' }}">
                        <flux:icon.banknotes class="size-6 {{ $showNetIncome ? 'text-emerald-600 dark:text-emerald-400' : 'text-green-600 dark:text-green-400' }}" />
                    </div>
                    <div>
                        <flux:text class="text-zinc-500">{{ $showNetIncome ? 'Sof daromad' : 'Yalpi daromad' }}</flux:text>
                        <flux:heading size="xl" class="{{ $showNetIncome && $stats['current_net_income'] < 0 ? 'text-red-600' : '' }}">
                            {{ number_format($showNetIncome ? $stats['current_net_income'] : $stats['current_revenue'], 0, '', ' ') }}
                        </flux:heading>
                    </div>
                </div>
                <flux:checkbox wire:model.live="showNetIncome" label="Sof" />
            </div>
            @if ($showNetIncome)
                <div class="mt-3 grid grid-cols-2 gap-2 rounded-lg bg-zinc-100 p-2 text-xs dark:bg-zinc-700">
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Ustoz ulushi:</span>
                        <span class="text-red-500">-{{ number_format($stats['current_teacher_share'], 0, '', ' ') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-zinc-500">Harajatlar:</span>
                        <span class="text-red-500">-{{ number_format($stats['current_expenses'], 0, '', ' ') }}</span>
                    </div>
                </div>
            @endif
            <div class="mt-3 text-sm">
                @php $change = $showNetIncome ? $stats['net_income_change'] : $stats['revenue_change']; @endphp
                @if ($change > 0)
                    <span class="text-green-600">+{{ $change }}% o'tgan oydan</span>
                @elseif ($change < 0)
                    <span class="text-red-600">{{ $change }}% o'tgan oydan</span>
                @else
                    <span class="text-zinc-500">O'tgan oy bilan teng</span>
                @endif
            </div>
        </div>

        {{-- Leads --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/30">
                    <flux:icon.inbox class="size-6 text-orange-600 dark:text-orange-400" />
                </div>
                <div>
                    <flux:text class="text-zinc-500">Yangi leadlar</flux:text>
                    <flux:heading size="xl">{{ $stats['new_leads'] }}</flux:heading>
                </div>
            </div>
            <div class="mt-4 text-sm">
                <span class="text-zinc-500">Jami {{ $stats['total_leads'] }} ta aktiv lead</span>
            </div>
        </div>
    </div>

    {{-- Second Row --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Outstanding Payments Alert --}}
        @if ($stats['outstanding_count'] > 0)
            <div class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-800 dark:bg-red-900/20 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                            <flux:icon.exclamation-triangle class="size-6 text-red-600 dark:text-red-400" />
                        </div>
                        <div>
                            <flux:heading class="text-red-700 dark:text-red-300">To'lanmagan to'lovlar</flux:heading>
                            <flux:text class="text-red-600 dark:text-red-400">
                                {{ $stats['outstanding_count'] }} ta o'quvchi - jami {{ number_format($stats['outstanding_amount'], 0, '', ' ') }} so'm
                            </flux:text>
                        </div>
                    </div>
                    <flux:button variant="filled" color="red" :href="route('admin.reports', ['report' => 'outstanding'])" wire:navigate icon="arrow-right" icon-trailing>
                        Ko'rish
                    </flux:button>
                </div>
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Today's Groups --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading>Bugungi darslar</flux:heading>
                <flux:badge>{{ now()->format('l') }}</flux:badge>
            </div>

            @if ($this->todayGroups->count() > 0)
                <div class="space-y-3">
                    @foreach ($this->todayGroups as $group)
                        <a href="{{ route('admin.groups.show', $group) }}" wire:navigate class="block rounded-lg border border-zinc-100 p-3 transition-colors hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-700/50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium">{{ $group->name }}</div>
                                    <div class="text-sm text-zinc-500">{{ $group->course->code }} • {{ $group->teacher->name }}</div>
                                </div>
                                <div class="text-right text-sm">
                                    <div class="font-medium">{{ $group->start_time?->format('H:i') }}</div>
                                    <div class="text-zinc-500">{{ $group->room->name }}</div>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center gap-2 text-xs text-zinc-500">
                                <flux:icon.users class="size-3" />
                                <span>{{ $group->enrollments_count }} o'quvchi</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-zinc-500">
                    <flux:icon.calendar class="mx-auto size-8 text-zinc-400" />
                    <p class="mt-2">Bugun dars yo'q</p>
                </div>
            @endif
        </div>

        {{-- Recent Payments --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading>So'nggi to'lovlar</flux:heading>
                <flux:button variant="ghost" size="sm" :href="route('admin.reports', ['report' => 'financial'])" wire:navigate>
                    Barchasi
                </flux:button>
            </div>

            @if ($this->recentPayments->count() > 0)
                <div class="space-y-3">
                    @foreach ($this->recentPayments as $payment)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-100 p-3 dark:border-zinc-700">
                            <div>
                                <div class="font-medium">{{ $payment->enrollment->student->name }}</div>
                                <div class="text-sm text-zinc-500">{{ $payment->enrollment->group->course->code }} • {{ $payment->paid_at->format('d.m.Y') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-green-600">+{{ number_format($payment->amount, 0, '', ' ') }}</div>
                                <flux:badge size="sm" :color="$payment->method === 'cash' ? 'green' : ($payment->method === 'card' ? 'blue' : 'purple')">
                                    {{ $payment->method === 'cash' ? 'Naqd' : ($payment->method === 'card' ? 'Karta' : "O'tkazma") }}
                                </flux:badge>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-zinc-500">
                    <flux:icon.banknotes class="mx-auto size-8 text-zinc-400" />
                    <p class="mt-2">Hozircha to'lovlar yo'q</p>
                </div>
            @endif
        </div>

        {{-- Recent Leads --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading>Yangi leadlar</flux:heading>
                <flux:button variant="ghost" size="sm" :href="route('admin.leads')" wire:navigate>
                    Barchasi
                </flux:button>
            </div>

            @if ($this->recentLeads->count() > 0)
                <div class="space-y-3">
                    @foreach ($this->recentLeads as $lead)
                        <div class="flex items-center justify-between rounded-lg border border-zinc-100 p-3 dark:border-zinc-700">
                            <div>
                                <div class="font-medium">{{ $lead->name }}</div>
                                <div class="text-sm text-zinc-500">{{ $lead->phone }}</div>
                            </div>
                            <div class="text-right">
                                <flux:badge size="sm">{{ $lead->course?->code ?? '—' }}</flux:badge>
                                <div class="mt-1 text-xs text-zinc-500">{{ $lead->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-zinc-500">
                    <flux:icon.inbox class="mx-auto size-8 text-zinc-400" />
                    <p class="mt-2">Yangi leadlar yo'q</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Weekly Revenue Chart --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading class="mb-4">Haftalik daromad</flux:heading>
        @php
            $weeklyData = $this->weeklyRevenue;
            $maxAmount = max(array_column($weeklyData, 'amount')) ?: 1;
        @endphp
        <div class="flex h-48 items-end gap-2">
            @foreach ($weeklyData as $day)
                @php $height = $maxAmount > 0 ? ($day['amount'] / $maxAmount) * 100 : 0; @endphp
                <div class="flex flex-1 flex-col items-center">
                    <div class="mb-1 text-xs font-medium text-zinc-600">
                        @if ($day['amount'] > 0)
                            {{ number_format($day['amount'] / 1000, 0) }}k
                        @endif
                    </div>
                    <div class="w-full rounded-t bg-gradient-to-t from-blue-500 to-blue-400 transition-all hover:from-blue-600 hover:to-blue-500" style="height: {{ max($height, 2) }}%"></div>
                    <div class="mt-2 text-xs text-zinc-500">{{ $day['day'] }}</div>
                    <div class="text-xs text-zinc-400">{{ $day['date'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading class="mb-4">Tezkor harakatlar</flux:heading>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <flux:button variant="ghost" class="justify-start" :href="route('admin.students')" wire:navigate icon="user-plus">
                Yangi o'quvchi
            </flux:button>
            <flux:button variant="ghost" class="justify-start" :href="route('admin.groups')" wire:navigate icon="plus">
                Yangi guruh
            </flux:button>
            <flux:button variant="ghost" class="justify-start" :href="route('admin.attendance')" wire:navigate icon="clipboard-document-check">
                Davomat belgilash
            </flux:button>
            <flux:button variant="ghost" class="justify-start" :href="route('admin.reports')" wire:navigate icon="chart-bar">
                Hisobotlar
            </flux:button>
        </div>
    </div>
</div>
