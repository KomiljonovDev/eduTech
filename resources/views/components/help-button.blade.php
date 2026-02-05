@props(['section' => null, 'title' => "Yordam"])

@php
    $helpUrl = $section ? route('help', ['section' => $section]) : route('help');
@endphp

<flux:button
    variant="ghost"
    size="sm"
    icon="question-mark-circle"
    :href="$helpUrl"
    wire:navigate
    {{ $attributes }}
>
    {{ $title }}
</flux:button>
