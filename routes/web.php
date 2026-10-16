<?php

use App\Http\Controllers\AppDownloadController;
use App\Http\Controllers\Auth\LogoutController;
use App\Livewire\Admin\Categories\Index as AdminCategoriesIndex;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Requests\Index as AdminRequestsIndex;
use App\Livewire\Admin\Services\Index as AdminServicesIndex;
use App\Livewire\Admin\Users\Index as AdminUsersIndex;
use App\Livewire\Admin\Verifications\Index as AdminVerificationsIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Customer\Categories\Index as CustomerCategoriesIndex;
use App\Livewire\Customer\Categories\Show as CustomerCategoriesShow;
use App\Livewire\Customer\Dashboard as CustomerDashboard;
use App\Livewire\Customer\Profile as CustomerProfile;
use App\Livewire\Customer\Requests\Create as CustomerRequestsCreate;
use App\Livewire\Customer\Requests\Index as CustomerRequestsIndex;
use App\Livewire\Customer\Requests\Show as CustomerRequestsShow;
use App\Livewire\Customer\Services\Show as CustomerServicesShow;
use App\Livewire\Partner\Dashboard as PartnerDashboard;
use App\Livewire\Partner\Jobs\Index as PartnerJobsIndex;
use App\Livewire\Partner\Jobs\Show as PartnerJobsShow;
use App\Livewire\Partner\Profile as PartnerProfile;
use App\Livewire\Partner\Requests\Index as PartnerRequestsIndex;
use App\Livewire\Partner\Requests\Show as PartnerRequestsShow;
use App\Livewire\Partner\Services\Index as PartnerServicesIndex;
use App\Livewire\Partner\Verification as PartnerVerification;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->dashboardRouteName());
    }

    return view('welcome', [
        'categories' => ServiceCategory::query()
            ->where('is_active', true)
            ->withCount([
                'services' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->take(6)
            ->get(),
        'serviceCount' => Service::query()->where('is_active', true)->count(),
        'apps' => config('downloads.apps'),
    ]);
})->name('home');

Route::get('/unduh/{app}', [AppDownloadController::class, 'show'])->name('downloads.show');

Route::middleware('guest')->group(function () {
    Route::get('/masuk', Login::class)->name('login');
    Route::get('/daftar', Register::class)->name('register');
});

Route::post('/keluar', LogoutController::class)->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:customer'])->prefix('app')->name('customer.')->group(function () {
    Route::get('/', CustomerDashboard::class)->name('dashboard');

    Route::get('/kategori', CustomerCategoriesIndex::class)->name('categories.index');
    Route::get('/kategori/{category:slug}', CustomerCategoriesShow::class)->name('categories.show');
    Route::get('/jasa/{service:slug}', CustomerServicesShow::class)->name('services.show');

    Route::get('/request', CustomerRequestsIndex::class)->name('requests.index');
    Route::get('/request/baru/{service:slug}', CustomerRequestsCreate::class)->name('requests.create');
    Route::get('/request/{serviceRequest}', CustomerRequestsShow::class)->name('requests.show');

    Route::get('/profil', CustomerProfile::class)->name('profile');
});

Route::middleware(['auth', 'role:partner'])->prefix('mitra')->name('partner.')->group(function () {
    Route::get('/', PartnerDashboard::class)->name('dashboard');

    Route::get('/profil', PartnerProfile::class)->name('profile');
    Route::get('/layanan', PartnerServicesIndex::class)->name('services.index');
    Route::get('/verifikasi', PartnerVerification::class)->name('verification');

    Route::get('/request', PartnerRequestsIndex::class)->name('requests.index');
    Route::get('/request/{serviceRequest}', PartnerRequestsShow::class)->name('requests.show');

    Route::get('/job', PartnerJobsIndex::class)->name('jobs.index');
    Route::get('/job/{serviceRequest}', PartnerJobsShow::class)->name('jobs.show');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');

    Route::get('/kategori', AdminCategoriesIndex::class)->name('categories.index');
    Route::get('/jasa', AdminServicesIndex::class)->name('services.index');
    Route::get('/request', AdminRequestsIndex::class)->name('requests.index');
    Route::get('/pengguna', AdminUsersIndex::class)->name('users.index');
    Route::get('/verifikasi', AdminVerificationsIndex::class)->name('verifications.index');
});
