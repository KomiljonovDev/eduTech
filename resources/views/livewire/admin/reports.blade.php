<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Hisobotlar</flux:heading>
            <flux:subheading>O'quv markaz statistikasi va hisobotlari</flux:subheading>
        </div>
    </div>

    {{-- Report Type Tabs --}}
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex gap-4 overflow-x-auto">
            <button
                wire:click="$set('report', 'financial')"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $report === 'financial' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                Moliyaviy hisobot
            </button>
            <button
                wire:click="$set('report', 'outstanding')"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $report === 'outstanding' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                To'lanmagan to'lovlar
            </button>
            <button
                wire:click="$set('report', 'attendance')"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $report === 'attendance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                Davomat
            </button>
            <button
                wire:click="$set('report', 'students')"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $report === 'students' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                O'quvchilar
            </button>
            <button
                wire:click="$set('report', 'groups')"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $report === 'groups' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                Guruhlar
            </button>
            <button
                wire:click="$set('report', 'dropped')"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $report === 'dropped' ? 'border-red-500 text-red-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                Chiqarilganlar qarzdorligi
            </button>
        </nav>
    </div>

    {{-- Financial Report --}}
    @if ($report === 'financial')
        <div class="space-y-6">
            {{-- Period Selector --}}
            <div class="flex items-center gap-4">
                <flux:input type="month" wire:model.live="period" label="Davr" class="w-48" />
            </div>

            @php $data = $this->financialReport; @endphp

            {{-- Summary Cards --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                    <flux:text class="text-green-600 dark:text-green-400">Jami daromad</flux:text>
                    <flux:heading size="xl" class="text-green-700 dark:text-green-300">
                        {{ number_format($data['total'], 0, '', ' ') }}
                    </flux:heading>
                    <flux:text class="text-sm text-green-600">{{ $data['count'] }} ta to'lov</flux:text>
                </div>
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <flux:text class="text-blue-600 dark:text-blue-400">Maktab ulushi</flux:text>
                    <flux:heading size="xl" class="text-blue-700 dark:text-blue-300">
                        {{ number_format($data['school_total'], 0, '', ' ') }}
                    </flux:heading>
                </div>
                <div class="rounded-lg border border-purple-200 bg-purple-50 p-4 dark:border-purple-800 dark:bg-purple-900/20">
                    <flux:text class="text-purple-600 dark:text-purple-400">Ustozlar ulushi</flux:text>
                    <flux:heading size="xl" class="text-purple-700 dark:text-purple-300">
                        {{ number_format($data['teacher_total'], 0, '', ' ') }}
                    </flux:heading>
                </div>
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:text class="text-zinc-500">To'lov usullari</flux:text>
                    <div class="mt-2 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span>Naqd:</span>
                            <span class="font-medium">{{ number_format($data['by_method']['cash'] ?? 0, 0, '', ' ') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Karta:</span>
                            <span class="font-medium">{{ number_format($data['by_method']['card'] ?? 0, 0, '', ' ') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>O'tkazma:</span>
                            <span class="font-medium">{{ number_format($data['by_method']['transfer'] ?? 0, 0, '', ' ') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- By Teacher --}}
            @if ($data['by_teacher']->count() > 0)
                <div>
                    <flux:heading size="lg" class="mb-4">Ustozlar bo'yicha</flux:heading>
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <table class="w-full text-sm">
                            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">Ustoz</th>
                                    <th class="px-4 py-3 text-right font-medium">Jami to'lov</th>
                                    <th class="px-4 py-3 text-right font-medium">Foiz</th>
                                    <th class="px-4 py-3 text-right font-medium">Ustoz ulushi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($data['by_teacher'] as $teacher)
                                    <tr>
                                        <td class="px-4 py-3 font-medium">{{ $teacher['name'] }}</td>
                                        <td class="px-4 py-3 text-right">{{ number_format($teacher['total'], 0, '', ' ') }}</td>
                                        <td class="px-4 py-3 text-right text-zinc-500">{{ $teacher['percentage'] }}%</td>
                                        <td class="px-4 py-3 text-right font-medium text-purple-600">{{ number_format($teacher['share'], 0, '', ' ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- By Group --}}
            @if ($data['by_group']->count() > 0)
                <div>
                    <flux:heading size="lg" class="mb-4">Guruhlar bo'yicha</flux:heading>
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <table class="w-full text-sm">
                            <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left font-medium">Guruh</th>
                                    <th class="px-4 py-3 text-left font-medium">Kurs</th>
                                    <th class="px-4 py-3 text-right font-medium">To'lovlar soni</th>
                                    <th class="px-4 py-3 text-right font-medium">Jami</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($data['by_group'] as $group)
                                    <tr>
                                        <td class="px-4 py-3 font-medium">{{ $group['name'] }}</td>
                                        <td class="px-4 py-3"><flux:badge size="sm">{{ $group['course'] }}</flux:badge></td>
                                        <td class="px-4 py-3 text-right">{{ $group['count'] }}</td>
                                        <td class="px-4 py-3 text-right font-medium">{{ number_format($group['amount'], 0, '', ' ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Recent Payments --}}
            <div>
                <flux:heading size="lg" class="mb-4">So'nggi to'lovlar</flux:heading>
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="w-full text-sm">
                        <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Sana</th>
                                <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                                <th class="px-4 py-3 text-left font-medium">Guruh</th>
                                <th class="px-4 py-3 text-left font-medium">Usul</th>
                                <th class="px-4 py-3 text-right font-medium">Summa</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($data['payments']->take(20) as $payment)
                                <tr>
                                    <td class="px-4 py-3">{{ $payment->paid_at->format('d.m.Y') }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $payment->enrollment->student->name }}</td>
                                    <td class="px-4 py-3">{{ $payment->enrollment->group->name }}</td>
                                    <td class="px-4 py-3">
                                        <flux:badge size="sm" :color="$payment->method === 'cash' ? 'green' : ($payment->method === 'card' ? 'blue' : 'purple')">
                                            {{ $payment->method === 'cash' ? 'Naqd' : ($payment->method === 'card' ? 'Karta' : "O'tkazma") }}
                                        </flux:badge>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">{{ number_format($payment->amount, 0, '', ' ') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-zinc-500">
                                        Bu davrda to'lovlar yo'q
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Outstanding Payments --}}
    @if ($report === 'outstanding')
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <flux:input type="month" wire:model.live="period" label="Davr" class="w-48" />
            </div>

            @php $outstanding = $this->outstandingPayments; @endphp

            {{-- Summary --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <flux:text class="text-red-600 dark:text-red-400">Jami qarzdorlik</flux:text>
                    <flux:heading size="xl" class="text-red-700 dark:text-red-300">
                        {{ number_format($outstanding->sum('remaining'), 0, '', ' ') }}
                    </flux:heading>
                </div>
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <flux:text class="text-yellow-600 dark:text-yellow-400">To'lanmagan</flux:text>
                    <flux:heading size="xl" class="text-yellow-700 dark:text-yellow-300">
                        {{ $outstanding->where('status', 'unpaid')->count() }} ta
                    </flux:heading>
                </div>
                <div class="rounded-lg border border-orange-200 bg-orange-50 p-4 dark:border-orange-800 dark:bg-orange-900/20">
                    <flux:text class="text-orange-600 dark:text-orange-400">Qisman to'langan</flux:text>
                    <flux:heading size="xl" class="text-orange-700 dark:text-orange-300">
                        {{ $outstanding->where('status', 'partial')->count() }} ta
                    </flux:heading>
                </div>
            </div>

            {{-- Table --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                            <th class="px-4 py-3 text-left font-medium">Telefon</th>
                            <th class="px-4 py-3 text-left font-medium">Guruh</th>
                            <th class="px-4 py-3 text-right font-medium">Kerakli</th>
                            <th class="px-4 py-3 text-right font-medium">To'langan</th>
                            <th class="px-4 py-3 text-right font-medium">Qoldi</th>
                            <th class="px-4 py-3 text-center font-medium">Holat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($outstanding as $item)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3 font-medium">{{ $item['student']->name }}</td>
                                <td class="px-4 py-3 text-zinc-500">{{ $item['student']->phone }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.groups.show', $item['group']) }}" wire:navigate class="text-blue-600 hover:underline">
                                        {{ $item['group']->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-right">{{ number_format($item['required'], 0, '', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-green-600">{{ number_format($item['paid'], 0, '', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-red-600">{{ number_format($item['remaining'], 0, '', ' ') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <flux:badge size="sm" :color="$item['status'] === 'partial' ? 'yellow' : 'red'">
                                        {{ $item['status'] === 'partial' ? 'Qisman' : "To'lanmagan" }}
                                    </flux:badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-zinc-500">
                                    Barcha o'quvchilar to'lovni amalga oshirgan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Attendance Report --}}
    @if ($report === 'attendance')
        <div class="space-y-6">
            <div class="flex items-center gap-4">
                <flux:select wire:model.live="group_id" label="Guruh" class="w-64">
                    <flux:select.option value="">Barcha guruhlar</flux:select.option>
                    @foreach ($this->groups as $group)
                        <flux:select.option value="{{ $group->id }}">{{ $group->course->code }} - {{ $group->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            @php $attendance = $this->attendanceReport; @endphp

            {{-- Summary --}}
            <div class="grid gap-4 sm:grid-cols-4">
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:text class="text-zinc-500">Jami o'quvchilar</flux:text>
                    <flux:heading size="xl">{{ $attendance->count() }}</flux:heading>
                </div>
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                    <flux:text class="text-green-600">A'lo (80%+)</flux:text>
                    <flux:heading size="xl" class="text-green-700">{{ $attendance->where('percentage', '>=', 80)->count() }}</flux:heading>
                </div>
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <flux:text class="text-yellow-600">O'rta (60-79%)</flux:text>
                    <flux:heading size="xl" class="text-yellow-700">{{ $attendance->where('percentage', '>=', 60)->where('percentage', '<', 80)->count() }}</flux:heading>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <flux:text class="text-red-600">Past (60%-)</flux:text>
                    <flux:heading size="xl" class="text-red-700">{{ $attendance->where('percentage', '<', 60)->where('total', '>', 0)->count() }}</flux:heading>
                </div>
            </div>

            {{-- Table --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                            <th class="px-4 py-3 text-left font-medium">Guruh</th>
                            <th class="px-4 py-3 text-center font-medium">Jami dars</th>
                            <th class="px-4 py-3 text-center font-medium">Keldi</th>
                            <th class="px-4 py-3 text-center font-medium">Kelmadi</th>
                            <th class="px-4 py-3 text-center font-medium">Foiz</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($attendance as $item)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3 font-medium">{{ $item['student']->name }}</td>
                                <td class="px-4 py-3">{{ $item['group']->name }}</td>
                                <td class="px-4 py-3 text-center">{{ $item['total'] }}</td>
                                <td class="px-4 py-3 text-center text-green-600">{{ $item['present'] }}</td>
                                <td class="px-4 py-3 text-center text-red-600">{{ $item['absent'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($item['total'] > 0)
                                        <div class="inline-flex items-center gap-2">
                                            <div class="h-2 w-16 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                                <div class="h-full {{ $item['percentage'] >= 80 ? 'bg-green-500' : ($item['percentage'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $item['percentage'] }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium {{ $item['percentage'] >= 80 ? 'text-green-600' : ($item['percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $item['percentage'] }}%
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-zinc-500">
                                    Ma'lumot topilmadi
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Students Stats --}}
    @if ($report === 'students')
        <div class="space-y-6">
            @php $stats = $this->studentStats; @endphp

            {{-- Summary Cards --}}
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:text class="text-zinc-500">Jami o'quvchilar</flux:text>
                    <flux:heading size="xl">{{ $stats['total'] }}</flux:heading>
                </div>
                <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
                    <flux:text class="text-green-600">Faol o'qiyotganlar</flux:text>
                    <flux:heading size="xl" class="text-green-700">{{ $stats['active'] }}</flux:heading>
                </div>
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <flux:text class="text-yellow-600">Kutayotganlar</flux:text>
                    <flux:heading size="xl" class="text-yellow-700">{{ $stats['waiting'] }}</flux:heading>
                </div>
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <flux:text class="text-blue-600">KS tugatgan</flux:text>
                    <flux:heading size="xl" class="text-blue-700">{{ $stats['completed_ks'] }}</flux:heading>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                {{-- By Source --}}
                <div class="rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">Manba bo'yicha</flux:heading>
                    @php
                        $sourceLabels = [
                            'instagram' => 'Instagram',
                            'telegram' => 'Telegram',
                            'referral' => 'Tanish-bilish',
                            'walk_in' => "O'zi kelgan",
                            'grand' => 'Grand',
                            'other' => 'Boshqa',
                        ];
                        $sourceColors = [
                            'instagram' => 'bg-pink-500',
                            'telegram' => 'bg-blue-500',
                            'referral' => 'bg-green-500',
                            'walk_in' => 'bg-zinc-500',
                            'grand' => 'bg-yellow-500',
                            'other' => 'bg-purple-500',
                        ];
                    @endphp
                    <div class="space-y-3">
                        @foreach ($stats['by_source'] as $source => $count)
                            @php $percentage = $stats['total'] > 0 ? round(($count / $stats['total']) * 100) : 0; @endphp
                            <div>
                                <div class="mb-1 flex justify-between text-sm">
                                    <span>{{ $sourceLabels[$source] ?? $source }}</span>
                                    <span class="font-medium">{{ $count }} ({{ $percentage }}%)</span>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                    <div class="{{ $sourceColors[$source] ?? 'bg-zinc-500' }} h-full" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Enrollments by Month --}}
                <div class="rounded-lg border border-zinc-200 p-6 dark:border-zinc-700">
                    <flux:heading size="lg" class="mb-4">Oylik ro'yxatga olish</flux:heading>
                    @php $maxEnrollments = $stats['enrollments_by_month']->max() ?: 1; @endphp
                    <div class="flex h-48 items-end gap-2">
                        @foreach ($stats['enrollments_by_month'] as $month => $count)
                            @php $height = ($count / $maxEnrollments) * 100; @endphp
                            <div class="flex flex-1 flex-col items-center">
                                <div class="mb-1 text-xs font-medium">{{ $count }}</div>
                                <div class="w-full rounded-t bg-blue-500" style="height: {{ $height }}%"></div>
                                <div class="mt-2 text-xs text-zinc-500">{{ \Carbon\Carbon::parse($month.'-01')->format('M') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Groups Stats --}}
    @if ($report === 'groups')
        <div class="space-y-6">
            @php $groupStats = $this->groupStats; @endphp

            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Guruh</th>
                            <th class="px-4 py-3 text-left font-medium">Kurs</th>
                            <th class="px-4 py-3 text-left font-medium">Ustoz</th>
                            <th class="px-4 py-3 text-center font-medium">O'quvchilar</th>
                            <th class="px-4 py-3 text-center font-medium">To'lganlik</th>
                            <th class="px-4 py-3 text-center font-medium">Darslar</th>
                            <th class="px-4 py-3 text-center font-medium">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($groupStats as $item)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.groups.show', $item['group']) }}" wire:navigate class="font-medium text-blue-600 hover:underline">
                                        {{ $item['group']->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3"><flux:badge size="sm">{{ $item['group']->course->code }}</flux:badge></td>
                                <td class="px-4 py-3">{{ $item['group']->teacher->name }}</td>
                                <td class="px-4 py-3 text-center">{{ $item['active_students'] }} / {{ $item['capacity'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <div class="h-2 w-16 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                            <div class="h-full {{ $item['fill_rate'] >= 80 ? 'bg-green-500' : ($item['fill_rate'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $item['fill_rate'] }}%"></div>
                                        </div>
                                        <span class="text-xs">{{ $item['fill_rate'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">{{ $item['completed_lessons'] }} / {{ $item['total_lessons'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="inline-flex items-center gap-2">
                                        <div class="h-2 w-16 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                            <div class="h-full bg-blue-500" style="width: {{ $item['progress'] }}%"></div>
                                        </div>
                                        <span class="text-xs">{{ $item['progress'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-zinc-500">
                                    Faol guruhlar yo'q
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Dropped Students with Debt --}}
    @if ($report === 'dropped')
        <div class="space-y-6">
            @php $dropped = $this->droppedWithDebt; @endphp

            {{-- Summary --}}
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                    <flux:text class="text-red-600 dark:text-red-400">Jami qarzdorlik</flux:text>
                    <flux:heading size="xl" class="text-red-700 dark:text-red-300">
                        {{ number_format($dropped->sum('final_balance'), 0, '', ' ') }} so'm
                    </flux:heading>
                </div>
                <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                    <flux:text class="text-zinc-500">Chiqarilganlar soni</flux:text>
                    <flux:heading size="xl">{{ $dropped->count() }} ta</flux:heading>
                </div>
                <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
                    <flux:text class="text-yellow-600 dark:text-yellow-400">O'rtacha qarzdorlik</flux:text>
                    <flux:heading size="xl" class="text-yellow-700 dark:text-yellow-300">
                        {{ $dropped->count() > 0 ? number_format($dropped->avg('final_balance'), 0, '', ' ') : 0 }} so'm
                    </flux:heading>
                </div>
            </div>

            {{-- Table --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                            <th class="px-4 py-3 text-left font-medium">Telefon</th>
                            <th class="px-4 py-3 text-left font-medium">Guruh</th>
                            <th class="px-4 py-3 text-left font-medium">Chiqarilgan sana</th>
                            <th class="px-4 py-3 text-right font-medium">Qarzdorlik</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($dropped as $enrollment)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.students.show', $enrollment->student) }}" wire:navigate class="font-medium text-blue-600 hover:underline">
                                        {{ $enrollment->student->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-zinc-500">{{ $enrollment->student->phone }}</td>
                                <td class="px-4 py-3">
                                    <flux:badge size="sm">{{ $enrollment->group->course->code }}</flux:badge>
                                    {{ $enrollment->group->name }}
                                </td>
                                <td class="px-4 py-3 text-zinc-500">
                                    {{ $enrollment->dropped_at?->format('d.m.Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-red-600">
                                    {{ number_format($enrollment->final_balance, 0, '', ' ') }} so'm
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-zinc-500">
                                    Qarzdor chiqarilgan o'quvchilar yo'q
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
