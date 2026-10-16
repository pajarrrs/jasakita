<div class="flex flex-col gap-6">
    <div>
        <flux:heading size="xl">Daftar Pengguna</flux:heading>
        <flux:text class="mt-1">Semua akun pelanggan, mitra, dan admin.</flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="Cari nama, email, atau nomor..."
            label="Cari"
        />

        <flux:select wire:model.live="role" label="Peran">
            <flux:select.option value="">Semua peran</flux:select.option>
            @foreach ($roles as $option)
                <flux:select.option value="{{ $option->value }}">{{ $option->label() }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($this->users->isEmpty())
        <x-ui.empty-state
            icon="users"
            title="Tidak ada pengguna"
            description="Coba ubah kata kunci atau filter peran."
        />
    @else
        <div class="rounded-2xl border border-zinc-200 bg-white p-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Pengguna</flux:table.column>
                    <flux:table.column>Kontak</flux:table.column>
                    <flux:table.column>Peran</flux:table.column>
                    <flux:table.column>Aktivitas</flux:table.column>
                    <flux:table.column align="end">Bergabung</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->users as $user)
                        <flux:table.row :key="$user->id">
                            <flux:table.cell variant="strong" class="whitespace-normal">
                                <div class="flex items-center gap-3">
                                    <flux:avatar :name="$user->name" size="sm" />

                                    <div class="min-w-0">
                                        <div class="truncate">{{ $user->name }}</div>
                                        <div class="truncate text-xs font-normal text-zinc-400">
                                            {{ $user->email }}
                                        </div>
                                    </div>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                {{ $user->phone ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge :color="$user->role->color()" size="sm">
                                    {{ $user->role->label() }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="text-xs text-zinc-500">
                                @if ($user->role->isPartner())
                                    {{ $user->assigned_jobs_count }} job
                                @elseif ($user->role->isCustomer())
                                    {{ $user->service_requests_count }} request &middot; {{ $user->reviews_written_count }} ulasan
                                @else
                                    —
                                @endif
                            </flux:table.cell>

                            <flux:table.cell align="end" class="text-xs text-zinc-500">
                                {{ $user->created_at->translatedFormat('d M Y') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            @if ($this->users->hasPages())
                <div class="mt-4">
                    <flux:pagination :paginator="$this->users" />
                </div>
            @endif
        </div>
    @endif
</div>
