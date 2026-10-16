<?php

namespace Tests\Feature\Livewire\Shared;

use App\Livewire\Shared\NotificationBell;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\JobCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_bell_lists_unread_notifications_with_the_counter(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['customer_id' => $customer->id]);

        $customer->notify(new JobCompleted($serviceRequest));

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Pekerjaan selesai')
            ->assertSee('Tandai semua dibaca')
            ->assertSee('wire:poll.30s', false);
    }

    public function test_clicking_a_notification_marks_it_read_and_redirects_to_its_page(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['customer_id' => $customer->id]);

        $customer->notify(new JobCompleted($serviceRequest));

        $notification = $customer->notifications()->sole();

        Livewire::actingAs($customer)
            ->test(NotificationBell::class)
            ->call('markAsRead', $notification->id)
            ->assertRedirect(route('customer.requests.show', $serviceRequest));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_all_notifications_can_be_marked_as_read_at_once(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['customer_id' => $customer->id]);

        $customer->notify(new JobCompleted($serviceRequest));
        $customer->notify(new JobCompleted(
            ServiceRequest::factory()->completed()->create(['customer_id' => $customer->id]),
        ));

        $this->assertSame(2, $customer->unreadNotifications()->count());

        Livewire::actingAs($customer)
            ->test(NotificationBell::class)
            ->call('markAllAsRead');

        $this->assertSame(0, $customer->fresh()->unreadNotifications()->count());
    }

    public function test_a_user_cannot_mark_another_users_notification_as_read(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['customer_id' => $otherCustomer->id]);

        $otherCustomer->notify(new JobCompleted($serviceRequest));

        $notification = $otherCustomer->notifications()->sole();

        Livewire::actingAs($customer)
            ->test(NotificationBell::class)
            ->call('markAsRead', $notification->id)
            ->assertNoRedirect();

        $this->assertNull($notification->fresh()->read_at);
    }
}
