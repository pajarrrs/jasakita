<div class="flex flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('partner.jobs.index')" wire:navigate>
            Job Saya
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>#{{ $serviceRequest->id }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <flux:heading size="xl">{{ $serviceRequest->title }}</flux:heading>
            <flux:text class="mt-1">
                {{ $serviceRequest->service->name }} &middot; {{ $serviceRequest->mode->label() }}
            </flux:text>
        </div>

        <x-ui.status-pill :status="$serviceRequest->status" />
    </div>

    <flux:card class="flex items-center gap-4">
        <flux:avatar :name="$serviceRequest->customer->name" color="zinc" />

        <div class="min-w-0">
            <p class="truncate font-medium text-zinc-900">{{ $serviceRequest->customer->name }}</p>
            <p class="truncate text-xs text-zinc-500">
                Pelanggan &middot; bergabung {{ $serviceRequest->customer->created_at->translatedFormat('F Y') }}
            </p>
        </div>

        @if ($serviceRequest->customer->phone)
            <flux:button
                :href="'https://wa.me/'.$serviceRequest->customer->phone"
                target="_blank"
                size="sm"
                variant="filled"
                icon="chat-bubble-left-right"
                class="ms-auto shrink-0"
            >
                WhatsApp
            </flux:button>
        @endif
    </flux:card>

    <livewire:shared.chat :service-request="$serviceRequest" wire:key="chat-{{ $serviceRequest->id }}" />

    <flux:card class="flex flex-col gap-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs text-zinc-500">Jadwal</p>
                <p class="text-sm font-medium text-zinc-900">
                    {{ $serviceRequest->scheduled_at?->translatedFormat('d F Y, H:i') ?? 'Belum ditentukan' }}
                </p>
            </div>

            <div>
                <p class="text-xs text-zinc-500">Nilai pekerjaan</p>
                <x-ui.price
                    class="text-sm font-medium text-zinc-900"
                    :amount="$serviceRequest->agreed_price ?? $serviceRequest->service->base_price"
                    :unit="$serviceRequest->service->price_unit->label()"
                />
            </div>

            <div class="sm:col-span-2">
                <p class="text-xs text-zinc-500">Alamat lengkap</p>
                <p class="text-sm font-medium text-zinc-900">
                    {{ $serviceRequest->address }}@if ($serviceRequest->city), {{ $serviceRequest->city }}@endif
                </p>
            </div>
        </div>

        <div>
            <p class="text-xs text-zinc-500">Detail kebutuhan</p>
            <p class="mt-1 text-sm leading-relaxed text-zinc-600">{{ $serviceRequest->description }}</p>
        </div>

        @if ($serviceRequest->cancellation_reason)
            <div class="rounded-xl bg-rose-50 p-3 text-sm text-rose-700">
                Alasan pembatalan: {{ $serviceRequest->cancellation_reason }}
            </div>
        @endif
    </flux:card>

    @if ($serviceRequest->canBeScheduled())
        <form wire:submit="confirmSchedule" class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-4">
            <div>
                <p class="text-sm font-medium text-zinc-900">Jadwal pengerjaan</p>
                <p class="text-sm text-zinc-500">Pastikan jadwal ini sudah kamu sepakati dengan pelanggan.</p>
            </div>

            <flux:input wire:model="scheduledAt" type="datetime-local" label="Jadwal" required />

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="calendar-days">
                    Simpan jadwal
                </flux:button>
            </div>
        </form>
    @endif

    @if ($serviceRequest->canBeCancelledByPartner())
        <form wire:submit="cancel" class="flex flex-col gap-4 rounded-2xl border border-rose-200 bg-white p-4">
            <div>
                <p class="text-sm font-medium text-zinc-900">Tidak bisa mengerjakan job ini?</p>
                <p class="text-sm text-zinc-500">
                    Beri tahu alasannya supaya pelanggan paham dan bisa mencari mitra lain.
                </p>
            </div>

            <flux:input wire:model="cancellationReason" label="Alasan (opsional)" placeholder="mis. jadwal bertabrakan" />

            <div class="flex justify-end">
                <flux:button
                    type="submit"
                    variant="danger"
                    icon="x-circle"
                    wire:confirm="Batalkan job ini? Status akan berubah menjadi dibatalkan."
                >
                    Batalkan job
                </flux:button>
            </div>
        </form>
    @endif

    <div class="flex flex-wrap justify-end gap-2">
        @if ($serviceRequest->canBeStarted())
            <flux:button
                variant="primary"
                icon="wrench-screwdriver"
                wire:click="start"
                wire:confirm="Mulai pekerjaan ini sekarang?"
            >
                Mulai pekerjaan
            </flux:button>
        @endif

        @if ($serviceRequest->isInProgress())
            <flux:button
                variant="primary"
                icon="check-badge"
                wire:click="complete"
                wire:confirm="Tandai pekerjaan ini selesai?"
            >
                Tandai selesai
            </flux:button>
        @endif
    </div>

    @if ($serviceRequest->isCompleted() && $serviceRequest->review)
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">Ulasan pelanggan</flux:heading>

            <flux:card class="flex flex-col gap-3">
                <x-ui.rating-stars :rating="$serviceRequest->review->rating" size="lg" />

                @if ($serviceRequest->review->comment)
                    <p class="text-sm leading-relaxed text-zinc-600">{{ $serviceRequest->review->comment }}</p>
                @endif

                <p class="text-xs text-zinc-400">
                    {{ $serviceRequest->review->customer->name }} &middot;
                    {{ $serviceRequest->review->created_at->translatedFormat('d F Y') }}
                </p>
            </flux:card>
        </div>
    @endif

    <div class="flex flex-col gap-4">
        <flux:heading size="lg">Riwayat</flux:heading>

        <flux:card>
            <x-ui.timeline :histories="$serviceRequest->statusHistories" />
        </flux:card>
    </div>
</div>
