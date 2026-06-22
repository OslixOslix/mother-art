<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/categories')
            ->assertOk();

        $this->actingAs($user)
            ->get('/admin/artworks/create')
            ->assertOk();

        $this->actingAs($user)
            ->get('/admin/order-requests')
            ->assertOk();

        $this->actingAs($user)
            ->get('/admin/import-artworks')
            ->assertOk();
    }
}
