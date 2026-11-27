// The stand-in API: a small server that keeps the promises in contract/README.md.
// It keeps everything in memory (no database), so it resets every time it restarts.
//
// Use it to:
//   - build the web shop and the rep app on day 1, before the real API is ready;
//   - run the contract tests (contract/contract.test.mjs starts it for you);
//   - stand in for the real API if your squad has no backend or full-stack member.
//
// Start it:   node contract/stand-in-api.mjs          -> http://127.0.0.1:8787/api/products
//             PORT=9000 node contract/stand-in-api.mjs   (PowerShell: $env:PORT=9000; node contract/stand-in-api.mjs)
// Products, prices and stock come from the Nkwen practice API. If it cannot be reached,
// or you set DATA_URL=local, the copy in contract/data/nkwen.json is used instead.
// Needs Node.js 22 or newer. No packages to install.
import http from 'node:http';
import { readFileSync } from 'node:fs';
import { randomBytes } from 'node:crypto';
import { pathToFileURL } from 'node:url';

const PRACTICE_API = 'https://innovakickstarter.com/api/course/nkwen';
const PAYMENT_METHODS = ['cash', 'mtn_momo', 'orange_money', 'bank_transfer'];
const CHANNELS = ['web', 'rep_app'];
const STATUSES = ['pay_at_counter', 'awaiting_payment', 'paid', 'delivered', 'cancelled'];
// Which status may follow which (contract section 6).
const NEXT = { pay_at_counter: ['paid', 'cancelled'], awaiting_payment: ['paid', 'cancelled'], paid: ['delivered'], delivered: [], cancelled: [] };
// The demo staff accounts seeded by the Chapter 9 Staff API (StaffSeeder), same emails and password.
const DEMO_PASSWORD = 'Nkwen-Demo-2026';
const DEMO_USERS = [
  { id: 1, email: 'headoffice@nkwen.test', name: 'Head Office Demo', role: 'head_office', branch: null, is_active: true },
  { id: 2, email: 'manager.nkw@nkwen.test', name: 'Nkwen Manager Demo', role: 'branch_manager', branch: 'NKW', is_active: true },
  { id: 3, email: 'cashier.nkw@nkwen.test', name: 'Nkwen Cashier Demo', role: 'cashier', branch: 'NKW', is_active: true },
];
const MOBILE_MONEY = ['mtn_momo', 'orange_money'];
// May this member of staff act on this branch? Head office: every branch. Others: their own (Chapter 9 rules).
const ownsBranch = (user, branch) => user.role === 'head_office' || user.branch === branch;

// Load products and branches from the practice API, or from the local copy.
export async function loadData(source = process.env.DATA_URL || PRACTICE_API) {
  if (source !== 'local') {
    try {
      const get = async (path) => {
        const res = await fetch(source.replace(/\/+$/, '') + path, { headers: { Accept: 'application/json' }, signal: AbortSignal.timeout(8000) });
        if (!res.ok) throw new Error(`${path} answered ${res.status}`);
        return res.json();
      };
      const [p, b] = await Promise.all([get('/products'), get('/branches')]);
      return { products: p.products, branches: b.branches, source };
    } catch (error) {
      console.warn(`Could not load ${source} (${error.message}). Using contract/data/nkwen.json instead.`);
    }
  }
  const file = JSON.parse(readFileSync(new URL('./data/nkwen.json', import.meta.url), 'utf8'));
  return { products: file.products, branches: file.branches, source: 'contract/data/nkwen.json' };
}

