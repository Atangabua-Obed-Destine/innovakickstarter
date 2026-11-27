<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

// Who may do what with an order. Laravel finds this class by its name: Order -> OrderPolicy.
// Each method answers Response::allow() or Response::deny('a clear reason') (403).
// Tip: add worksAt(string $branchCode) to the User model: true for head office, or for staff of that branch.
class OrderPolicy
{
    public function view(User $user, Order $order): Response
    {
        // TODO: head office sees every order; other staff only their own branch's orders.
        return Response::deny('Not written yet.');
    }

    public function create(User $user, string $branchCode): Response
    {
        // TODO: staff sell at their own branch; head office may sell for any branch.
        return Response::deny('Not written yet.');
    }

    public function changeStatus(User $user, Order $order, string $newStatus): Response
    {
        // TODO: staff of the order's branch (or head office) may change its status,
        //       but only a branch manager or head office may cancel.
        return Response::deny('Not written yet.');
    }

    public function requestPayment(User $user, Order $order): Response
    {
        // TODO: the same people who can view the order.
        return Response::deny('Not written yet.');
    }
}
