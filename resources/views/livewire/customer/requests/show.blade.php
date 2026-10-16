<div class="flex flex-col gap-6">
    <flux:breadcrumbs>
        <flux:breadcrumbs.item :href="route('customer.requests.index')" wire:navigate>
            Request Saya
        </flux:breadcrumbs.item>
        <flux:breadcrumbs.item>#{{ $serviceRequest->id }}</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <flux:heading size="xl">{{ $serviceRequest->title }}</flux:heading>
            <flux:text class="mt-1">
                {{ $serviceRequest->service->name }} &middot;
                dibuat {{ $serviceRequest->created_at->translatedFormat('d F Y, H:i') }}
            </flux:text>
        </div>

        <x-ui.status-pill :status="$serviceRequest->status" />
    </div>

    <flux:card class="flex flex-col gap-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-xs text-zinc-500">Cara pesan</p>
                <p class="text-sm font-medium text-zinc-900">{{ $serviceRequest->mode->label() }}</p>
            </div>

            <div>
                <p class="text-xs text-zinc-500">Jadwal</p>
                <p class="text-sm font-medium text-zinc-900">
                    {{ $serviceRequest->scheduled_at?->translatedFormat('d F Y, H:i') ?? 'Belum ditentukan' }}
                </p>
            </div>

            <div class="sm:col-span-2">
                <p class="text-xs text-zinc-500">Alamat</p>
                <p class="text-sm font-medium text-zinc-900">
                    {{ $serviceRequest->address }}@if ($serviceRequest->city), {{ $serviceRequest->city }}@endif
                </p>
            </div>

            <div>
                <p class="text-xs text-zinc-500">
                    {{ $serviceRequest->agreed_price !== null ? 'Harga disepakati' : 'Patokan harga' }}
                </p>
                <x-ui.price
                    class="text-sm font-medium text-zinc-900"
                    :amount="$serviceRequest->agreed_price ?? $serviceRequest->service->base_price"
                    :unit="$serviceRequest->service->price_unit->label()"
                />
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

    @if ($serviceRequest->partner)
        <flux:card class="flex items-center gap-4">
            <flux:avatar :name="$serviceRequest->partner->user->name" color="teal" />

            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="truncate font-medium text-zinc-900">{{ $serviceRequest->partner->user->name }}</p>
                    <x-ui.verified-badge :partner="$serviceRequest->partner" />
                </div>

                <p class="truncate text-xs text-zinc-500">
                    {{ $serviceRequest->partner->city ?? 'Area layanan belum diisi' }}
                    @if ($serviceRequest->partner->years_of_experience)
                        &middot; {{ $serviceRequest->partner->years_of_experience }} tahun pengalaman
                    @endif
                </p>
            </div>

            @if ($serviceRequest->partner->user->phone)
                <flux:button
                    :href="'https://wa.me/'.$serviceRequest->partner->user->phone"
                    target="_blank"
                    size="sm"
                    variant="filled"
                    icon="chat-bubble-left-right"
                    class="ms-auto shrink-0"
                >
                    Hubungi
                </flux:button>
            @endif
        </flux:card>
    @endif

    @if ($serviceRequest->canBeChatted())
        <livewire:shared.chat :service-request="$serviceRequest" wire:key="chat-{{ $serviceRequest->id }}" />
    @endif

    @if ($serviceRequest->isOpen())
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between gap-3">
                <flux:heading size="lg">Penawaran masuk</flux:heading>
                <flux:text class="text-sm">{{ $serviceRequest->offers->count() }} penawaran</flux:text>
            </div>

            @if ($serviceRequest->offers->isEmpty())
                <x-ui.empty-state
                    icon="chat-bubble-left-right"
                    title="Belum ada penawaran"
                    description="Mitra yang menyediakan jasa ini akan mengirim penawaran harga. Kamu akan melihatnya di sini."
                />
            @else
                <div class="flex flex-col gap-3">
                    @foreach ($serviceRequest->offers as $offer)
                        <flux:card class="flex flex-col gap-3" wire:key="offer-{{ $offer->id }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="truncate text-sm font-medium text-zinc-900">
                                            {{ $offer->partner->user->name }}
                                        </p>
                                        <x-ui.verified-badge :partner="$offer->partner" />
                                    </div>

                                    <p class="truncate text-xs text-zinc-500">
                                        {{ $offer->partner->city ?? 'Area belum diisi' }}
                                        @if ($offer->partner->years_of_experience)
                                            &middot; {{ $offer->partner->years_of_experience }} tahun pengalaman
                                        @endif
                                    </p>
                                </div>

                                <flux:badge :color="$offer->status->color()" size="sm" class="shrink-0">
                                    {{ $offer->status->label() }}
                                </flux:badge>
                            </div>

                            <div class="flex flex-wrap items-end gap-x-6 gap-y-2">
                                <div>
                                    <p class="text-xs text-zinc-500">Harga penawaran</p>
                                    <x-ui.price
                                        class="text-lg font-semibold text-zinc-900"
                                        :amount="$offer->price"
                                        :unit="$serviceRequest->service->price_unit->label()"
                                    />
                                </div>

                                @if ($offer->duration_estimate)
                                    <div>
                                        <p class="text-xs text-zinc-500">Perkiraan durasi</p>
                                        <p class="text-sm font-medium text-zinc-900">{{ $offer->duration_estimate }}</p>
                                    </div>
                                @endif
                            </div>

                            @if ($offer->message)
                                <p class="text-sm text-zinc-600">{{ $offer->message }}</p>
                            @endif

                            @if ($offer->isPending())
                                <div class="flex justify-end">
                                    <flux:button
                                        variant="primary"
                                        size="sm"
                                        icon="check"
                                        wire:click="acceptOffer({{ $offer->id }})"
                                        wire:confirm="Terima penawaran {{ $offer->partner->user->name }} seharga Rp {{ number_format((float) $offer->price, 0, ',', '.') }}?"
                                    >
                                        Terima penawaran
                                    </flux:button>
                                </div>
                            @endif
                        </flux:card>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if ($serviceRequest->canBeScheduled())
        <form wire:submit="confirmSchedule" class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-4">
            <div>
                <p class="text-sm font-medium text-zinc-900">Atur jadwal pengerjaan</p>
                <p class="text-sm text-zinc-500">Sepakati waktu dengan mitra, lalu simpan di sini.</p>
            </div>

            <flux:input
                wire:model="scheduledAt"
                type="datetime-local"
                label="Jadwal"
                required
            />

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="calendar-days">
                    Simpan jadwal
                </flux:button>
            </div>
        </form>
    @endif

    <div class="flex flex-wrap justify-end gap-2">
        @if ($serviceRequest->isInProgress())
            <flux:button
                variant="primary"
                icon="check-badge"
                wire:click="confirmCompletion"
                wire:confirm="Tandai pekerjaan ini selesai?"
            >
                Tandai selesai
            </flux:button>
        @endif

        @if ($serviceRequest->status->isCancellableByCustomer())
            <flux:button
                variant="danger"
                icon="x-circle"
                wire:click="cancel"
                wire:confirm="Batalkan request ini?"
            >
                Batalkan
            </flux:button>
        @endif
    </div>

    @if ($serviceRequest->isCompleted())
        <div class="flex flex-col gap-4">
            <flux:heading size="lg">Ulasan</flux:heading>

            @if ($serviceRequest->review)
                <flux:card class="flex flex-col gap-3">
                    <x-ui.rating-stars :rating="$serviceRequest->review->rating" size="lg" />

                    @if ($serviceRequest->review->comment)
                        <p class="text-sm leading-relaxed text-zinc-600">{{ $serviceRequest->review->comment }}</p>
                    @endif

                    <p class="text-xs text-zinc-400">
                        Dikirim {{ $serviceRequest->review->created_at->translatedFormat('d F Y') }}
                    </p>
                </flux:card>
            @else
                <form wire:submit="submitReview" class="flex flex-col gap-4 rounded-2xl border border-zinc-200 bg-white p-4">
                    <div>
                        <p class="text-sm font-medium text-zinc-900">Bagaimana hasil kerja mitra?</p>
                        <p class="text-sm text-zinc-500">Ulasanmu membantu pelanggan lain memilih mitra.</p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium text-zinc-700">Rating</span>

                        <div class="flex items-center gap-1">
                            @foreach (range(1, 5) as $star)
                                @php $isFilled = $rating !== '' && (int) $rating >= $star; @endphp

                                <button
                                    type="button"
                                    wire:click="$set('rating', '{{ $star }}')"
                                    class="rounded-md p-1 transition hover:bg-amber-50"
                                    aria-label="Beri {{ $star }} bintang"
                                >
                                    <flux:icon
                                        name="star"
                                        :variant="$isFilled ? 'solid' : 'outline'"
                                        class="size-7 {{ $isFilled ? 'text-amber-400' : 'text-zinc-300' }}"
                                    />
                                </button>
                            @endforeach

                            @if ($rating !== '')
                                <span class="ms-2 text-sm text-zinc-500">{{ $rating }}/5</span>
                            @endif
                        </div>

                        @error('rating')
                            <p class="text-sm font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <flux:textarea
                        wire:model="comment"
                        label="Komentar (opsional)"
                        rows="3"
                        placeholder="Ceritakan pengalamanmu bekerja sama dengan mitra ini."
                    />

                    <div class="flex justify-end">
                        <flux:button type="submit" variant="primary" icon="paper-airplane">
                            Kirim ulasan
                        </flux:button>
                    </div>
                </form>
            @endif
        </div>
    @endif

    <div class="flex flex-col gap-4">
        <flux:heading size="lg">Riwayat</flux:heading>

        <flux:card>
            <x-ui.timeline :histories="$serviceRequest->statusHistories" />
        </flux:card>
    </div>
</div>
