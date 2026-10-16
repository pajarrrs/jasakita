<?php

namespace App\Livewire\Partner\Requests;

use App\Enums\RequestStatus;
use App\Models\ServiceRequest;
use App\Notifications\RequestAssigned;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait TakesDirectJobs
{
    /**
     * Ambil job pesan langsung secara atomik: mitra tercepat yang menang.
     *
     * Komponen pemakai wajib menyediakan computed property `profile`.
     */
    protected function takeDirectJob(ServiceRequest $serviceRequest): bool
    {
        $profile = $this->profile;

        if (! $profile->is_available) {
            Flux::toast(text: 'Aktifkan status siap menerima job dulu.', variant: 'danger');

            return false;
        }

        if (! $serviceRequest->mode->isDirect()) {
            Flux::toast(text: 'Request ini memakai sistem penawaran harga.', variant: 'danger');

            return false;
        }

        if (! $profile->services()->whereKey($serviceRequest->service_id)->exists()) {
            abort(403);
        }

        $taken = DB::transaction(function () use ($serviceRequest, $profile): bool {
            $locked = ServiceRequest::query()->whereKey($serviceRequest->id)->lockForUpdate()->first();

            if ($locked === null || ! $locked->isOpen()) {
                return false;
            }

            $locked->update([
                'partner_profile_id' => $profile->id,
                'agreed_price' => $profile->effectivePriceFor($locked->service),
            ]);

            $locked->recordStatus(
                RequestStatus::Assigned,
                Auth::user(),
                'Job diambil langsung oleh '.Auth::user()->name.'.',
            );

            if ($locked->scheduled_at !== null) {
                $locked->recordStatus(
                    RequestStatus::Scheduled,
                    Auth::user(),
                    'Memakai jadwal yang diminta pelanggan.',
                );
            }

            return true;
        });

        if (! $taken) {
            Flux::toast(text: 'Job ini sudah diambil mitra lain.', variant: 'danger');

            return false;
        }

        $serviceRequest->refresh()->loadMissing('partner.user');
        $serviceRequest->customer->notify(new RequestAssigned($serviceRequest));

        Flux::toast(text: 'Job berhasil diambil.', variant: 'success');

        return true;
    }
}
