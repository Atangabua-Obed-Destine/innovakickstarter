<?php

namespace App\Http\Controllers\CourseApi;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Nkwen Traders course API: a small public, read-only API that Software Engineering
 * lessons call with fetch() (catalogue, branches, categories), plus a practice
 * "orders" endpoint that validates and prices an order but never saves anything.
 *
 * Teaching switches on every endpoint: ?delay=1500 (milliseconds, max 3000) to see
 * loading states, ?fail=404|500|503 to practise error handling.
 * Data: database/data/se-course/nkwen.json (public practice data).
 */
class NkwenController extends Controller
{
    private function data(): array
    {
        $file = database_path('data/se-course/nkwen.json');

        return Cache::remember('nkwen-course:' . filemtime($file), now()->addDay(), fn () => json_decode((string) file_get_contents($file), true));
    }

    /** Apply ?delay and ?fail. Returns an error response when ?fail is set. */
    private function switches(Request $request): ?JsonResponse
    {
        $delay = (int) $request->query('delay', 0);
        if ($delay > 0) {
            usleep(min($delay, 3000) * 1000);
        }
        $fail = (int) $request->query('fail', 0);

        return match ($fail) {
            404 => response()->json(['message' => 'Not found (practice error from ?fail=404).'], 404),
            500 => response()->json(['message' => 'Server error (practice error from ?fail=500).'], 500),
            503 => response()->json(['message' => 'Service unavailable, try again later (practice error from ?fail=503).'], 503),
            default => null,
        };
    }

