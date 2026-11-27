<?php

namespace App\Http\Controllers;

use App\Models\Branch;

class StockController extends Controller
{
    // GET /api/branches/{code}/stock
    public function branchStock(string $code)
    {
        // TODO 1: find the branch by code; if there is none, answer 404 {"message": "No branch with code ..."}.
        // TODO 2: load its stock levels WITH their products in one go: ->with('product'), ordered by product_id.
        // TODO 3: answer {"data": [...], "meta": {"count": n}} with, for each row:
        //         product_id, branch_code, sku, name, quantity, reorder_level and status:
        //         "RESTOCK" if quantity is at or below the reorder level,
        //         "LOW" if at or below twice the reorder level, otherwise "OK" (Restock Alerts, Chapter 1).
    }
}
