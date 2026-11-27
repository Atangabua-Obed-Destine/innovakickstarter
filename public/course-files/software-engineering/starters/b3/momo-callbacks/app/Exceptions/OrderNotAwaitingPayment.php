<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

// The order is cash, already paid or cancelled: it conflicts with a mobile-money request (409).
class OrderNotAwaitingPayment extends RuntimeException
{
    public function __construct(public string $status)
    {
        parent::__construct('This order is not waiting for a mobile-money payment.');
    }

    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage(), 'status' => $this->status], 409);
    }
}
