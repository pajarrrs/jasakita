@props([
    'icon' => 'inbox',
    'title',
    'description' => null,
])

<div {{ $attributes->class('flex flex-col items-center justify-center rounded-2xl border border-dashed border-zinc-300 bg-white px-6 py-12 text-center') }}>
    <span class="flex size-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400">
        <flux:icon :name="$icon" class="size-6" />
    </span>

    <p class="mt-4 text-sm font-medium text-zinc-900">{{ $title }}</p>

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-zinc-500">{{ $description }}</p>
    @endif

    @if (! $slot->isEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
