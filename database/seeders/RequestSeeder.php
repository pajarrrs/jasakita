<?php

namespace Database\Seeders;

use App\Enums\OfferStatus;
use App\Enums\RequestMode;
use App\Enums\RequestStatus;
use App\Enums\Role;
use App\Enums\VerificationStatus;
use App\Models\Message;
use App\Models\Offer;
use App\Models\PartnerProfile;
use App\Models\PartnerVerification;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\OfferAccepted;
use App\Notifications\OfferReceived;
use App\Notifications\ReviewReceived;
use App\Notifications\VerificationSubmitted;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class RequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::where('email', 'pelanggan@jasakita.test')->first();
        $firstPartner = User::where('email', 'mitra@jasakita.test')->first();
        $secondPartner = User::where('email', 'mitra2@jasakita.test')->first();
        $thirdPartner = User::where('email', 'mitra3@jasakita.test')->first();

        if ($customer === null || $firstPartner === null || $secondPartner === null || $thirdPartner === null) {
            return;
        }

        if (ServiceRequest::query()->exists()) {
            return;
        }

        $ac = Service::where('slug', 'servis-ac')->firstOrFail();
        $fridge = Service::where('slug', 'servis-kulkas')->firstOrFail();
        $sofa = Service::where('slug', 'cuci-sofa-karpet')->firstOrFail();
        $cctv = Service::where('slug', 'instalasi-cctv')->firstOrFail();

        $firstProfile = $this->prepareProfile($firstPartner, [
            'bio' => 'Teknisi AC dan kulkas dengan alat lengkap. Siap datang ke lokasi di Bandung dan sekitarnya.',
            'city' => 'Bandung',
            'years_of_experience' => 6,
        ], [
            $ac->id => ['custom_price' => 165000, 'note' => 'Termasuk cuci unit dan isi freon bila perlu.'],
            $fridge->id => ['custom_price' => 210000, 'note' => null],
            $cctv->id => ['custom_price' => null, 'note' => null],
        ]);

        $secondProfile = $this->prepareProfile($secondPartner, [
            'bio' => 'Tim kebersihan rumah dan kantor. Membawa alat sendiri dan ramah hewan peliharaan.',
            'city' => 'Bandung',
            'years_of_experience' => 4,
        ], [
            $ac->id => ['custom_price' => 150000, 'note' => 'Fokus cuci dan vakum, tanpa isi freon.'],
            $sofa->id => ['custom_price' => 65000, 'note' => null],
        ]);

        $thirdProfile = $this->prepareProfile($thirdPartner, [
            'bio' => 'Teknisi listrik dan taman, melayani area Bandung kota.',
            'city' => 'Bandung',
            'years_of_experience' => 8,
        ], [
            $cctv->id => ['custom_price' => 380000, 'note' => 'Termasuk kabel 20 meter.'],
        ]);

        $this->seedReviewedJob($customer, $ac, $firstProfile);
        $this->seedJobWaitingForReview($customer, $fridge, $firstProfile);

        $scheduledJob = $this->seedScheduledJob($customer, $ac, $firstProfile);

        $this->seedOpenQuoteRequest($customer, $ac, $secondProfile);
        $this->seedOpenDirectRequest($customer, $cctv);

        $this->seedMessages($scheduledJob, $customer, $firstPartner);
        $this->seedVerifications($firstProfile, $thirdProfile);
    }

    /**
     * @param  array<string, mixed>  $profileAttributes
     * @param  array<int, array<string, mixed>>  $services
     */
    private function prepareProfile(User $partner, array $profileAttributes, array $services): PartnerProfile
    {
        $profile = $partner->partnerProfileOrCreate();
        $profile->update($profileAttributes);
        $profile->services()->syncWithoutDetaching($services);

        return $profile;
    }

    private function seedReviewedJob(User $customer, Service $service, PartnerProfile $partner): void
    {
        $serviceRequest = $this->createRequest($customer, $service, [
            'mode' => RequestMode::Direct,
            'title' => 'Servis AC kamar tidur',
            'description' => 'AC tidak dingin sejak seminggu dan berisik saat dinyalakan.',
            'address' => 'Jl. Kenanga No. 12',
            'city' => 'Bandung',
            'partner_profile_id' => $partner->id,
            'agreed_price' => 165000,
        ], [
            [RequestStatus::Open, 12, 'Request dibuat pelanggan.'],
            [RequestStatus::Assigned, 12, 'Job diambil langsung oleh Mitra Contoh.'],
            [RequestStatus::Scheduled, 11, 'Jadwal disepakati pukul 09.00.'],
            [RequestStatus::InProgress, 10, 'Mitra mulai mengerjakan.'],
            [RequestStatus::Completed, 10, 'Mitra menandai pekerjaan selesai.'],
        ]);

        $review = Review::create([
            'service_request_id' => $serviceRequest->id,
            'customer_id' => $customer->id,
            'partner_profile_id' => $partner->id,
            'rating' => 5,
            'comment' => 'Datang tepat waktu, AC dingin lagi, dan lokasi dibersihkan setelah selesai.',
        ]);

        $partner->user->notify(new ReviewReceived($review));
    }

    private function seedJobWaitingForReview(User $customer, Service $service, PartnerProfile $partner): void
    {
        $serviceRequest = $this->createRequest($customer, $service, [
            'mode' => RequestMode::Quote,
            'title' => 'Kulkas dua pintu tidak dingin',
            'description' => 'Bagian bawah kulkas hangat dan ada bunyi berdecit dari mesin.',
            'address' => 'Jl. Melati No. 4',
            'city' => 'Bandung',
            'partner_profile_id' => $partner->id,
            'agreed_price' => 210000,
        ], [
            [RequestStatus::Open, 20, 'Request dibuat pelanggan.'],
            [RequestStatus::Assigned, 19, 'Penawaran dari Mitra Contoh diterima.'],
            [RequestStatus::Scheduled, 18, 'Jadwal pengerjaan dikonfirmasi.'],
            [RequestStatus::InProgress, 17, 'Mitra mulai mengerjakan.'],
            [RequestStatus::Completed, 17, 'Mitra menandai pekerjaan selesai.'],
        ]);

        $offer = Offer::create([
            'service_request_id' => $serviceRequest->id,
            'partner_profile_id' => $partner->id,
            'price' => 210000,
            'duration_estimate' => '1-3 jam',
            'message' => 'Sudah biasa menangani kulkas dua pintu, garansi 14 hari untuk sparepart.',
            'status' => OfferStatus::Accepted,
        ]);

        $partner->user->notify(new OfferAccepted($offer));
    }

    private function seedScheduledJob(User $customer, Service $service, PartnerProfile $partner): ServiceRequest
    {
        return $this->createRequest($customer, $service, [
            'mode' => RequestMode::Direct,
            'title' => 'Cuci AC ruang tamu',
            'description' => 'Minta dicuci sekalian dicek apakah freonnya masih cukup.',
            'address' => 'Jl. Merdeka No. 88',
            'city' => 'Bandung',
            'partner_profile_id' => $partner->id,
            'agreed_price' => 165000,
            'scheduled_at' => now()->addDays(2)->setTime(9, 0),
        ], [
            [RequestStatus::Open, 3, 'Request dibuat pelanggan.'],
            [RequestStatus::Assigned, 3, 'Job diambil langsung oleh Mitra Contoh.'],
            [RequestStatus::Scheduled, 2, 'Jadwal pengerjaan dikonfirmasi.'],
        ]);
    }

    private function seedOpenQuoteRequest(User $customer, Service $service, PartnerProfile $offerPartner): void
    {
        $serviceRequest = $this->createRequest($customer, $service, [
            'mode' => RequestMode::Quote,
            'title' => 'Servis AC kantor 2 unit',
            'description' => 'Dua unit AC di ruang meeting kurang dingin, minta dicek dan dicuci.',
            'address' => 'Jl. Asia Afrika No. 5',
            'city' => 'Bandung',
            'scheduled_at' => now()->addDays(4)->setTime(13, 0),
        ], [
            [RequestStatus::Open, 2, 'Request dibuat pelanggan.'],
        ]);

        $offer = Offer::create([
            'service_request_id' => $serviceRequest->id,
            'partner_profile_id' => $offerPartner->id,
            'price' => 320000,
            'duration_estimate' => '2-3 jam',
            'message' => 'Harga untuk dua unit sekaligus, termasuk vakum dan pembersihan filter.',
            'status' => OfferStatus::Pending,
        ]);

        $customer->notify(new OfferReceived($offer));
    }

    private function seedOpenDirectRequest(User $customer, Service $service): void
    {
        $this->createRequest($customer, $service, [
            'mode' => RequestMode::Direct,
            'title' => 'Pasang CCTV 4 kamera di ruko',
            'description' => 'Butuh pemasangan 4 kamera CCTV di ruko dua lantai, kabel sudah tersedia sebagian.',
            'address' => 'Jl. Sukajadi No. 21',
            'city' => 'Bandung',
            'scheduled_at' => now()->addDays(3)->setTime(10, 0),
        ], [
            [RequestStatus::Open, 1, 'Request dibuat pelanggan.'],
        ]);
    }

    /**
     * Contoh percakapan pelanggan dan mitra pada job yang sudah terjadwal.
     */
    private function seedMessages(ServiceRequest $serviceRequest, User $customer, User $partner): void
    {
        $conversation = [
            [$customer->id, 2, 'Halo, untuk cuci AC ruang tamu bisa datang lebih pagi jam 8?', true],
            [$partner->id, 2, 'Halo! Bisa, jadwal sebelumnya saya geser. Jam 8 saya sampai ya.', true],
            [$customer->id, 1, 'Oke, nanti gerbangnya saya buka. Terima kasih.', true],
            [$partner->id, 0, 'Siap, saya bawa steamer dan vacuum biar hasilnya maksimal.', false],
        ];

        foreach ($conversation as [$senderId, $daysAgo, $body, $isRead]) {
            $message = Message::create([
                'service_request_id' => $serviceRequest->id,
                'sender_id' => $senderId,
                'body' => $body,
                'read_at' => $isRead ? now()->subDays(max($daysAgo - 1, 0)) : null,
            ]);

            $this->stamp($message, now()->subDays($daysAgo));
        }
    }

    /**
     * Contoh pengajuan verifikasi: satu sudah disetujui, satu menunggu review admin.
     */
    private function seedVerifications(PartnerProfile $verifiedPartner, PartnerProfile $pendingPartner): void
    {
        $adminId = User::where('email', 'admin@jasakita.test')->value('id');

        PartnerVerification::create([
            'partner_profile_id' => $verifiedPartner->id,
            'full_name' => $verifiedPartner->user->name,
            'id_number' => '3273010101900001',
            'id_photo_path' => 'kyc/contoh-ktp.jpg',
            'selfie_photo_path' => 'kyc/contoh-selfie.jpg',
            'status' => VerificationStatus::Approved,
            'reviewed_by' => $adminId,
            'reviewed_at' => now()->subDays(5),
        ]);

        $pending = PartnerVerification::create([
            'partner_profile_id' => $pendingPartner->id,
            'full_name' => $pendingPartner->user->name,
            'id_number' => '3273010101880002',
            'id_photo_path' => 'kyc/contoh-ktp.jpg',
            'selfie_photo_path' => 'kyc/contoh-selfie.jpg',
            'status' => VerificationStatus::Pending,
        ]);

        User::query()
            ->where('role', Role::Admin)
            ->get()
            ->each(fn (User $admin) => $admin->notify(new VerificationSubmitted($pending)));
    }

    /**
     * Buat request beserta riwayat status yang mundur sesuai hari ke belakang.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<int, array{0: RequestStatus, 1: int, 2: string}>  $history
     */
    private function createRequest(User $customer, Service $service, array $attributes, array $history): ServiceRequest
    {
        $serviceRequest = ServiceRequest::create([
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'status' => RequestStatus::Open,
            ...$attributes,
        ]);

        foreach ($history as [$status, $daysAgo, $note]) {
            $serviceRequest->update($status === RequestStatus::Completed
                ? ['status' => $status, 'completed_at' => now()->subDays($daysAgo)]
                : ['status' => $status]);

            $this->stamp(
                $serviceRequest->statusHistories()->create([
                    'status' => $status,
                    'note' => $note,
                    'changed_by' => $customer->id,
                ]),
                now()->subDays($daysAgo),
            );

            $this->stamp($serviceRequest, now()->subDays($daysAgo));
        }

        return $serviceRequest->refresh();
    }

    private function stamp(Model $model, CarbonInterface $moment): void
    {
        Model::withoutTimestamps(function () use ($model, $moment): void {
            $model->forceFill([
                'created_at' => $moment,
                'updated_at' => $moment,
            ])->save();
        });
    }
}
