<?php

namespace App\Exceptions;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class NotEnoughStock extends RuntimeException
{
    public function __construct(public int $productId, public int $requested, public int $available)
    {
        $name = Product::find($productId)?->name ?? "product {$productId}";
        parent::__construct("Not enough {$name} at this branch.");
    }

    // Laravel calls render() for us, so every route answers "not enough stock" the same way: 409.
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'product_id' => $this->productId,
            'requested' => $this->requested,
            'available' => $this->available,
        ], 409);
    }
}
