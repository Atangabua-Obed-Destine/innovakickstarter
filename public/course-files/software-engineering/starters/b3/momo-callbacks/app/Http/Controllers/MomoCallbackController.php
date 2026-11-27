<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// POST /api/payments/momo/callback: the payment provider tells us how a payment ended.
// The body looks like MTN MoMo's status answer, plus the referenceId we chose:
// {"referenceId":"<uuid>","amount":"78610","currency":"XAF","financialTransactionId":"...","status":"SUCCESSFUL"}
// The header X-Nkwen-Signature is "sha256=" + HMAC-SHA256 of the raw body with our shared secret.
class MomoCallbackController extends Controller
{
    public function handle(Request $request)
    {
        // TODO 1: read the secret from config('services.momo.webhook_secret').
        //         If it is empty, log an error and answer 503: never accept callbacks without a secret.

        // TODO 2: compute 'sha256='.hash_hmac('sha256', $request->getContent(), $secret)
        //         and compare it with the X-Nkwen-Signature header using hash_equals().
        //         If they differ: log a warning and answer 401 {"message": "Invalid signature."}

        // TODO 3: validate the body: referenceId (uuid), status (SUCCESSFUL or FAILED), amount (numeric), currency.

        // TODO 4: inside DB::transaction, find the payment by reference WITH lockForUpdate().
        //         - not found: 404
        //         - already successful or failed: answer 200 "Already processed." and change NOTHING (idempotent)
        //         - amount or currency (XAF) does not match: 422, change nothing
        //         - otherwise: save the new status, the financialTransactionId and completed_at;
        //           if SUCCESSFUL, set the order's status to 'paid'. Answer 200.

        return response()->json(['message' => 'Not written yet.'], 501);
    }
}
