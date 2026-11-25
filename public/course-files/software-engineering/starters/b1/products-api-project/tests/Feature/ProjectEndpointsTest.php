<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the project endpoints: update, delete and branches.
 * Run: php artisan test --filter=ProjectEndpointsTest
 * Replace every $this->fail('TODO ...') with a real test. Take the values from database/data/nkwen.json.
 * You may add more tests. Every endpoint needs at least one "it works" test and one "it refuses" test.
 */
class ProjectEndpointsTest extends TestCase
{
    use RefreshDatabase;

    // Run DatabaseSeeder (products AND branches) before each test.
    protected $seed = true;

    public function test_patch_changes_only_the_fields_sent(): void
    {
        // TODO: PATCH /api/products/9 with a new price only. Assert 200, the new price,
        // that the name did not change, and assertDatabaseHas('products', [...]).
        $this->fail('TODO: write test_patch_changes_only_the_fields_sent');
    }

    public function test_put_can_change_several_fields(): void
    {
        // TODO: PUT /api/products/13 with a new name and price. Assert 200 and both new values.
        $this->fail('TODO: write test_put_can_change_several_fields');
    }

    public function test_update_rejects_bad_data_and_keeps_the_old_price(): void
    {
        // TODO: PATCH a price of 0. Assert 422, an error for price_fcfa, and that the database still has the old price.
        $this->fail('TODO: write test_update_rejects_bad_data_and_keeps_the_old_price');
    }

    public function test_update_cannot_take_another_products_sku(): void
    {
        // TODO: PATCH product 9 with the SKU of product 6. Assert 422 and an error for sku.
        $this->fail('TODO: write test_update_cannot_take_another_products_sku');
    }

    public function test_update_can_keep_its_own_sku(): void
    {
        // TODO: PATCH product 9 with ITS OWN sku and a new price. Assert 200 (a product may keep its own SKU).
        $this->fail('TODO: write test_update_can_keep_its_own_sku');
    }

    public function test_updating_a_missing_product_is_a_404(): void
    {
        // TODO
        $this->fail('TODO: write test_updating_a_missing_product_is_a_404');
    }

    public function test_delete_removes_the_product(): void
    {
        // TODO: DELETE /api/products/12. Assert 204 with no content, then that GET gives 404,
        // and that the list count went down by one.
        $this->fail('TODO: write test_delete_removes_the_product');
    }

    public function test_deleting_a_missing_product_is_a_404(): void
    {
        // TODO
        $this->fail('TODO: write test_deleting_a_missing_product_is_a_404');
    }

    public function test_branches_lists_all_five_in_file_order(): void
    {
        // TODO: GET /api/branches. Assert 200, the count, the first code and its manager, and no created_at.
        $this->fail('TODO: write test_branches_lists_all_five_in_file_order');
    }

    public function test_limbe_is_listed_as_planned_not_open(): void
    {
        // TODO: assert that LMB is last with status "planned", and count the open branches.
        $this->fail('TODO: write test_limbe_is_listed_as_planned_not_open');
    }
}
