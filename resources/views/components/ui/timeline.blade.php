@props([
    'histories',
])

<ol class="flex flex-col">
    @foreach ($histories as $history)
        <li class="flex gap-3" wire:key="history-{{ $history->id }}">
            <div class="flex flex-col items-center">
                <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-teal-50 text-teal-600">
                    <flux:icon :name="$history->status->icon()" class="size-4" />
                </span>

                @unless ($loop->last)
                    <span class="w-px flex-1 bg-zinc-200"></span>
                @endunless
            </div>

            <div class="min-w-0 pb-4">
                <p class="text-sm font-medium text-zinc-900">{{ $history->status->label() }}</p>

                @if ($history->note)
                    <p class="text-sm text-zinc-500">{{ $history->note }}</p>
                @endif

                <p class="mt-0.5 text-xs text-zinc-400">
                    {{ $history->created_at->translatedFormat('d F Y, H:i') }}
                    @if ($history->changedBy)
                        &middot; {{ $history->changedBy->name }}
                    @endif
                </p>
            </div>
        </li>
    @endforeach
</ol>
