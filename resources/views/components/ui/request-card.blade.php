@props([
    'request',
])

<a
    href="{{ route('customer.requests.show', $request) }}"
    wire:navigate
    wire:key="request-{{ $request->id }}"
    {{ $attributes->class('group block') }}
>
    <flux:card class="flex flex-col gap-3 transition group-hover:border-teal-300">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="truncate text-sm font-medium text-zinc-900">{{ $request->title }}</p>
                <p class="truncate text-xs text-zinc-500">
                    {{ $request->service->name }} &middot; {{ $request->created_at->translatedFormat('d F Y') }}
                </p>

                @if (($request->unread_messages_count ?? 0) > 0)
                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-teal-600 px-2 py-0.5 text-[11px] font-semibold text-white">
                        <flux:icon name="chat-bubble-left-right" class="size-3" />
                        {{ $request->unread_messages_count }} pesan baru
                    </span>
                @endif
            </div>

            <x-ui.status-pill :status="$request->status" class="shrink-0" />
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500">
            <span class="flex items-center gap-1">
                <flux:icon name="tag" class="size-3.5" />
                {{ $request->mode->label() }}
            </span>

            <span class="flex items-center gap-1">
                <flux:icon name="chat-bubble-left-right" class="size-3.5" />
                {{ $request->offers_count }} penawaran
            </span>

            @if ($request->partner)
                <span class="flex items-center gap-1">
                    <flux:icon name="user" class="size-3.5" />
                    {{ $request->partner->user->name }}
                </span>
            @endif

            @if ($request->scheduled_at)
                <span class="flex items-center gap-1">
                    <flux:icon name="calendar-days" class="size-3.5" />
                    {{ $request->scheduled_at->translatedFormat('d F Y, H:i') }}
                </span>
            @endif
        </div>
    </flux:card>
</a>
