@props(['partner'])

@if ($partner?->isVerified())
    <flux:badge color="teal" icon="check-badge" size="sm" {{ $attributes->merge(['class' => 'shrink-0']) }}>
        Terverifikasi
    </flux:badge>
@endif
