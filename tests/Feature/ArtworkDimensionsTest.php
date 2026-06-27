<?php

namespace Tests\Feature;

use App\Models\Artwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtworkDimensionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_detail_page_shows_both_dimensions_when_provided(): void
    {
        $artwork = Artwork::factory()->create([
            'title' => 'Sized Work',
            'width_cm' => 50,
            'height_cm' => 70,
        ]);

        $response = $this->get(route('artworks.show', $artwork));

        $response->assertOk();
        $response->assertSee('70 × 50 см', false);
    }

    public function test_detail_page_shows_single_dimension_when_only_height_provided(): void
    {
        $artwork = Artwork::factory()->create([
            'title' => 'Tall Work',
            'width_cm' => null,
            'height_cm' => 120,
        ]);

        $response = $this->get(route('artworks.show', $artwork));

        $response->assertOk();
        $response->assertSee('120 см', false);
        $response->assertDontSee('×', false);
    }

    public function test_detail_page_shows_single_dimension_when_only_width_provided(): void
    {
        $artwork = Artwork::factory()->create([
            'title' => 'Wide Work',
            'width_cm' => 80,
            'height_cm' => null,
        ]);

        $response = $this->get(route('artworks.show', $artwork));

        $response->assertOk();
        $response->assertSee('80 см', false);
    }

    public function test_detail_page_hides_dimensions_when_not_provided(): void
    {
        $artwork = Artwork::factory()->create([
            'title' => 'Untyped Work',
            'width_cm' => null,
            'height_cm' => null,
        ]);

        $response = $this->get(route('artworks.show', $artwork));

        $response->assertOk();
        $response->assertDontSee('см');
    }
}
