<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <flux:button variant="ghost" :href="route('admin.groups')" icon="arrow-left" wire:navigate />
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <flux:heading size="xl">{{ $group->name }}</flux:heading>
                <flux:badge :color="$group->status === 'active' ? 'green' : ($group->status === 'pending' ? 'yellow' : 'zinc')">
                    {{ $group->status === 'active' ? 'Faol' : ($group->status === 'pending' ? 'Kutilmoqda' : 'Tugagan') }}
                </flux:badge>
            </div>
            <flux:subheading>{{ $group->course->name }}</flux:subheading>
        </div>
    </div>

    {{-- Group Info Cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Ustoz</flux:text>
            <flux:heading>{{ $group->teacher->name }}</flux:heading>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Xona</flux:text>
            <flux:heading>{{ $group->room->name }}</flux:heading>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Jadval</flux:text>
            <flux:heading>{{ $group->days_label }}</flux:heading>
            <flux:text class="text-sm">{{ $group->start_time?->format('H:i') }} - {{ $group->end_time?->format('H:i') }}</flux:text>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">O'quvchilar</flux:text>
            <flux:heading>{{ $this->enrollments->count() }} / {{ $group->room->capacity }}</flux:heading>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <flux:text class="text-zinc-500">Narx</flux:text>
            <flux:heading>{{ number_format($group->course->monthly_price, 0, '', ' ') }}</flux:heading>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-zinc-200 dark:border-zinc-700">
        <nav class="-mb-px flex gap-4">
            <button
                wire:click="$set('activeTab', 'students')"
                class="border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $activeTab === 'students' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                O'quvchilar
            </button>
            <button
                wire:click="$set('activeTab', 'attendance')"
                class="border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $activeTab === 'attendance' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                Davomat
            </button>
            <button
                wire:click="$set('activeTab', 'payments')"
                class="border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $activeTab === 'payments' ? 'border-blue-500 text-blue-600' : 'border-transparent text-zinc-500 hover:border-zinc-300 hover:text-zinc-700' }}"
            >
                To'lovlar
            </button>
        </nav>
    </div>

    {{-- Students Tab --}}
    @if ($activeTab === 'students')
        <div class="space-y-4">
            {{-- Period selector --}}
            <div class="flex items-center gap-4">
                <flux:input type="month" wire:model.live="period" label="Davr" class="w-48" />
                @php $stats = $this->getTotalStats(); @endphp
                <div class="flex gap-4 text-sm">
                    <div>
                        <span class="text-zinc-500">Jami:</span>
                        <span class="font-medium">{{ number_format($stats['required'] - $stats['discount'], 0, '', ' ') }}</span>
                    </div>
                    <div>
                        <span class="text-green-600">To'langan:</span>
                        <span class="font-medium text-green-600">{{ number_format($stats['paid'], 0, '', ' ') }}</span>
                    </div>
                    <div>
                        <span class="text-red-600">Qoldi:</span>
                        <span class="font-medium text-red-600">{{ number_format($stats['remaining'], 0, '', ' ') }}</span>
                    </div>
                </div>
            </div>

            {{-- Students Table --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">#</th>
                            <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                            <th class="px-4 py-3 text-left font-medium">Telefon</th>
                            <th class="px-4 py-3 text-center font-medium">Davomat</th>
                            <th class="px-4 py-3 text-right font-medium">To'lov ({{ \Carbon\Carbon::parse($period.'-01')->format('M Y') }})</th>
                            <th class="px-4 py-3 text-right font-medium">Amallar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->enrollments as $index => $enrollment)
                            @php
                                $paymentStatus = $this->getPaymentStatusForPeriod($enrollment);
                                $attendanceStats = $this->getAttendanceStats($enrollment);
                            @endphp
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3 text-zinc-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $enrollment->student->name }}</div>
                                    @if ($enrollment->student->activeDiscounts->count() > 0)
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach ($enrollment->student->activeDiscounts as $discount)
                                                <flux:badge size="sm" color="purple">{{ $discount->formatted_value }}</flux:badge>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-zinc-500">{{ $enrollment->student->phone }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($attendanceStats['total'] > 0)
                                        <div class="inline-flex items-center gap-2">
                                            <div class="h-2 w-12 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                                <div class="h-full bg-green-500" style="width: {{ $attendanceStats['percentage'] }}%"></div>
                                            </div>
                                            <span class="text-xs">{{ $attendanceStats['present'] }}/{{ $attendanceStats['total'] }}</span>
                                        </div>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($paymentStatus['discount'] > 0)
                                            <span class="text-xs text-zinc-400 line-through">{{ number_format($paymentStatus['course_price'], 0, '', ' ') }}</span>
                                        @endif
                                        <span class="font-medium">{{ number_format($paymentStatus['required'], 0, '', ' ') }}</span>
                                        <flux:badge size="sm" :color="$paymentStatus['status'] === 'paid' ? 'green' : ($paymentStatus['status'] === 'partial' ? 'yellow' : 'red')">
                                            @if ($paymentStatus['status'] === 'paid')
                                                To'langan
                                            @elseif ($paymentStatus['status'] === 'partial')
                                                {{ number_format($paymentStatus['paid'], 0, '', ' ') }}
                                            @else
                                                To'lanmagan
                                            @endif
                                        </flux:badge>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($paymentStatus['status'] !== 'paid')
                                        <flux:button variant="primary" size="sm" wire:click="openPaymentModal({{ $enrollment->id }})" icon="banknotes">
                                            To'lov
                                        </flux:button>
                                    @else
                                        <flux:button variant="ghost" size="sm" wire:click="openPaymentModal({{ $enrollment->id }})" icon="plus">
                                            Qo'shimcha
                                        </flux:button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-zinc-500">
                                    Bu guruhda faol o'quvchilar yo'q
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Attendance Tab --}}
    @if ($activeTab === 'attendance')
        <div class="space-y-4">
            {{-- Lesson selector --}}
            <div class="flex flex-wrap items-end gap-4">
                <flux:select wire:model.live="lesson_number" label="Dars raqami" class="w-48">
                    @foreach ($this->lessonDates as $num => $date)
                        <flux:select.option value="{{ $num }}">
                            {{ $num }}-dars {{ $date ? "($date)" : '' }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="lesson_date" type="date" label="Sana" class="w-48" />

                <div class="flex gap-2">
                    <flux:button wire:click="markAllPresent" variant="ghost" size="sm" icon="check-circle">
                        Hammasi keldi
                    </flux:button>
                    <flux:button wire:click="markAllAbsent" variant="ghost" size="sm" icon="x-circle">
                        Hammasi kelmadi
                    </flux:button>
                </div>

                <flux:button wire:click="saveAttendance" variant="primary" icon="check">
                    Saqlash
                </flux:button>
            </div>

            {{-- Attendance Table --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">#</th>
                            <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                            <th class="px-4 py-3 text-center font-medium">Statistika</th>
                            <th class="px-4 py-3 text-center font-medium">{{ $lesson_number }}-dars</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @forelse ($this->enrollments as $index => $enrollment)
                            @php $stats = $this->getAttendanceStats($enrollment); @endphp
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50" wire:key="att-{{ $enrollment->id }}">
                                <td class="px-4 py-3 text-zinc-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 font-medium">{{ $enrollment->student->name }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($stats['total'] > 0)
                                        <div class="inline-flex items-center gap-2">
                                            <div class="h-2 w-16 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                                                <div class="h-full bg-green-500" style="width: {{ $stats['percentage'] }}%"></div>
                                            </div>
                                            <span class="text-xs {{ $stats['percentage'] >= 80 ? 'text-green-600' : ($stats['percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $stats['present'] }}/{{ $stats['total'] }} ({{ $stats['percentage'] }}%)
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        wire:click="toggleAttendance({{ $enrollment->id }})"
                                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg transition-colors {{ ($attendance[$enrollment->id] ?? false) ? 'bg-green-100 text-green-600 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400' }}"
                                    >
                                        @if ($attendance[$enrollment->id] ?? false)
                                            <flux:icon.check class="size-5" />
                                        @else
                                            <flux:icon.x-mark class="size-5" />
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-zinc-500">
                                    Bu guruhda faol o'quvchilar yo'q
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Summary --}}
            @if ($this->enrollments->count() > 0)
                <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="flex gap-6">
                        <div>
                            <flux:text class="text-zinc-500">Jami</flux:text>
                            <flux:heading size="lg">{{ $this->enrollments->count() }}</flux:heading>
                        </div>
                        <div>
                            <flux:text class="text-green-600">Keldi</flux:text>
                            <flux:heading size="lg" class="text-green-600">{{ collect($attendance)->filter()->count() }}</flux:heading>
                        </div>
                        <div>
                            <flux:text class="text-red-600">Kelmadi</flux:text>
                            <flux:heading size="lg" class="text-red-600">{{ $this->enrollments->count() - collect($attendance)->filter()->count() }}</flux:heading>
                        </div>
                    </div>
                    <flux:button wire:click="saveAttendance" variant="primary" icon="check">
                        Davomatni saqlash
                    </flux:button>
                </div>
            @endif
        </div>
    @endif

    {{-- Payments Tab --}}
    @if ($activeTab === 'payments')
        <div class="space-y-4">
            {{-- Payment History --}}
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-sm">
                    <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Sana</th>
                            <th class="px-4 py-3 text-left font-medium">O'quvchi</th>
                            <th class="px-4 py-3 text-left font-medium">Davr</th>
                            <th class="px-4 py-3 text-left font-medium">Usul</th>
                            <th class="px-4 py-3 text-right font-medium">Summa</th>
                            <th class="px-4 py-3 text-right font-medium">Ustoz ulushi</th>
                            <th class="px-4 py-3 text-right font-medium">Maktab ulushi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @php
                            $payments = \App\Models\Payment::whereHas('enrollment', fn($q) => $q->where('group_id', $group->id))
                                ->with('enrollment.student')
                                ->latest('paid_at')
                                ->get();
                        @endphp
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3">{{ $payment->paid_at->format('d.m.Y') }}</td>
                                <td class="px-4 py-3 font-medium">{{ $payment->enrollment->student->name }}</td>
                                <td class="px-4 py-3">
                                    @if ($payment->period)
                                        {{ \Carbon\Carbon::parse($payment->period.'-01')->format('F Y') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <flux:badge size="sm" :color="$payment->method === 'cash' ? 'green' : ($payment->method === 'card' ? 'blue' : 'purple')">
                                        {{ $payment->method === 'cash' ? 'Naqd' : ($payment->method === 'card' ? 'Karta' : "O'tkazma") }}
                                    </flux:badge>
                                </td>
                                <td class="px-4 py-3 text-right font-medium">{{ number_format($payment->amount, 0, '', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-zinc-500">{{ number_format($payment->teacher_share, 0, '', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-zinc-500">{{ number_format($payment->school_share, 0, '', ' ') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-zinc-500">
                                    Hozircha to'lovlar yo'q
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($payments->count() > 0)
                        <tfoot class="border-t border-zinc-300 bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800">
                            <tr>
                                <td colspan="4" class="px-4 py-3 font-medium">Jami</td>
                                <td class="px-4 py-3 text-right font-bold">{{ number_format($payments->sum('amount'), 0, '', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-zinc-600">{{ number_format($payments->sum('teacher_share'), 0, '', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-zinc-600">{{ number_format($payments->sum('school_share'), 0, '', ' ') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif

    {{-- Payment Modal --}}
    <flux:modal wire:model="showPaymentModal" class="max-w-md">
        <form wire:submit="collectPayment" class="space-y-6">
            <div>
                <flux:heading size="lg">To'lov qabul qilish</flux:heading>
                @if ($this->paymentEnrollment)
                    <flux:subheading>{{ $this->paymentEnrollment->student->name }}</flux:subheading>
                @endif
            </div>

            @if ($this->paymentEnrollment)
                @php $status = $this->getPaymentStatusForPeriod($this->paymentEnrollment); @endphp
                <div class="rounded-lg bg-zinc-100 p-4 dark:bg-zinc-800">
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-zinc-500">Kurs narxi:</span>
                            <span class="font-medium">{{ number_format($status['course_price'], 0, '', ' ') }}</span>
                        </div>
                        @if ($status['discount'] > 0)
                            <div>
                                <span class="text-zinc-500">Chegirma:</span>
                                <span class="font-medium text-green-600">-{{ number_format($status['discount'], 0, '', ' ') }}</span>
                            </div>
                        @endif
                        <div>
                            <span class="text-zinc-500">To'langan:</span>
                            <span class="font-medium">{{ number_format($status['paid'], 0, '', ' ') }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500">Qoldi:</span>
                            <span class="font-bold text-red-600">{{ number_format($status['remaining'], 0, '', ' ') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <flux:input wire:model="period" type="month" label="Davr" required />

            <flux:input wire:model="amount" type="number" label="Summa" min="1000" step="1000" suffix="so'm" required />

            <flux:select wire:model="method" label="To'lov usuli">
                <flux:select.option value="cash">Naqd pul</flux:select.option>
                <flux:select.option value="card">Plastik karta</flux:select.option>
                <flux:select.option value="transfer">Bank o'tkazmasi</flux:select.option>
            </flux:select>

            <flux:textarea wire:model="payment_notes" label="Izoh" placeholder="Qo'shimcha ma'lumot..." rows="2" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" icon="banknotes">
                    To'lovni qabul qilish
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Toast notifications --}}
    <div
        x-data="{ show: false, message: '' }"
        @attendance-saved.window="show = true; message = 'Davomat saqlandi!'; setTimeout(() => show = false, 3000)"
        @payment-collected.window="show = true; message = 'Tolov qabul qilindi!'; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 rounded-lg bg-green-600 px-4 py-2 text-white shadow-lg"
    >
        <span x-text="message"></span>
    </div>
</div>
