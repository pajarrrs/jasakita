@props([
    'amount' => null,
    'unit' => null,
])

<span {{ $attributes->class('tabular-nums') }}>
    @if ($amount === null)
        <span class="text-zinc-500">Menyesuaikan</span>
    @else
        Rp {{ number_format((float) $amount, 0, ',', '.') }}@if ($unit)<span class="text-zinc-500"> {{ $unit }}</span>@endif
    @endif
</span>
