<div class="flex flex-col gap-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <flux:heading size="xl">Verifikasi Mitra</flux:heading>
            <flux:text class="mt-1">Periksa data identitas mitra sebelum memberi badge terverifikasi.</flux:text>
        </div>

        @if ($this->pendingCount > 0)
            <flux:badge color="amber" icon="clock" size="lg">
                {{ $this->pendingCount }} menunggu review
            </flux:badge>
        @endif
    </div>

    <flux:select wire:model.live="status" label="Status" class="max-w-xs">
        <flux:select.option value="">Semua status</flux:select.option>
        @foreach ($statuses as $statusOption)
            <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    @if ($this->verifications->isEmpty())
        <x-ui.empty-state
            icon="shield-check"
            title="Belum ada pengajuan verifikasi"
            description="Pengajuan verifikasi identitas dari mitra akan tampil di sini."
        />
    @else
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Mitra</flux:table.column>
                    <flux:table.column>NIK</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Diperbarui</flux:table.column>
                    <flux:table.column align="end">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->verifications as $verification)
                        <flux:table.row :key="$verification->id">
                            <flux:table.cell variant="strong" class="whitespace-normal">
                                <div class="truncate">{{ $verification->partner->user->name }}</div>
                                <div class="truncate text-xs font-normal text-zinc-400">
                                    {{ $verification->full_name }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>{{ $verification->id_number }}</flux:table.cell>

                            <flux:table.cell>
                                <flux:badge :color="$verification->status->color()" :icon="$verification->status->icon()" size="sm">
                                    {{ $verification->status->label() }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $verification->updated_at->translatedFormat('d M Y, H:i') }}
                            </flux:table.cell>

                            <flux:table.cell align="end">
                                <flux:button size="sm" variant="filled" icon="eye" wire:click="inspect({{ $verification->id }})">
                                    Periksa
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            @if ($this->verifications->hasPages())
                <div class="mt-4">
                    <flux:pagination :paginator="$this->verifications" />
                </div>
            @endif
        </div>
    @endif

    <flux:modal wire:model.self="showDetail" class="md:w-2xl">
        @php $verification = $this->selectedVerification; @endphp

        @if ($verification !== null)
            <div class="flex flex-col gap-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <flux:heading size="lg">{{ $verification->partner->user->name }}</flux:heading>
                        <flux:text class="mt-1">
                            Diajukan {{ $verification->updated_at->translatedFormat('d F Y, H:i') }}
                        </flux:text>
                    </div>

                    <flux:badge :color="$verification->status->color()" :icon="$verification->status->icon()">
                        {{ $verification->status->label() }}
                    </flux:badge>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl bg-zinc-50 p-3">
                        <p class="text-xs text-zinc-500">Nama sesuai KTP</p>
                        <p class="text-sm font-medium text-zinc-900">{{ $verification->full_name }}</p>
                    </div>

                    <div class="rounded-xl bg-zinc-50 p-3">
                        <p class="text-xs text-zinc-500">NIK</p>
                        <p class="text-sm font-medium text-zinc-900">{{ $verification->id_number }}</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        'id_photo_path' => 'Foto KTP',
                        'selfie_photo_path' => 'Selfie dengan KTP',
                    ] as $pathField => $label)
                        <div>
                            @if (Storage::disk('public')->exists($verification->{$pathField}))
                                <a href="{{ Storage::disk('public')->url($verification->{$pathField}) }}" target="_blank" class="group block">
                                    <img
                                        src="{{ Storage::disk('public')->url($verification->{$pathField}) }}"
                                        alt="{{ $label }}"
                                        class="h-44 w-full rounded-xl border border-zinc-200 object-cover transition group-hover:border-teal-300"
                                    >
                                </a>
                            @else
                                <div class="flex h-44 w-full flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-300 bg-zinc-50 text-zinc-400">
                                    <flux:icon name="photo" class="size-6" />
                                    <span class="text-xs">Berkas tidak tersedia</span>
                                </div>
                            @endif

                            <p class="mt-2 text-center text-xs text-zinc-500">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>

                @if ($verification->isPending())
                    <div>
                        <flux:textarea
                            wire:model="rejectionReason"
                            label="Alasan penolakan"
                            rows="2"
                            placeholder="mis. Foto KTP buram, mohon unggah ulang"
                        />

                        @error('rejectionReason')
                            <p class="mt-1 text-sm font-medium text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap justify-end gap-2">
                        <flux:modal.close>
                            <flux:button variant="filled">Tutup</flux:button>
                        </flux:modal.close>

                        <flux:button variant="danger" icon="x-circle" wire:click="reject">
                            Tolak
                        </flux:button>

                        <flux:button variant="primary" icon="check-badge" wire:click="approve">
                            Setujui
                        </flux:button>
                    </div>
                @else
                    <flux:text>
                        Diproses {{ $verification->reviewed_at?->translatedFormat('d F Y, H:i') ?? '-' }}
                        oleh {{ $verification->reviewer?->name ?? 'admin' }}.
                        @if ($verification->rejection_reason)
                            Alasan penolakan: {{ $verification->rejection_reason }}
                        @endif
                    </flux:text>

                    <div class="flex justify-end">
                        <flux:modal.close>
                            <flux:button variant="filled">Tutup</flux:button>
                        </flux:modal.close>
                    </div>
                @endif
            </div>
        @endif
    </flux:modal>
</div>
