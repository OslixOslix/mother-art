<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustedProxiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_vite_assets_use_https_when_behind_tls_terminating_proxy(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Proto' => 'https',
            'X-Forwarded-For' => '203.0.113.1',
            'X-Forwarded-Host' => 'mother-art-production.up.railway.app',
        ])->get(route('home'));

        $response->assertOk();
        $response->assertSee('https://mother-art-production.up.railway.app/build/assets/', false);
        $response->assertDontSee('http://mother-art-production.up.railway.app/build/assets/', false);
    }
}
