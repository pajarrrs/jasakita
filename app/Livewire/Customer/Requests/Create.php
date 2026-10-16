<?php

namespace App\Livewire\Customer\Requests;

use App\Enums\RequestMode;
use App\Enums\RequestStatus;
use App\Models\Service;
use App\Models\ServiceRequest;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class Create extends Component
{
    public Service $service;

    public string $mode = 'quote';

    public string $title = '';

    public string $description = '';

    public string $address = '';

    public string $city = '';

    public string $scheduledAt = '';

    public function mount(Service $service): void
    {
        abort_unless($service->is_active, 404);

        $this->service = $service->loadMissing('category');
        $this->title = $service->name;

        $requestedMode = request()->query('mode');

        if (is_string($requestedMode) && RequestMode::tryFrom($requestedMode) !== null) {
            $this->mode = $requestedMode;
        }
    }

    public function createRequest(): void
    {
        $validated = $this->validate([
            'mode' => ['required', Rule::enum(RequestMode::class)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'scheduledAt' => ['nullable', 'date', 'after:now'],
        ]);

        $serviceRequest = ServiceRequest::create([
            'customer_id' => Auth::id(),
            'service_id' => $this->service->id,
            'mode' => $validated['mode'],
            'status' => RequestStatus::Open,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'address' => $validated['address'],
            'city' => $validated['city'] !== '' ? $validated['city'] : null,
            'scheduled_at' => $validated['scheduledAt'] !== '' ? $validated['scheduledAt'] : null,
        ]);

        $serviceRequest->recordStatus(
            RequestStatus::Open,
            Auth::user(),
            'Request dibuat pelanggan.',
        );

        Flux::toast(
            text: $validated['mode'] === RequestMode::Direct->value
                ? 'Request dikirim. Mitra tercepat akan mengambil job ini.'
                : 'Request dikirim. Mitra akan mengirim penawaran harga.',
            variant: 'success',
        );

        $this->redirect(route('customer.requests.show', $serviceRequest), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.customer.requests.create', [
            'modes' => RequestMode::cases(),
        ]);
    }

    #[Computed]
    public function selectedMode(): RequestMode
    {
        return RequestMode::from($this->mode);
    }
}
