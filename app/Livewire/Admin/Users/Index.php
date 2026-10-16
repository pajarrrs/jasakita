<?php

namespace App\Livewire\Admin\Users;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $role = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->withCount(['serviceRequests', 'assignedJobs', 'reviewsWritten'])
            ->when($this->role !== '', fn ($query) => $query->where('role', $this->role))
            ->when($this->search !== '', function ($query): void {
                $keyword = '%'.$this->search.'%';

                $query->where(fn ($query) => $query
                    ->where('name', 'like', $keyword)
                    ->orWhere('email', 'like', $keyword)
                    ->orWhere('phone', 'like', $keyword));
            })
            ->orderBy('name')
            ->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.admin.users.index', [
            'roles' => Role::cases(),
        ]);
    }
}
