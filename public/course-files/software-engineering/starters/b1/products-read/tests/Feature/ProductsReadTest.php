<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Self-check for "Routes, Controllers & JSON Responses".
 * Run: php artisan test --filter=ProductsReadTest
 * These tests compare your API with database/data/nkwen.json. They do not tell you how to write the code.
 */
class ProductsReadTest extends TestCase
{
    use RefreshDatabase;

    // Fill the empty test database by running DatabaseSeeder (which calls ProductSeeder).
    protected $seed = true;

    private function dataFile(): array
    {
        return json_decode(file_get_contents(database_path('data/nkwen.json')), true);
    }

    public function test_the_seeder_filled_the_products_table(): void
    {
        $expected = count($this->dataFile()['products']);

        $this->assertSame($expected, DB::table('products')->count(), 'The products table should hold every product from nkwen.json. Does DatabaseSeeder call ProductSeeder?');
    }

    public function test_list_returns_every_product_in_the_data_file(): void
    {
        $expected = count($this->dataFile()['products']);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonCount($expected, 'data');
        $response->assertJsonPath('meta.count', $expected);
    }

    public function test_each_product_has_the_public_shape_and_no_timestamps(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertJsonStructure([
            'data' => [['id', 'sku', 'name', 'category', 'unit', 'price_fcfa', 'currency']],
            'meta' => ['count'],
        ]);
        $response->assertJsonPath('data.0.currency', 'FCFA');
        $response->assertJsonMissingPath('data.0.created_at');
        $this->assertIsInt($response->json('data.0.price_fcfa'), 'price_fcfa must be a number, not text.');
    }

    public function test_category_filter_keeps_only_that_category(): void
    {
        $products = $this->dataFile()['products'];

        foreach ($this->dataFile()['categories'] as $category) {
            $expected = count(array_filter($products, fn ($p) => $p['category'] === $category));

            $response = $this->getJson('/api/products?category=' . $category);

            $response->assertStatus(200);
            $this->assertCount($expected, $response->json('data'), "?category={$category} should return only {$category} products.");
            foreach ($response->json('data') as $item) {
                $this->assertSame($category, $item['category']);
            }
        }
    }

    public function test_show_returns_the_same_product_as_the_data_file(): void
    {
        $first = $this->dataFile()['products'][0];

        $response = $this->getJson('/api/products/' . $first['id']);

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $first['id']);
        $response->assertJsonPath('data.name', $first['name']);
        $response->assertJsonPath('data.price_fcfa', $first['price_fcfa']);
    }

    public function test_a_missing_product_is_a_404_with_a_message(): void
    {
        $response = $this->getJson('/api/products/999999');

        $response->assertStatus(404);
        $response->assertJsonStructure(['message']);
    }
}
