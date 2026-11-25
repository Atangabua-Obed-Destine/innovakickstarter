<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * My own feature tests for the Nkwen Products API ("Testing Your API").
 * Run: php artisan test --filter=ProductApiTest
 *
 * Each test follows three steps:
 *   Arrange - get the data ready (here: $seed fills the products table),
 *   Act     - send one request,
 *   Assert  - check the status code and the JSON.
 * Replace every $this->fail('TODO ...') with real code. Use nkwen.json to find the right values.
 */
class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    // Run DatabaseSeeder before each test, so the 20 products are there.
    protected $seed = true;

    // A finished example. Read it, then write the others in the same way.
    public function test_hello_says_hello(): void
    {
        $response = $this->getJson('/api/hello');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Hello from Nkwen Traders!']);
    }

    public function test_status_reports_ok(): void
    {
        // TODO: GET /api/status. Assert 200, and that "status" is "ok" and "service" is "Nkwen Products API".
        $this->fail('TODO: write test_status_reports_ok');
    }

    public function test_rice_50kg_has_the_price_from_the_price_list(): void
    {
        // TODO: GET the Rice 50kg product by its id (look it up in database/data/nkwen.json).
        // Assert 200, its name and its exact price_fcfa with assertJsonPath('data.price_fcfa', ...).
        $this->fail('TODO: write test_rice_50kg_has_the_price_from_the_price_list');
    }

    public function test_grains_filter_returns_only_the_grains(): void
    {
        // TODO: GET /api/products?category=Grains. Count the Grains products in nkwen.json yourself.
        // Assert 200, assertJsonCount(<your count>, 'data'), meta.count, and the name of the first one.
        $this->fail('TODO: write test_grains_filter_returns_only_the_grains');
    }

    public function test_an_unknown_id_is_a_404_that_names_the_id(): void
    {
        // TODO: GET /api/products/777. Assert 404 and the exact message your API sends.
        $this->fail('TODO: write test_an_unknown_id_is_a_404_that_names_the_id');
    }

    // ---- Step 8: write these three BEFORE you build ?search= (red), then build it (green). ----

    public function test_search_finds_products_by_part_of_the_name_in_any_letter_case(): void
    {
        // TODO: GET /api/products?search=RICE (capital letters on purpose).
        // Assert 200, the number of products whose name contains "rice", and the first two names.
        $this->fail('TODO: write test_search_finds_products_by_part_of_the_name_in_any_letter_case');
    }

    public function test_search_with_no_match_returns_an_empty_list_not_an_error(): void
    {
        // TODO: GET /api/products?search=laptop. Assert 200, an empty "data" list and meta.count 0.
        $this->fail('TODO: write test_search_with_no_match_returns_an_empty_list_not_an_error');
    }

    public function test_search_and_category_work_together(): void
    {
        // TODO: GET /api/products?category=Oils&search=palm. Assert 200, the count and the first name.
        $this->fail('TODO: write test_search_and_category_work_together');
    }
}
