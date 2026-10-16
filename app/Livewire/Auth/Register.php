<?php

namespace App\Livewire\Auth;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.auth')]
class Register extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|string|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:9|max:20')]
    public string $phone = '';

    #[Validate('required|in:customer,partner')]
    public string $role = 'customer';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * @return array<string, array<int, mixed>>
     */
    protected function rules(): array
    {
        return [
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function mount(): void
    {
        $requestedRole = request()->query('role');

        if (in_array($requestedRole, ['customer', 'partner'], true)) {
            $this->role = $requestedRole;
        }
    }

    public function register(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => $this->password,
            'role' => Role::from($this->role),
        ]);

        Auth::login($user);

        session()->regenerate();

        $this->redirect(route($user->dashboardRouteName()), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
