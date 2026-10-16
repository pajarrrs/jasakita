<?php

namespace App\Livewire\Partner\Services;

use App\Models\Service;
use App\Models\ServiceCategory;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.partner')]
class Index extends Component
{
    /**
     * Pilihan mitra per jasa, dikunci dengan ID jasa.
     *
     * @var array<string, array{offered: bool, customPrice: string, note: string}>
     */
    public array $selection = [];

    public function mount(): void
    {
        $profile = Auth::user()->partnerProfileOrCreate();

        $offered = $profile->services()->get()->keyBy('id');

        $this->selection = Service::query()
            ->where('is_active', true)
            ->get()
            ->mapWithKeys(function (Service $service) use ($offered): array {
                $pivot = $offered->get($service->id)?->pivot;

                return [
                    (string) $service->id => [
                        'offered' => $pivot !== null,
                        'customPrice' => $this->numberToString($pivot?->custom_price),
                        'note' => (string) ($pivot?->note ?? ''),
                    ],
                ];
            })
            ->all();
    }

    public function save(): void
    {
        $rules = $this->rules();

        if ($rules !== []) {
            $this->validate($rules);
        }

        $profile = Auth::user()->partnerProfileOrCreate();

        DB::transaction(function () use ($profile): void {
            foreach ($this->selection as $serviceId => $row) {
                if (! ($row['offered'] ?? false)) {
                    $profile->services()->detach((int) $serviceId);

                    continue;
                }

                $profile->services()->syncWithoutDetaching([
                    (int) $serviceId => [
                        'custom_price' => ($row['customPrice'] ?? '') !== '' ? $row['customPrice'] : null,
                        'note' => ($row['note'] ?? '') !== '' ? $row['note'] : null,
                    ],
                ]);
            }
        });

        Flux::toast(text: 'Layanan mitra disimpan.', variant: 'success');
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(): array
    {
        $rules = [];

        foreach (array_keys($this->selection) as $serviceId) {
            if (! ($this->selection[$serviceId]['offered'] ?? false)) {
                continue;
            }

            $rules["selection.{$serviceId}.customPrice"] = ['nullable', 'numeric', 'min:0', 'max:999999999'];
            $rules["selection.{$serviceId}.note"] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return Collection<int, ServiceCategory>
     */
    #[Computed]
    public function categories(): Collection
    {
        return ServiceCategory::query()
            ->where('is_active', true)
            ->whereHas('services', fn ($query) => $query->where('is_active', true))
            ->with(['services' => fn ($query) => $query->where('is_active', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function offeredCount(): int
    {
        return collect($this->selection)->where('offered', true)->count();
    }

    public function render(): View
    {
        return view('livewire.partner.services.index');
    }

    private function numberToString(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return (string) (float) $value;
    }
}
