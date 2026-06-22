<?php

namespace Tests\Feature;

use App\Mail\OrderRequestReceived;
use App\Models\Artwork;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_gallery_shows_only_published_artworks(): void
    {
        $published = Artwork::factory()->create(['title' => 'Published Work']);
        $draft = Artwork::factory()->draft()->create(['title' => 'Draft Work']);

        $response = $this->get(route('gallery.index'));

        $response->assertOk();
        $response->assertSee($published->title);
        $response->assertDontSee($draft->title);
    }

    public function test_category_page_filters_artworks(): void
    {
        $category = Category::factory()->create(['name' => 'Oil']);
        $otherCategory = Category::factory()->create(['name' => 'Ceramic']);
        $included = Artwork::factory()->for($category)->create(['title' => 'Included Work']);
        $excluded = Artwork::factory()->for($otherCategory)->create(['title' => 'Excluded Work']);

        $response = $this->get(route('gallery.category', $category));

        $response->assertOk();
        $response->assertSee($included->title);
        $response->assertDontSee($excluded->title);
    }

    public function test_artwork_detail_hides_missing_price(): void
    {
        $artwork = Artwork::factory()->create([
            'title' => 'Priceless Work',
            'price' => null,
        ]);

        $response = $this->get(route('artworks.show', $artwork));

        $response->assertOk();
        $response->assertSee('Priceless Work');
        $response->assertDontSee('₽');
    }

    public function test_order_form_creates_request_and_sends_email(): void
    {
        Mail::fake();

        $artwork = Artwork::factory()->create();

        $response = $this->post(route('orders.store', $artwork), [
            'customer_name' => 'Мария',
            'customer_email' => 'maria@example.com',
            'customer_phone' => '',
            'message' => 'Интересует доставка.',
        ]);

        $response->assertRedirect(route('artworks.show', $artwork));
        $this->assertDatabaseHas('order_requests', [
            'artwork_id' => $artwork->id,
            'customer_name' => 'Мария',
            'customer_email' => 'maria@example.com',
        ]);
        Mail::assertSent(OrderRequestReceived::class);
    }

    public function test_draft_artwork_detail_is_not_public(): void
    {
        $artwork = Artwork::factory()->draft()->create();

        $this->get(route('artworks.show', $artwork))->assertNotFound();
    }
}
