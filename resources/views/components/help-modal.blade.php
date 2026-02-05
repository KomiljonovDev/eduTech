@props(['id' => 'help-modal', 'title' => 'Yordam'])

<div x-data="{ open: false }">
    <flux:button
        variant="ghost"
        size="sm"
        icon="question-mark-circle"
        x-on:click="open = true"
        {{ $attributes }}
    >
        {{ __('Yordam') }}
    </flux:button>

    <flux:modal x-model="open" :name="$id" class="max-w-2xl">
        <div class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <flux:icon name="question-mark-circle" class="size-8 text-blue-500" />
                <flux:heading size="lg">{{ $title }}</flux:heading>
            </div>

            <div class="prose prose-zinc dark:prose-invert max-w-none">
                {{ $slot }}
            </div>

            <div class="mt-6 flex justify-end">
                <flux:button variant="primary" x-on:click="open = false">
                    Tushundim
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
