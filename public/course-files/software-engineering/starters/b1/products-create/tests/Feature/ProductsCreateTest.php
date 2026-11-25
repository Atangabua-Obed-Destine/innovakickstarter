<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Self-check for "Handling POST Requests: Validation & Middleware".
 * Run: php artisan test --filter=ProductsCreateTest
 */
class ProductsCreateTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function validProduct(): array
    {
        return [
            'sku' => 'NKT-GRO-021',
            'name' => 'Garri 1kg',
            'category' => 'Groceries',
            'unit' => 'bag',
            'price_fcfa' => 650,
        ];
    }

    public function test_a_valid_product_is_created_with_201_and_can_be_read_back(): void
    {
        $response = $this->postJson('/api/products', $this->validProduct());

        $response->assertStatus(201);
        $response->assertJsonPath('data.name', 'Garri 1kg');
        $response->assertJsonPath('data.currency', 'FCFA');
        $id = $response->json('data.id');
        $this->assertNotNull($id, 'The 201 response should include the new id in data.id.');

        $this->getJson("/api/products/{$id}")->assertStatus(200)->assertJsonPath('data.sku', 'NKT-GRO-021');
        $this->assertDatabaseHas('products', ['sku' => 'NKT-GRO-021', 'price_fcfa' => 650]);
    }

    public function test_an_empty_body_is_a_422_with_an_error_for_every_field(): void
    {
        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['sku', 'name', 'category', 'unit', 'price_fcfa']);
    }

    public function test_an_unknown_category_is_rejected(): void
    {
        $product = array_merge($this->validProduct(), ['category' => 'Electronics']);

        $this->postJson('/api/products', $product)->assertStatus(422)->assertJsonValidationErrors(['category']);
    }

    public function test_a_price_must_be_a_whole_number_above_zero(): void
    {
        foreach ([0, -500, 'cheap', 650.5] as $badPrice) {
            $product = array_merge($this->validProduct(), ['price_fcfa' => $badPrice]);

            $this->postJson('/api/products', $product)->assertStatus(422)->assertJsonValidationErrors(['price_fcfa']);
        }
    }

    public function test_a_sku_that_already_exists_is_rejected(): void
    {
        $existingSku = json_decode(file_get_contents(database_path('data/nkwen.json')), true)['products'][0]['sku'];
        $product = array_merge($this->validProduct(), ['sku' => $existingSku]);

        $this->postJson('/api/products', $product)->assertStatus(422)->assertJsonValidationErrors(['sku']);
    }

    public function test_nothing_is_saved_when_validation_fails(): void
    {
        $before = $this->getJson('/api/products')->json('meta.count');

        $this->postJson('/api/products', ['name' => 'No price']);

        $this->assertSame($before, $this->getJson('/api/products')->json('meta.count'));
    }

    public function test_errors_are_json_even_when_the_client_forgets_the_accept_header(): void
    {
        // post() (not postJson) sends a plain form, like a careless client. Your middleware should still give JSON.
        $response = $this->post('/api/products', []);

        $response->assertStatus(422);
        $response->assertHeader('Content-Type', 'application/json');
    }
}
