<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="xl">Verifikasi Mitra</flux:heading>
        <flux:text class="mt-1">
            Verifikasi identitas bikin pelanggan lebih percaya. Badge terverifikasi tampil di penawaran dan profilmu.
        </flux:text>
    </div>

    @if ($verification !== null)
        <flux:card class="flex flex-col gap-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-zinc-900">Status verifikasi identitas</p>
                    <p class="text-xs text-zinc-500">
                        Diperbarui {{ $verification->updated_at->translatedFormat('d F Y, H:i') }}
                    </p>
                </div>

                <flux:badge :color="$verification->status->color()" :icon="$verification->status->icon()" size="sm">
                    {{ $verification->status->label() }}
                </flux:badge>
            </div>

            <flux:text>{{ $verification->status->description() }}</flux:text>

            @if ($verification->isRejected() && $verification->rejection_reason)
                <div class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700">
                    Catatan admin: {{ $verification->rejection_reason }}
                </div>
            @endif
        </flux:card>
    @endif

    @if ($verification === null || $verification->isRejected())
        <flux:card>
            <form wire:submit="submit" class="flex flex-col gap-6">
                <div>
                    <flux:heading size="lg">
                        {{ $verification?->isRejected() ? 'Kirim Ulang Verifikasi' : 'Formulir Verifikasi' }}
                    </flux:heading>
                    <flux:text class="mt-1">
                        Data identitas hanya dilihat admin untuk keperluan verifikasi.
                    </flux:text>
                </div>

                <flux:input
                    wire:model="fullName"
                    label="Nama lengkap sesuai KTP"
                    placeholder="mis. Budi Santoso"
                    required
                />

                <flux:input
                    wire:model="idNumber"
                    label="NIK"
                    inputmode="numeric"
                    placeholder="16 digit NIK"
                    required
                />

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="flex flex-col gap-3">
                        <flux:input
                            wire:model="idPhoto"
                            type="file"
                            accept="image/*"
                            label="Foto KTP"
                            description="Pastikan seluruh bagian KTP terbaca jelas."
                        />

                        @error('idPhoto')
                            <p class="text-sm font-medium text-red-500">{{ $message }}</p>
                        @enderror

                        @if ($idPhoto)
                            <img src="{{ $idPhoto->temporaryUrl() }}" alt="Pratinjau foto KTP" class="h-36 w-full rounded-xl border border-zinc-200 object-cover">
                        @elseif ($verification?->id_photo_path && Storage::disk('public')->exists($verification->id_photo_path))
                            <img src="{{ Storage::disk('public')->url($verification->id_photo_path) }}" alt="Foto KTP tersimpan" class="h-36 w-full rounded-xl border border-zinc-200 object-cover">
                        @endif
                    </div>

                    <div class="flex flex-col gap-3">
                        <flux:input
                            wire:model="selfiePhoto"
                            type="file"
                            accept="image/*"
                            label="Foto selfie dengan KTP"
                            description="Wajah dan KTP harus terlihat dalam satu foto."
                        />

                        @error('selfiePhoto')
                            <p class="text-sm font-medium text-red-500">{{ $message }}</p>
                        @enderror

                        @if ($selfiePhoto)
                            <img src="{{ $selfiePhoto->temporaryUrl() }}" alt="Pratinjau foto selfie" class="h-36 w-full rounded-xl border border-zinc-200 object-cover">
                        @elseif ($verification?->selfie_photo_path && Storage::disk('public')->exists($verification->selfie_photo_path))
                            <img src="{{ Storage::disk('public')->url($verification->selfie_photo_path) }}" alt="Foto selfie tersimpan" class="h-36 w-full rounded-xl border border-zinc-200 object-cover">
                        @endif
                    </div>
                </div>

                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary" icon="paper-airplane">
                        {{ $verification?->isRejected() ? 'Kirim ulang verifikasi' : 'Kirim verifikasi' }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    <flux:card class="flex flex-col gap-3">
        <p class="text-sm font-medium text-zinc-900">Kenapa perlu verifikasi?</p>

        <ul class="flex flex-col gap-2 text-sm text-zinc-600">
            <li class="flex gap-2">
                <flux:icon name="check-badge" variant="solid" class="mt-0.5 size-4 shrink-0 text-teal-600" />
                Badge <span class="font-medium text-zinc-900">Terverifikasi</span> tampil di penawaran dan profil mitra.
            </li>
            <li class="flex gap-2">
                <flux:icon name="users" variant="solid" class="mt-0.5 size-4 shrink-0 text-teal-600" />
                Pelanggan lebih memilih mitra yang identitasnya sudah diverifikasi.
            </li>
            <li class="flex gap-2">
                <flux:icon name="lock-closed" variant="solid" class="mt-0.5 size-4 shrink-0 text-teal-600" />
                Data identitasmu hanya dipakai admin untuk proses verifikasi.
            </li>
        </ul>
    </flux:card>
</div>
