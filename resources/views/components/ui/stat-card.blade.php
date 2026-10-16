@props([
    'label',
    'value',
    'icon' => null,
    'hint' => null,
])

<flux:card class="flex items-start gap-4">
    @if ($icon)
        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
            <flux:icon :name="$icon" class="size-5" />
        </span>
    @endif

    <div class="min-w-0">
        <p class="text-sm text-zinc-500">{{ $label }}</p>
        <p class="mt-1 text-2xl font-semibold text-zinc-900">{{ $value }}</p>
        @if ($hint)
            <p class="mt-1 text-xs text-zinc-400">{{ $hint }}</p>
        @endif
    </div>
</flux:card>
