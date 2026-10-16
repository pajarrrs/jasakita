<?php

namespace App\Livewire\Partner;

use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.partner')]
class Profile extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $bio = '';

    public string $city = '';

    public string $yearsOfExperience = '';

    public bool $isAvailable = true;

    public function mount(): void
    {
        $user = Auth::user();
        $profile = $user->partnerProfileOrCreate();

        $this->name = $user->name;
        $this->phone = (string) $user->phone;
        $this->bio = (string) $profile->bio;
        $this->city = (string) $profile->city;
        $this->yearsOfExperience = $profile->years_of_experience === null
            ? ''
            : (string) $profile->years_of_experience;
        $this->isAvailable = (bool) $profile->is_available;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'yearsOfExperience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'isAvailable' => ['required', 'boolean'],
        ]);

        $user = Auth::user();

        $user->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
        ]);

        $user->partnerProfileOrCreate()->update([
            'bio' => $validated['bio'] !== '' ? $validated['bio'] : null,
            'city' => $validated['city'] !== '' ? $validated['city'] : null,
            'years_of_experience' => $validated['yearsOfExperience'] !== '' ? $validated['yearsOfExperience'] : null,
            'is_available' => $validated['isAvailable'],
        ]);

        Flux::toast(text: 'Profil mitra disimpan.', variant: 'success');
    }

    public function render(): View
    {
        $profile = Auth::user()->partnerProfile;

        return view('livewire.partner.profile', [
            'offeredServicesCount' => $profile?->services()->count() ?? 0,
            'ratingAverage' => $profile?->ratingAverage(),
            'reviewsCount' => $profile?->reviews()->count() ?? 0,
            'reviews' => $profile?->reviews()->with('customer')->take(5)->get() ?? collect(),
            'verificationStatus' => $profile?->verificationStatus(),
        ]);
    }
}
