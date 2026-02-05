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
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Ism</th>
                        <th class="px-4 py-3 text-left font-medium">Telefon</th>
                        <th class="px-4 py-3 text-left font-medium">Oylik turi</th>
                        <th class="px-4 py-3 text-left font-medium">Bu oy</th>
                        <th class="px-4 py-3 text-left font-medium">Qarzdorlik</th>
                        <th class="px-4 py-3 text-left font-medium">Holat</th>
                        <th class="px-4 py-3 text-left font-medium">Akkaunt</th>
                        <th class="px-4 py-3 text-right font-medium">Amallar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($teachers as $teacher)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-4 py-3 font-medium">{{ $teacher->name }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $teacher->phone ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($teacher->salary_type === 'fixed')
                                    <flux:badge color="purple" size="sm">Belgilangan</flux:badge>
                                @elseif ($teacher->salary_type === 'hybrid')
                                    <flux:badge color="amber" size="sm">Aralash</flux:badge>
                                @else
                                    <flux:badge color="blue" size="sm">{{ $teacher->payment_percentage }}%</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    wire:click="openSalaryModal({{ $teacher->id }})"
                                    class="text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400"
                                >
                                    {{ number_format($teacher->currentMonthEarnings, 0, '', ' ') }} so'm
                                </button>
                            </td>
                            <td class="px-4 py-3">
                                @if ($teacher->currentMonthDebt > 0)
                                    <span class="font-medium text-red-600 dark:text-red-400">
                                        {{ number_format($teacher->currentMonthDebt, 0, '', ' ') }} so'm
                                    </span>
                                @else
                                    <span class="font-medium text-green-600 dark:text-green-400">
                                        To'langan
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($teacher->is_active)
                                    <flux:badge color="green" size="sm">Faol</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Nofaol</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($teacher->user)
                                    <div class="flex items-center gap-2">
                                        <flux:badge color="blue" size="sm">
                                            <flux:icon.check class="size-3" />
                                            {{ $teacher->user->email }}
                                        </flux:badge>
                                    </div>
                                @else
                                    <flux:badge color="zinc" size="sm">Yo'q</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <flux:button variant="ghost" size="sm" wire:click="openPaymentModal({{ $teacher->id }})" icon="banknotes" title="To'lov qilish" class="text-green-600" />
                                @if (!$teacher->user)
                                    <flux:button variant="ghost" size="sm" wire:click="openAccountModal({{ $teacher->id }})" icon="user-plus" title="Akkaunt yaratish" />
                                @else
                                    <flux:button variant="ghost" size="sm" wire:click="unlinkAccount({{ $teacher->id }})" wire:confirm="Bu akkauntni o'chirmoqchimisiz? Ustoz tizimga kira olmay qoladi." icon="user-minus" title="Akkauntni o'chirish" class="text-red-600" />
                                @endif
                                <flux:button variant="ghost" size="sm" wire:click="edit({{ $teacher->id }})" icon="pencil" />
                                <flux:button variant="ghost" size="sm" wire:click="delete({{ $teacher->id }})" wire:confirm="Rostdan ham o'chirmoqchimisiz?" icon="trash" class="text-red-600 hover:text-red-700" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-zinc-500">
                                Hozircha ustozlar yo'q
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Ustozni tahrirlash' : 'Yangi ustoz' }}</flux:heading>
                <flux:subheading>Ustoz ma'lumotlarini kiriting</flux:subheading>
            </div>

            <flux:input wire:model="name" label="Ism" placeholder="Ustoz ismi" required />

            <flux:input wire:model="phone" label="Telefon" placeholder="+998 90 123 45 67" />

            <flux:select wire:model.live="salary_type" label="Oylik turi">
                @foreach ($salaryTypes as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            @if ($salary_type === 'fixed' || $salary_type === 'hybrid')
                <flux:input wire:model="fixed_salary" label="Belgilangan oylik" type="number" min="0" suffix="so'm" required />
            @endif

            @if ($salary_type === 'percent' || $salary_type === 'hybrid')
                <flux:input wire:model="payment_percentage" label="To'lov foizi" type="number" min="0" max="100" suffix="%" required />
            @endif

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

    {{-- Account Modal --}}
    <flux:modal wire:model="showAccountModal" class="max-w-lg">
        <form wire:submit="createAccount" class="space-y-6">
            <div>
                <flux:heading size="lg">Akkaunt yaratish</flux:heading>
                <flux:subheading>Ustoz uchun tizimga kirish akkauntini yarating</flux:subheading>
            </div>

            <flux:callout color="blue" icon="information-circle">
                <flux:callout.text>
                    Akkaunt yaratilgach ustoz o'z email va paroli bilan tizimga kirib, o'z guruhlarini, jadvalini va davomatni ko'ra oladi.
                </flux:callout.text>
            </flux:callout>

            <flux:input wire:model="email" label="Email" type="email" placeholder="ustoz@example.com" required />

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

    {{-- Payment Modal --}}
    <flux:modal wire:model="showPaymentModal" class="max-w-lg">
        <form wire:submit="makePayment" class="space-y-6">
            <div>
                <flux:heading size="lg">To'lov qilish</flux:heading>
                <flux:subheading>Ustozga oylik to'lash</flux:subheading>
            </div>

            <flux:input wire:model="paymentAmount" label="Summa" type="number" min="0" suffix="so'm" placeholder="1000000" required />

            <flux:input wire:model="paymentDate" label="Sana" type="date" required />

            <flux:input wire:model="paymentPeriod" label="Davr (yil-oy)" type="month" required />

            <flux:select wire:model="paymentMethod" label="To'lov usuli">
                @foreach ($paymentMethods as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:textarea wire:model="paymentNotes" label="Izoh" placeholder="Qo'shimcha ma'lumot..." rows="2" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Bekor qilish</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" type="submit" icon="banknotes">
                    To'lov qilish
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Salary Detail Modal --}}
    <flux:modal wire:model="showSalaryModal" class="max-w-4xl">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Oylik ma'lumotlari</flux:heading>
                <flux:subheading>{{ $this->salaryDetails['teacher']->name ?? '' }} - {{ $salaryPeriod }}</flux:subheading>
            </div>

            <flux:input wire:model.live="salaryPeriod" label="Davr" type="month" />

            @if (!empty($this->salaryDetails))
                {{-- Stats Cards --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <div class="text-sm text-zinc-500 dark:text-zinc-400">Hisoblangan</div>
                        <div class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">
                            {{ number_format($this->salaryDetails['earnings'], 0, '', ' ') }} so'm
                        </div>
                        @if ($this->salaryDetails['teacher']->salary_type === 'fixed')
                            <div class="mt-1 text-xs text-zinc-500">Belgilangan oylik</div>
                        @elseif ($this->salaryDetails['teacher']->salary_type === 'hybrid')
                            <div class="mt-1 text-xs text-zinc-500">
                                {{ number_format($this->salaryDetails['teacher']->fixed_salary, 0, '', ' ') }} (belgilangan) +
                                {{ $this->salaryDetails['teacher']->payment_percentage }}% (foiz)
                            </div>
                        @else
                            <div class="mt-1 text-xs text-zinc-500">{{ $this->salaryDetails['teacher']->payment_percentage }}% foiz</div>
                        @endif
                    </div>

                    <div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-900/20">
                        <div class="text-sm text-green-600 dark:text-green-400">To'langan</div>
                        <div class="mt-1 text-2xl font-bold text-green-700 dark:text-green-300">
                            {{ number_format($this->salaryDetails['paid'], 0, '', ' ') }} so'm
                        </div>
                    </div>

                    <div class="rounded-lg border {{ $this->salaryDetails['debt'] > 0 ? 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20' : 'border-green-200 bg-green-50 dark:border-green-900 dark:bg-green-900/20' }} p-4">
                        <div class="text-sm {{ $this->salaryDetails['debt'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            Qarzdorlik
                        </div>
                        <div class="mt-1 text-2xl font-bold {{ $this->salaryDetails['debt'] > 0 ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300' }}">
                            @if ($this->salaryDetails['debt'] > 0)
                                {{ number_format($this->salaryDetails['debt'], 0, '', ' ') }} so'm
                            @else
                                To'langan
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Student Payments --}}
                @if ($this->salaryDetails['studentPayments']->count() > 0)
                    <div>
                        <flux:heading size="sm" class="mb-3">O'quvchi to'lovlari</flux:heading>
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <table class="w-full text-sm">
                                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium">O'quvchi</th>
                                        <th class="px-4 py-2 text-left font-medium">Guruh</th>
                                        <th class="px-4 py-2 text-right font-medium">To'lov</th>
                                        <th class="px-4 py-2 text-right font-medium">Ustoz ulushi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach ($this->salaryDetails['studentPayments'] as $payment)
                                        <tr>
                                            <td class="px-4 py-2">{{ $payment->enrollment->student->name }}</td>
                                            <td class="px-4 py-2 text-zinc-500">{{ $payment->enrollment->group->name }}</td>
                                            <td class="px-4 py-2 text-right">{{ number_format($payment->amount, 0, '', ' ') }}</td>
                                            <td class="px-4 py-2 text-right font-medium text-green-600">{{ number_format($payment->teacher_share, 0, '', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="border-t border-zinc-300 bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800">
                                    <tr>
                                        <td colspan="2" class="px-4 py-2 font-medium">Jami</td>
                                        <td class="px-4 py-2 text-right font-bold">{{ number_format($this->salaryDetails['studentPayments']->sum('amount'), 0, '', ' ') }}</td>
                                        <td class="px-4 py-2 text-right font-bold text-green-600">{{ number_format($this->salaryDetails['studentPayments']->sum('teacher_share'), 0, '', ' ') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- Payment History --}}
                @if ($this->salaryDetails['paymentHistory']->count() > 0)
                    <div>
                        <flux:heading size="sm" class="mb-3">To'lov tarixi</flux:heading>
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <table class="w-full text-sm">
                                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium">Sana</th>
                                        <th class="px-4 py-2 text-left font-medium">Summa</th>
                                        <th class="px-4 py-2 text-left font-medium">Usul</th>
                                        <th class="px-4 py-2 text-left font-medium">Izoh</th>
                                        <th class="px-4 py-2 text-left font-medium">Tomonidan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach ($this->salaryDetails['paymentHistory'] as $payment)
                                        <tr>
                                            <td class="px-4 py-2">{{ $payment->paid_at->format('d.m.Y') }}</td>
                                            <td class="px-4 py-2 font-medium text-green-600">{{ number_format($payment->amount, 0, '', ' ') }} so'm</td>
                                            <td class="px-4 py-2">
                                                <flux:badge size="sm" color="zinc">
                                                    {{ \App\Models\TeacherPayment::methods()[$payment->method] ?? $payment->method }}
                                                </flux:badge>
                                            </td>
                                            <td class="px-4 py-2 text-zinc-500">{{ $payment->notes ?? '-' }}</td>
                                            <td class="px-4 py-2 text-zinc-500">{{ $payment->user->name ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost">Yopish</flux:button>
                </flux:modal.close>
            </div>
        </div>
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

    {{-- Payment Success Toast --}}
    <div
        x-data="{ show: false }"
        x-on:payment-created.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 max-w-md rounded-lg bg-green-600 p-4 text-white shadow-lg"
    >
        <div class="flex items-center gap-3">
            <flux:icon.check-circle class="size-6 flex-shrink-0" />
            <div class="font-medium">To'lov muvaffaqiyatli amalga oshirildi!</div>
        </div>
    </div>
</div>
