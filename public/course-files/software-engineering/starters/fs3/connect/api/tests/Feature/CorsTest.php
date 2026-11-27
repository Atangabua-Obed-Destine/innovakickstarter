<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CORS: which websites may call the API from a browser.
 * The browser sends "Origin: <the site's address>" and only reads the answer
 * if the API sends back Access-Control-Allow-Origin with the same address.
 * These tests use FRONTEND_URL=http://localhost:5173 (see phpunit.xml step in the README).
 */
class CorsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_the_shop_on_localhost_5173_is_allowed(): void
    {
        $response = $this->withHeaders(['Origin' => 'http://localhost:5173'])->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }

    public function test_another_website_is_not_allowed(): void
    {
        $response = $this->withHeaders(['Origin' => 'https://fake-nkwen-shop.example'])->getJson('/api/products');

        // The API still answers (CORS is the browser's rule). But the header does not name this website,
        // so the browser hides the answer from it. (With only one allowed site, Laravel always sends that site.)
        $response->assertStatus(200);
        $allowed = $response->headers->get('Access-Control-Allow-Origin');
        $this->assertNotContains(
            $allowed,
            ['*', 'https://fake-nkwen-shop.example'],
            'Any website may read your API. In config/cors.php, replace "*" with the FRONTEND_URL list.'
        );
    }

    public function test_the_browser_may_send_a_token_with_a_post(): void
    {
        // Before a POST with JSON and an Authorization header, the browser first asks with OPTIONS (a "preflight").
        $response = $this->call('OPTIONS', '/api/orders', [], [], [], [
            'HTTP_ORIGIN' => 'http://localhost:5173',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'authorization, content-type',
        ]);

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
        $this->assertStringContainsStringIgnoringCase('authorization', (string) $response->headers->get('Access-Control-Allow-Headers'));
    }
}