export function createStandIn(data) {
  const products = data.products.map((p) => ({ id: p.id, sku: p.sku, name: p.name, category: p.category, unit: p.unit, price_fcfa: p.price_fcfa, currency: 'FCFA', reorder_level: p.reorder_level ?? 0 }));
  const branches = data.branches.map((b) => ({ ...b }));
  const openCodes = branches.filter((b) => b.status === 'open').map((b) => b.code);
  const stock = new Map(); // "CAV:6" -> quantity
  for (const p of data.products) for (const b of branches) stock.set(`${b.code}:${p.id}`, p.stock?.[b.code] ?? 0);
  const orders = new Map(); // id -> order
  const byKey = new Map(); // Idempotency-Key -> order id
  const tokens = new Map(); // token -> user

  const productOut = ({ reorder_level, ...p }) => p;
  const findProduct = (id) => products.find((p) => String(p.id) === String(id));
  const newId = () => 'ORD-' + [...randomBytes(8)].map((n) => 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'[n % 32]).join('');

  function validateOrder(body) {
    const errors = {};
    const add = (field, message) => (errors[field] ??= []).push(message);
    if (!body || typeof body !== 'object') body = {};
    if (!openCodes.includes(body.branch)) add('branch', `The branch must be one of the open branches: ${openCodes.join(', ')}.`);
    const name = typeof body.customer_name === 'string' ? body.customer_name.trim() : '';
    if (!name) add('customer_name', 'The customer name field is required.');
    else if (name.length < 2 || name.length > 100) add('customer_name', 'The customer name field must be between 2 and 100 characters.');
    if (!PAYMENT_METHODS.includes(body.payment_method)) add('payment_method', 'The selected payment method is invalid.');
    if (body.channel !== undefined && !CHANNELS.includes(body.channel)) add('channel', 'The selected channel is invalid.');
    if (!Array.isArray(body.items) || body.items.length === 0) add('items', 'The items field is required.');
    else body.items.forEach((item, i) => {
      if (!Number.isInteger(item?.product_id)) add(`items.${i}.product_id`, `The items.${i}.product_id field must be an integer.`);
      else if (!findProduct(item.product_id)) add(`items.${i}.product_id`, `No product with id ${item.product_id}.`);
      if (!Number.isInteger(item?.quantity)) add(`items.${i}.quantity`, `The items.${i}.quantity field must be an integer.`);
      else if (item.quantity < 1) add(`items.${i}.quantity`, `The items.${i}.quantity field must be at least 1.`);
      else if (item.quantity > 100) add(`items.${i}.quantity`, `The items.${i}.quantity field must not be greater than 100.`);
    });
    return errors;
  }

  function createOrder(body, key) {
    if (key && byKey.has(key)) return [200, { data: orders.get(byKey.get(key)) }];
    const errors = validateOrder(body);
    if (Object.keys(errors).length) return [422, { message: 'The order is not valid.', errors }];
    // Add up each product first: two lines of the same product must not beat the stock check.
    const wanted = new Map();
    for (const item of body.items) wanted.set(item.product_id, (wanted.get(item.product_id) ?? 0) + item.quantity);
    for (const [productId, quantity] of wanted) {
      const available = stock.get(`${body.branch}:${productId}`) ?? 0;
      if (quantity > available) return [409, { message: `Not enough ${findProduct(productId).name} at this branch.`, product_id: productId, requested: quantity, available }];
    }
    for (const [productId, quantity] of wanted) stock.set(`${body.branch}:${productId}`, stock.get(`${body.branch}:${productId}`) - quantity);
    const items = body.items.map(({ product_id, quantity }) => {
      const p = findProduct(product_id);
      return { product_id, name: p.name, quantity, unit_price_fcfa: p.price_fcfa, line_total_fcfa: p.price_fcfa * quantity };
    });
    const order = {
      id: newId(), branch: body.branch, customer_name: body.customer_name.trim(), payment_method: body.payment_method,
      channel: body.channel ?? 'web', status: body.payment_method === 'cash' ? 'pay_at_counter' : 'awaiting_payment',
      items, total_fcfa: items.reduce((sum, i) => sum + i.line_total_fcfa, 0), currency: 'FCFA', created_at: new Date().toISOString(),
    };
    orders.set(order.id, order);
    if (key) byKey.set(key, order.id);
    return [201, { data: order }, { Location: `/api/orders/${order.id}` }];
  }

  function changeStatus(order, user, body) {
    if (!ownsBranch(user, order.branch)) return [403, { message: 'You can only change orders of your own branch.' }];
    const status = body?.status;
    if (!STATUSES.includes(status)) return [422, { message: 'The selected status is invalid.', errors: { status: ['The selected status is invalid.'] } }];
    if (status === 'cancelled' && user.role === 'cashier') return [403, { message: 'Only a branch manager or head office can cancel an order.' }];
    if (!NEXT[order.status].includes(status)) return [409, { message: `An order that is ${order.status} cannot become ${status}.` }];
    if (status === 'paid' && MOBILE_MONEY.includes(order.payment_method)) {
      return [409, { message: 'A mobile-money order becomes paid only when the payment is confirmed by the provider.' }];
    }
    if (status === 'cancelled') for (const item of order.items) stock.set(`${order.branch}:${item.product_id}`, stock.get(`${order.branch}:${item.product_id}`) + item.quantity);
    order.status = status;
    return [200, { data: order }];
  }

  function listOrders(user, query) {
    let branch = query.get('branch');
    if (user.role !== 'head_office') {
      if (branch && branch !== user.branch) return [403, { message: 'You can only see the orders of your own branch.' }];
      branch = user.branch;
    }
    const status = query.get('status');
    const from = query.get('from');
    if (status && !STATUSES.includes(status)) return [422, { message: 'The selected status is invalid.', errors: { status: ['The selected status is invalid.'] } }];
    if (from && Number.isNaN(Date.parse(from))) return [422, { message: 'The from field must be a valid date.', errors: { from: ['The from field must be a valid date.'] } }];
    const list = [...orders.values()]
      .filter((o) => (!branch || o.branch === branch) && (!status || o.status === status) && (!from || o.created_at >= new Date(from).toISOString()))
      .reverse();
    return [200, { data: list, meta: { count: list.length } }];
  }

  function route(method, path, query, body, headers) {
    const user = tokens.get((headers.authorization || '').replace(/^Bearer\s+/i, ''));
    let m;
    if (method === 'GET' && path === '/api/products') {
      let list = products;
      const branch = query.get('branch');
      if (branch && !branches.some((b) => b.code === branch)) return [404, { message: `Unknown branch code "${branch}".` }];
      const category = query.get('category')?.toLowerCase();
      const search = query.get('search')?.toLowerCase();
      if (category) list = list.filter((p) => p.category.toLowerCase() === category);
      if (search) list = list.filter((p) => p.name.toLowerCase().includes(search));
      if (branch) list = list.map((p) => ({ ...p, stock: stock.get(`${branch}:${p.id}`) })).filter((p) => p.stock > 0);
      const sort = query.get('sort');
      if (sort === 'price') list = [...list].sort((a, b) => a.price_fcfa - b.price_fcfa);
      if (sort === '-price') list = [...list].sort((a, b) => b.price_fcfa - a.price_fcfa);
      if (sort === 'name') list = [...list].sort((a, b) => a.name.localeCompare(b.name));
      return [200, { data: list.map(productOut), meta: { count: list.length } }];
    }
    if (method === 'GET' && (m = path.match(/^\/api\/products\/([^/]+)$/))) {
      const p = findProduct(m[1]);
      return p ? [200, { data: productOut(p) }] : [404, { message: `No product with id ${m[1]}.` }];
    }
    if (method === 'GET' && path === '/api/branches') return [200, { data: branches, meta: { count: branches.length } }];
    if (method === 'GET' && (m = path.match(/^\/api\/branches\/([^/]+)\/stock$/))) {
      if (!branches.some((b) => b.code === m[1])) return [404, { message: `Unknown branch code "${m[1]}".` }];
      // A planned branch (Limbe) has no stock rows yet. Status, the Chapter 1 "Restock Alerts" rule:
      // at or below the reorder level = RESTOCK, at or below twice the reorder level = LOW, otherwise OK.
      const open = branches.find((b) => b.code === m[1]).status === 'open';
      const rows = !open ? [] : products.map((p) => {
        const quantity = stock.get(`${m[1]}:${p.id}`);
        const status = quantity <= p.reorder_level ? 'RESTOCK' : quantity <= 2 * p.reorder_level ? 'LOW' : 'OK';
        return { product_id: p.id, branch_code: m[1], sku: p.sku, name: p.name, quantity, reorder_level: p.reorder_level, status };
      });
      return [200, { data: rows, meta: { count: rows.length } }];
    }
    if (method === 'POST' && path === '/api/login') {
      const found = DEMO_USERS.find((u) => u.email === body?.email);
      if (!found || body?.password !== DEMO_PASSWORD) return [401, { message: 'Wrong email or password.' }];
      const token = randomBytes(20).toString('hex');
      tokens.set(token, found);
      const { id, name, email, role, branch, is_active } = found;
      return [200, { token, token_type: 'Bearer', data: { id, name, email, role, branch, is_active } }];
    }
    if (method === 'POST' && path === '/api/logout') {
      if (!user) return [401, { message: 'Unauthenticated.' }];
      tokens.delete((headers.authorization || '').replace(/^Bearer\s+/i, ''));
      return [204, null];
    }
    if (method === 'POST' && path === '/api/orders') {
      const key = headers['idempotency-key'];
      if (key !== undefined && (key.length < 1 || key.length > 100)) {
        return [422, { message: 'The Idempotency-Key header must be 1 to 100 characters.', errors: { 'Idempotency-Key': ['The Idempotency-Key header must be 1 to 100 characters.'] } }];
      }
      // Staff (with a token) follow the Chapter 9 rules: their own branch, or any branch for head office.
      // Customers and reps send no token: guest orders at any open branch (the one T1 addition).
      if (user && openCodes.includes(body?.branch) && !ownsBranch(user, body.branch)) {
        return [403, { message: 'You can only create orders for your own branch.' }];
      }
      return createOrder(body, key);
    }
    if (method === 'GET' && path === '/api/orders') return user ? listOrders(user, query) : [401, { message: 'Unauthenticated.' }];
    if ((m = path.match(/^\/api\/orders\/([^/]+)$/))) {
      const order = orders.get(m[1]);
      if (method === 'GET') return order ? [200, { data: order }] : [404, { message: `No order with id ${m[1]}.` }];
      if (method === 'PATCH') {
        if (!user) return [401, { message: 'Unauthenticated.' }];
        return order ? changeStatus(order, user, body) : [404, { message: `No order with id ${m[1]}.` }];
      }
      return [405, { message: `The ${method} method is not supported for this route. Orders are cancelled, never deleted.` }];
    }
    return [404, { message: `No route for ${method} ${path}.` }];
  }

  return http.createServer(async (req, res) => {
    const cors = {
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PATCH, OPTIONS',
      'Access-Control-Allow-Headers': 'Accept, Content-Type, Authorization, Idempotency-Key',
      'Access-Control-Expose-Headers': 'Location',
    };
    const send = (status, payload, extra = {}) => {
      res.writeHead(status, { ...cors, ...(payload === null ? {} : { 'Content-Type': 'application/json' }), ...extra });
      res.end(payload === null ? undefined : JSON.stringify(payload));
    };
    if (req.method === 'OPTIONS') return send(204, null);
    const url = new URL(req.url, 'http://stand-in');
    // Teaching switches, as on the practice API: ?delay=1500 (max 3000 ms) and ?fail=404|500|503.
    const delay = Math.min(Number(url.searchParams.get('delay')) || 0, 3000);
    if (delay > 0) await new Promise((r) => setTimeout(r, delay));
    const fail = Number(url.searchParams.get('fail'));
    if ([404, 500, 503].includes(fail)) return send(fail, { message: `Practice failure: status ${fail} (you asked for it with ?fail=${fail}).` });
    let raw = '';
    for await (const chunk of req) raw += chunk;
    let body = null;
    if (raw.trim()) {
      try { body = JSON.parse(raw); } catch { return send(400, { message: 'The request body is not valid JSON.' }); }
    }
    try {
      const [status, payload, extra] = route(req.method, url.pathname.replace(/\/+$/, '') || '/', url.searchParams, body, req.headers);
      send(status, payload, extra);
    } catch (error) {
      console.error(error);
      send(500, { message: 'The stand-in API crashed. See its terminal for details.' });
    }
  });
}

// Run directly: node contract/stand-in-api.mjs
if (import.meta.url === pathToFileURL(process.argv[1] ?? '').href) {
  const data = await loadData();
  const port = Number(process.env.PORT) || 8787;
  createStandIn(data).listen(port, () => {
    console.log(`Stand-in API on http://127.0.0.1:${port}/api/products (data from ${data.source}).`);
    console.log(`Demo logins: ${DEMO_USERS.map((u) => u.email).join(', ')} / ${DEMO_PASSWORD}`);
    console.log('Everything is kept in memory and resets when you stop it (Ctrl+C).');
  });
}