    public function index(Request $request): JsonResponse
    {
        if ($r = $this->switches($request)) {
            return $r;
        }
        $base = url('/api/course/nkwen');

        return response()->json([
            'name' => 'Nkwen Traders practice API',
            'about' => 'Practice data for I-NNOVA Kickstarter lessons. Read-only, except POST /orders, which checks and prices an order but never saves it.',
            'endpoints' => [
                'GET ' . $base . '/products' => 'All products. Filters: ?category=Grains, ?search=rice, ?branch=NKW (only products in stock there), ?sort=price|-price|name',
                'GET ' . $base . '/products/{id}' => 'One product',
                'GET ' . $base . '/categories' => 'Product categories',
                'GET ' . $base . '/branches' => 'Branches (one is still planned)',
                'GET ' . $base . '/legacy/price-list' => 'An old, messy price list (for cleaning practice)',
                'POST ' . $base . '/login' => 'Practice staff login: {"email":"manager.nkw@nkwen.test","password":"Nkwen-Demo-2026"} -> {token, data}. Also headoffice@ and cashier.nkw@',
                'GET ' . $base . '/me' => 'Who you are (send Authorization: Bearer <token>)',
                'GET ' . $base . '/branches/{code}/stock' => 'Stock with OK/LOW/RESTOCK status. Staff only (Bearer token); your own branch unless head office',
                'POST ' . $base . '/orders' => 'Check and price an order: {"branch":"NKW","customer_name":"...","payment_method":"mtn_momo","items":[{"product_id":1,"quantity":2}]}',
            ],
            'switches' => ['?delay=1500' => 'wait 1.5 s before answering (max 3000)', '?fail=404|500|503' => 'answer with that error'],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        if ($r = $this->switches($request)) {
            return $r;
        }
        $d = $this->data();
        $items = collect($d['products']);
        if ($c = trim((string) $request->query('category', ''))) {
            $items = $items->filter(fn ($p) => strcasecmp($p['category'], $c) === 0);
        }
        if ($s = trim((string) $request->query('search', ''))) {
            $items = $items->filter(fn ($p) => str_contains(mb_strtolower($p['name']), mb_strtolower($s)));
        }
        if ($b = strtoupper(trim((string) $request->query('branch', '')))) {
            if (!collect($d['branches'])->contains('code', $b)) {
                return response()->json(['message' => "Unknown branch code \"{$b}\". See /branches."], 404);
            }
            $items = $items->filter(fn ($p) => ($p['stock'][$b] ?? 0) > 0);
        }
        $items = match ((string) $request->query('sort', '')) {
            'price' => $items->sortBy('price_fcfa'),
            '-price' => $items->sortByDesc('price_fcfa'),
            'name' => $items->sortBy('name'),
            default => $items,
        };

        return response()->json(['count' => $items->count(), 'currency' => 'FCFA', 'products' => $items->values()]);
    }

    public function product(Request $request, string $id): JsonResponse
    {
        if ($r = $this->switches($request)) {
            return $r;
        }
        $p = collect($this->data()['products'])->firstWhere('id', (int) $id);

        return $p && ctype_digit($id) ? response()->json($p) : response()->json(['message' => "No product with id {$id}."], 404);
    }

    public function categories(Request $request): JsonResponse
    {
        return $this->switches($request) ?? response()->json(['categories' => $this->data()['categories']]);
    }

    public function branches(Request $request): JsonResponse
    {
        return $this->switches($request) ?? response()->json(['branches' => $this->data()['branches']]);
    }

    public function legacyPriceList(Request $request): JsonResponse
    {
        return $this->switches($request) ?? response()->json(['exported_from' => 'old till system (2019)', 'rows' => $this->data()['legacy_price_list']]);
    }

    /** Practice staff accounts (same as the Backend pathway's B3 demo accounts). Password: Nkwen-Demo-2026. */
    private const STAFF = [
        'headoffice@nkwen.test' => ['id' => 1, 'name' => 'Head Office', 'role' => 'head_office', 'branch' => null],
        'manager.nkw@nkwen.test' => ['id' => 2, 'name' => 'Mrs. Enow Patience', 'role' => 'branch_manager', 'branch' => 'NKW'],
        'cashier.nkw@nkwen.test' => ['id' => 3, 'name' => 'Nkwen Cashier', 'role' => 'cashier', 'branch' => 'NKW'],
    ];
    private const STAFF_PASSWORD = 'Nkwen-Demo-2026';

    private function tokenFor(string $email): string
    {
        $payload = rtrim(strtr(base64_encode(json_encode(['e' => $email, 'x' => now()->addHours(8)->timestamp])), '+/', '-_'), '=');

        return $payload . '.' . hash_hmac('sha256', $payload, (string) config('app.key'));
    }

    /** The staff member behind a Bearer token, or null. Tokens are signed and expire after 8 hours. */
    private function staffFrom(Request $request): ?array
    {
        $token = (string) $request->bearerToken();
        if (!str_contains($token, '.')) {
            return null;
        }
        [$payload, $sig] = explode('.', $token, 2);
        if (!hash_equals(hash_hmac('sha256', $payload, (string) config('app.key')), $sig)) {
            return null;
        }
        $data = json_decode((string) base64_decode(strtr($payload, '-_', '+/')), true);
        if (!is_array($data) || ($data['x'] ?? 0) < now()->timestamp || !isset(self::STAFF[$data['e'] ?? ''])) {
            return null;
        }

        return ['email' => $data['e']] + self::STAFF[$data['e']];
    }

    /** POST /login: practice staff login, same shape as the Backend pathway (B3). */
    public function login(Request $request): JsonResponse
    {
        if ($r = $this->switches($request)) {
            return $r;
        }
        $email = strtolower(trim((string) $request->input('email')));
        if (!isset(self::STAFF[$email]) || !hash_equals(self::STAFF_PASSWORD, (string) $request->input('password'))) {
            return response()->json(['message' => 'These credentials do not match our records.'], 401);
        }

        return response()->json(['token' => $this->tokenFor($email), 'token_type' => 'Bearer', 'data' => ['email' => $email] + self::STAFF[$email]]);
    }

    /** GET /me: who the token belongs to. */
    public function me(Request $request): JsonResponse
    {
        if ($r = $this->switches($request)) {
            return $r;
        }
        $staff = $this->staffFrom($request);

        return $staff ? response()->json(['data' => $staff]) : response()->json(['message' => 'Unauthenticated.'], 401);
    }

    /** GET /branches/{code}/stock: staff only; managers and cashiers see their own branch, head office all. */
    public function branchStock(Request $request, string $code): JsonResponse
    {
        if ($r = $this->switches($request)) {
            return $r;
        }
        $staff = $this->staffFrom($request);
        if (!$staff) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        $code = strtoupper($code);
        $branch = collect($this->data()['branches'])->firstWhere('code', $code);
        if (!$branch) {
            return response()->json(['message' => "Unknown branch code \"{$code}\"."], 404);
        }
        if ($staff['role'] !== 'head_office' && $staff['branch'] !== $code) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }
        $rows = collect($this->data()['products'])->filter(fn ($p) => isset($p['stock'][$code]))->map(function ($p) use ($code) {
            $q = $p['stock'][$code];
            $level = (int) ($p['reorder_level'] ?? 0);

            return ['product_id' => $p['id'], 'branch_code' => $code, 'sku' => $p['sku'], 'name' => $p['name'], 'quantity' => $q, 'reorder_level' => $level,
                'status' => $q <= $level ? 'RESTOCK' : ($q <= 2 * $level ? 'LOW' : 'OK')];
        })->values();

        return response()->json(['data' => $rows, 'meta' => ['count' => $rows->count()]]);
    }

    /** Validate and price an order. Nothing is stored and stock is not changed. */
    public function order(Request $request): JsonResponse
    {
        if ($r = $this->switches($request)) {
            return $r;
        }
        // Idempotency-Key (as in ORDERS_API_REFERENCE): repeating a request with the same key returns the
        // first order instead of a new one, so a retried or double-tapped order is never created twice.
        $idemKey = trim((string) $request->header('Idempotency-Key', ''));
        $bodyHash = sha1((string) json_encode($request->all()));
        if ($idemKey !== '') {
            if (mb_strlen($idemKey) > 100) {
                return response()->json(['message' => 'The Idempotency-Key header must be at most 100 characters.'], 422);
            }
            $seen = Cache::get('nkwen-idem:' . sha1($idemKey));
            if ($seen) {
                return $seen['body_hash'] === $bodyHash
                    ? response()->json($seen['response'], 201)->header('Idempotent-Replayed', 'true')
                    : response()->json(['message' => 'This Idempotency-Key was already used for a different order.'], 422);
            }
        }
        $d = $this->data();
        $open = collect($d['branches'])->where('status', 'open')->pluck('code')->all();
        $v = Validator::make($request->all(), [
            'branch' => ['required', 'string', 'in:' . implode(',', $open)],
            'customer_name' => ['required', 'string', 'min:2', 'max:80'],
            'payment_method' => ['required', 'in:' . implode(',', $d['payment_methods'])],
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ], [
            'branch.in' => 'The branch must be one of the open branches: ' . implode(', ', $open) . '.',
        ]);
        if ($v->fails()) {
            return response()->json(['message' => 'The order is not valid.', 'errors' => $v->errors()], 422);
        }
        $products = collect($d['products'])->keyBy('id');
        foreach ($request->input('items') as $i => $item) {
            if (!$products->has((int) $item['product_id'])) {
                return response()->json(['message' => 'The order is not valid.', 'errors' => ["items.{$i}.product_id" => ["No product with id {$item['product_id']}."]]], 422);
            }
        }
        // Stock is checked per product over the whole order: two lines of the same product are added up.
        $wanted = collect($request->input('items'))->groupBy(fn ($it) => (int) $it['product_id'])->map(fn ($g) => $g->sum(fn ($it) => (int) $it['quantity']));
        foreach ($wanted as $productId => $qty) {
            $p = $products->get($productId);
            $available = $p['stock'][$request->input('branch')] ?? 0;
            if ($qty > $available) {
                return response()->json(['message' => "Not enough {$p['name']} at this branch.", 'product_id' => $p['id'], 'requested' => $qty, 'available' => $available], 409);
            }
        }
        $lines = [];
        foreach ($request->input('items') as $item) {
            $p = $products->get((int) $item['product_id']);
            $lines[] = ['product_id' => $p['id'], 'name' => $p['name'], 'quantity' => (int) $item['quantity'], 'unit_price_fcfa' => $p['price_fcfa'], 'line_total_fcfa' => $p['price_fcfa'] * (int) $item['quantity']];
        }

        $response = ['order' => [
            'id' => 'ORD-' . strtoupper(Str::random(8)),
            'branch' => $request->input('branch'),
            'customer_name' => $request->input('customer_name'),
            'items' => $lines,
            'total_fcfa' => array_sum(array_column($lines, 'line_total_fcfa')),
            'payment_method' => $request->input('payment_method'),
            'status' => $request->input('payment_method') === 'cash' ? 'pay_at_counter' : 'awaiting_payment',
            'created_at' => now()->toIso8601String(),
            'saved' => false,
        ], 'message' => 'Order checked and priced. This is a practice API: nothing was saved.'];
        if ($idemKey !== '') {
            Cache::put('nkwen-idem:' . sha1($idemKey), ['body_hash' => $bodyHash, 'response' => $response], now()->addDay());
        }

        return response()->json($response, 201);
    }
}
