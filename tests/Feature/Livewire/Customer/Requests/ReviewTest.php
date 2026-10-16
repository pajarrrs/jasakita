<?php

namespace Tests\Feature\Livewire\Customer\Requests;

use App\Livewire\Customer\Requests\Show;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_customer_can_review_a_completed_job(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['customer_id' => $customer->id]);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->set('rating', '5')
            ->set('comment', 'Datang tepat waktu dan hasilnya rapi.')
            ->call('submitReview')
            ->assertHasNoErrors();

        $review = Review::sole();

        $this->assertSame(5, $review->rating);
        $this->assertSame($customer->id, $review->customer_id);
        $this->assertSame($serviceRequest->partner_profile_id, $review->partner_profile_id);
    }

    public function test_a_review_cannot_be_sent_before_the_job_is_completed(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->inProgress()->create(['customer_id' => $customer->id]);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->set('rating', '4')
            ->call('submitReview');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_a_job_can_only_be_reviewed_once(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['customer_id' => $customer->id]);

        Review::factory()->forRequest($serviceRequest)->create(['rating' => 4]);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->set('rating', '1')
            ->call('submitReview');

        $this->assertDatabaseCount('reviews', 1);
        $this->assertSame(4, Review::sole()->rating);
    }

    public function test_the_rating_is_required_and_must_between_one_and_five(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['customer_id' => $customer->id]);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->call('submitReview')
            ->assertHasErrors(['rating' => 'required'])
            ->set('rating', '6')
            ->call('submitReview')
            ->assertHasErrors(['rating' => 'between']);

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_the_submitted_review_is_shown_on_the_request_page(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->completed()->create(['customer_id' => $customer->id]);

        Livewire::actingAs($customer)
            ->test(Show::class, ['serviceRequest' => $serviceRequest])
            ->set('rating', '5')
            ->set('comment', 'Hasilnya memuaskan.')
            ->call('submitReview')
            ->assertHasNoErrors();

        $this->actingAs($customer)
            ->get(route('customer.requests.show', $serviceRequest))
            ->assertOk()
            ->assertSee('Hasilnya memuaskan.')
            ->assertDontSee('Kirim ulasan');
    }

    public function test_the_review_form_is_hidden_when_the_request_is_not_completed(): void
    {
        $customer = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->assigned()->create(['customer_id' => $customer->id]);

        $this->actingAs($customer)
            ->get(route('customer.requests.show', $serviceRequest))
            ->assertOk()
            ->assertDontSee('Kirim ulasan');
    }
}
