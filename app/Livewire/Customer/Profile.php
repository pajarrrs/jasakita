<?php

namespace App\Livewire\Customer;

use App\Enums\RequestStatus;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.customer')]
class Profile extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $currentPassword = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->phone = (string) $user->phone;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:9', 'max:20'],
        ]);

        Auth::user()->update($validated);

        Flux::toast(text: 'Profil disimpan.', variant: 'success');
    }

    public function updatePassword(): void
    {
        $validated = $this->validate([
            'currentPassword' => ['required', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->update(['password' => $validated['password']]);

        $this->reset('currentPassword', 'password', 'password_confirmation');

        Flux::toast(text: 'Password diperbarui.', variant: 'success');
    }

    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.customer.profile', [
            'requestCount' => $user->serviceRequests()->count(),
            'completedCount' => $user->serviceRequests()
                ->where('status', RequestStatus::Completed->value)
                ->count(),
            'reviewCount' => $user->reviewsWritten()->count(),
        ]);
    }
}
