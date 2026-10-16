@props([
    'rating' => null,
    'count' => null,
    'size' => 'sm',
])

@php
    $value = $rating === null ? 0.0 : (float) $rating;
    $iconSize = match ($size) {
        'lg' => 'size-5',
        default => 'size-4',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-1.5']) }}>
    <div class="flex items-center gap-0.5">
        @foreach (range(1, 5) as $star)
            @php $isFilled = $value >= $star - 0.5; @endphp

            <flux:icon
                name="star"
                :variant="$isFilled ? 'solid' : 'outline'"
                class="{{ $iconSize }} {{ $isFilled ? 'text-amber-400' : 'text-zinc-300' }}"
            />
        @endforeach
    </div>

    @if ($value > 0)
        <span class="text-sm font-medium text-zinc-900">{{ number_format($value, 1, ',', '.') }}</span>
    @endif

    @if ($count !== null)
        <span class="text-xs text-zinc-500">({{ $count }} ulasan)</span>
    @endif
</div>
