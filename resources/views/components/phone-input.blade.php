@props([
    'label' => null,
    'placeholder' => '(90) 123-45-67',
    'required' => false,
])

<div
    x-data="{
        value: @entangle($attributes->wire('model')),
        formatted: '',
        init() {
            this.formatFromValue();
            this.$watch('value', () => this.formatFromValue());
        },
        formatFromValue() {
            if (!this.value) {
                this.formatted = '';
                return;
            }
            // Remove +998 prefix if exists and non-digits
            let digits = this.value.replace(/\D/g, '');
            if (digits.startsWith('998')) {
                digits = digits.slice(3);
            }
            this.formatted = this.formatDigits(digits);
        },
        formatDigits(digits) {
            digits = digits.slice(0, 9);
            let result = '';
            if (digits.length > 0) {
                result = '(' + digits.slice(0, 2);
            }
            if (digits.length >= 2) {
                result += ') ';
            }
            if (digits.length > 2) {
                result += digits.slice(2, 5);
            }
            if (digits.length > 5) {
                result += '-' + digits.slice(5, 7);
            }
            if (digits.length > 7) {
                result += '-' + digits.slice(7, 9);
            }
            return result;
        },
        onInput(e) {
            let digits = e.target.value.replace(/\D/g, '').slice(0, 9);
            this.formatted = this.formatDigits(digits);
            this.value = digits.length > 0 ? '+998' + digits : '';
        }
    }"
    class="w-full"
>
    @if ($label)
        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="flex">
        <span class="inline-flex items-center rounded-l-lg border border-r-0 border-zinc-300 bg-zinc-100 px-3 text-sm text-zinc-600 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-300">
            +998
        </span>
        <input
            type="text"
            x-model="formatted"
            @input="onInput"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->except(['wire:model', 'wire:model.live', 'wire:model.blur', 'wire:model.lazy'])->merge([
                'class' => 'block w-full rounded-r-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-zinc-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:placeholder:text-zinc-500 dark:focus:border-blue-500'
            ]) }}
        />
    </div>
</div>
