<?php

namespace Tests\Feature\Livewire\Shared;

use App\Enums\RequestStatus;
use App\Livewire\Shared\Chat;
use App\Models\Message;
use App\Models\PartnerProfile;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\MessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_customer_can_send_a_message_to_the_assigned_partner(): void
    {
        $customer = User::factory()->create();
        $partner = PartnerProfile::factory()->create();
        $serviceRequest = ServiceRequest::factory()->assigned($partner)->create(['customer_id' => $customer->id]);

        Livewire::actingAs($customer)
            ->test(Chat::class, ['serviceRequest' => $serviceRequest])
            ->set('body', 'Halo, bisa datang jam 8 pagi?')
            ->call('send')
            ->assertHasNoErrors()
            ->assertSet('body', '');

        $message = Message::sole();

        $this->assertSame($customer->id, $message->sender_id);
        $this->assertSame('Halo, bisa datang jam 8 pagi?', $message->body);
        $this->assertTrue(
            $partner->user->notifications()->where('type', MessageReceived::class)->exists(),
        );
    }

    public function test_the_partner_can_reply_and_the_customer_is_notified(): void
    {
        $customer = User::factory()->create();
        $partner = PartnerProfile::factory()->create();
        $serviceRequest = ServiceRequest::factory()->assigned($partner)->create(['customer_id' => $customer->id]);

        Livewire::actingAs($partner->user)
            ->test(Chat::class, ['serviceRequest' => $serviceRequest])
            ->set('body', 'Bisa, saya datang jam 8 ya.')
            ->call('send')
            ->assertHasNoErrors();

        $this->assertSame($partner->user->id, Message::sole()->sender_id);
        $this->assertTrue(
            $customer->notifications()->where('type', MessageReceived::class)->exists(),
        );
    }

    public function test_the_message_body_is_required_and_has_a_minimum_length(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->assigned()->create(['customer_id' => $customer->id]);

        Livewire::actingAs($customer)
            ->test(Chat::class, ['serviceRequest' => $serviceRequest])
            ->call('send')
            ->assertHasErrors(['body' => 'required'])
            ->set('body', 'a')
            ->call('send')
            ->assertHasErrors(['body' => 'min']);

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_an_unrelated_customer_cannot_open_the_chat(): void
    {
        $serviceRequest = ServiceRequest::factory()->assigned()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(Chat::class, ['serviceRequest' => $serviceRequest])
            ->assertStatus(403);
    }

    public function test_a_partner_who_is_not_assigned_cannot_open_the_chat(): void
    {
        $serviceRequest = ServiceRequest::factory()->assigned()->create();
        $otherPartner = PartnerProfile::factory()->create();

        Livewire::actingAs($otherPartner->user)
            ->test(Chat::class, ['serviceRequest' => $serviceRequest])
            ->assertStatus(403);
    }

    public function test_incoming_messages_are_marked_as_read_when_the_chat_opens(): void
    {
        $customer = User::factory()->create();
        $partner = PartnerProfile::factory()->create();
        $serviceRequest = ServiceRequest::factory()->assigned($partner)->create(['customer_id' => $customer->id]);

        $incoming = Message::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'sender_id' => $partner->user->id,
        ]);

        $outgoing = Message::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'sender_id' => $customer->id,
        ]);

        Livewire::actingAs($customer)
            ->test(Chat::class, ['serviceRequest' => $serviceRequest]);

        $this->assertNotNull($incoming->fresh()->read_at);
        $this->assertNull($outgoing->fresh()->read_at);
    }

    public function test_messages_cannot_be_sent_after_the_request_is_cancelled(): void
    {
        $customer = User::factory()->create();
        $partner = PartnerProfile::factory()->create();
        $serviceRequest = ServiceRequest::factory()->assigned($partner)->create([
            'customer_id' => $customer->id,
            'status' => RequestStatus::Cancelled,
            'cancellation_reason' => 'Rencana berubah.',
        ]);

        Livewire::actingAs($customer)
            ->test(Chat::class, ['serviceRequest' => $serviceRequest])
            ->set('body', 'Masih bisa dikerjakan?')
            ->call('send');

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_the_conversation_is_shown_on_the_customer_request_page(): void
    {
        $customer = User::factory()->create();
        $partner = PartnerProfile::factory()->create();
        $serviceRequest = ServiceRequest::factory()->assigned($partner)->create(['customer_id' => $customer->id]);

        Message::factory()->create([
            'service_request_id' => $serviceRequest->id,
            'sender_id' => $partner->user->id,
            'body' => 'Saya otw ke lokasi ya.',
        ]);

        $this->actingAs($customer)
            ->get(route('customer.requests.show', $serviceRequest))
            ->assertOk()
            ->assertSee('Saya otw ke lokasi ya.')
            ->assertSee('Chat')
            ->assertSee('wire:poll.5s="refreshChat"', false);
    }
}
