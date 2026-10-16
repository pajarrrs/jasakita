<?php

namespace Tests\Feature\Livewire\Partner;

use App\Livewire\Partner\Profile;
use App\Models\Review;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_the_partner_profile_on_first_visit(): void
    {
        $partner = User::factory()->partner()->create(['phone' => '081234567890']);

        Livewire::actingAs($partner)
            ->test(Profile::class)
            ->assertSet('name', $partner->name)
            ->assertSet('phone', '081234567890')
            ->assertSet('city', '')
            ->assertSet('isAvailable', true);

        $this->assertDatabaseHas('partner_profiles', ['user_id' => $partner->id]);
    }

    public function test_saves_profile_details_and_availability(): void
    {
        $partner = User::factory()->partner()->create(['phone' => '081234567890']);

        Livewire::actingAs($partner)
            ->test(Profile::class)
            ->set('name', 'Budi Teknisi')
            ->set('bio', 'Spesialis AC dan kulkas.')
            ->set('city', 'Bandung')
            ->set('yearsOfExperience', '7')
            ->set('isAvailable', false)
            ->call('save')
            ->assertHasNoErrors();

        $partner->refresh();

        $this->assertSame('Budi Teknisi', $partner->name);
        $this->assertDatabaseHas('partner_profiles', [
            'user_id' => $partner->id,
            'bio' => 'Spesialis AC dan kulkas.',
            'city' => 'Bandung',
            'years_of_experience' => 7,
            'is_available' => 0,
        ]);
    }

    public function test_clears_optional_fields_when_they_are_emptied(): void
    {
        $partner = User::factory()->partner()->create(['phone' => '081234567890']);
        $partner->partnerProfileOrCreate()->update([
            'city' => 'Bandung',
            'years_of_experience' => 7,
        ]);

        Livewire::actingAs($partner)
            ->test(Profile::class)
            ->set('city', '')
            ->set('yearsOfExperience', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('partner_profiles', [
            'user_id' => $partner->id,
            'city' => null,
            'years_of_experience' => null,
        ]);
    }

    public function test_requires_a_name(): void
    {
        $partner = User::factory()->partner()->create(['phone' => '081234567890']);

        Livewire::actingAs($partner)
            ->test(Profile::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);

        $this->assertSame($partner->name, $partner->refresh()->name);
    }

    public function test_requires_a_phone_number(): void
    {
        $partner = User::factory()->partner()->create(['phone' => '081234567890']);

        Livewire::actingAs($partner)
            ->test(Profile::class)
            ->set('phone', '')
            ->call('save')
            ->assertHasErrors(['phone' => 'required']);
    }

    public function test_rejects_unrealistic_experience_years(): void
    {
        $partner = User::factory()->partner()->create(['phone' => '081234567890']);

        Livewire::actingAs($partner)
            ->test(Profile::class)
            ->set('yearsOfExperience', '81')
            ->call('save')
            ->assertHasErrors(['yearsOfExperience' => 'max']);
    }

    public function test_shows_the_rating_average_and_recent_reviews(): void
    {
        $partner = User::factory()->partner()->create(['phone' => '081234567890']);
        $profile = $partner->partnerProfileOrCreate();

        $first = ServiceRequest::factory()->completed($profile)->create();
        $second = ServiceRequest::factory()->completed($profile)->create();

        Review::factory()->forRequest($first)->create(['rating' => 5, 'comment' => 'Kerjanya cepat dan rapi.']);
        Review::factory()->forRequest($second)->create(['rating' => 4]);

        Livewire::actingAs($partner)
            ->test(Profile::class)
            ->assertViewHas('reviewsCount', 2)
            ->assertViewHas('ratingAverage', 4.5)
            ->assertSee('Kerjanya cepat dan rapi.')
            ->assertSee('4,5');
    }
}
