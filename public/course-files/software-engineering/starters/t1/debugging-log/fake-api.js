// A pretend Orders API that lives in memory. It keeps the squad contract's rules:
// stock per branch, 409 when stock is short, 422 for a bad order, and the Idempotency-Key.
// It can also LOSE answers: the order is saved, but the phone never hears back,
// like on a weak network at the market. You do not need to change this file.
const PRODUCTS = { 6: { name: 'Rice 50kg', price: 36005 }, 9: { name: 'Sugar 1kg', price: 760 } };

function createFakeApi({ stock = { 'BAF:6': 21, 'BAF:9': 42 }, loseAnswers = [], networkDown = false } = {}) {
  const orders = [];
  const byKey = new Map();
  let calls = 0;

  function handle(order, key) {
    if (key && byKey.has(key)) return { status: 200, body: { data: byKey.get(key) } };
    if (!order.customer_name || order.customer_name.trim().length < 2) {
      return { status: 422, body: { message: 'The order is not valid.', errors: { customer_name: ['The customer name field is required.'] } } };
    }
    for (const item of order.items) {
      const available = stock[`${order.branch}:${item.product_id}`] ?? 0;
      if (item.quantity > available) {
        return { status: 409, body: { message: `Not enough ${PRODUCTS[item.product_id].name} at this branch.`, product_id: item.product_id, requested: item.quantity, available } };
      }
    }
    for (const item of order.items) stock[`${order.branch}:${item.product_id}`] -= item.quantity;
    const items = order.items.map((i) => ({ ...i, name: PRODUCTS[i.product_id].name, line_total_fcfa: PRODUCTS[i.product_id].price * i.quantity }));
    const saved = {
      id: `ORD-${String(orders.length + 1).padStart(8, '0')}`,
      branch: order.branch,
      customer_name: order.customer_name,
      items,
      total_fcfa: items.reduce((sum, i) => sum + i.line_total_fcfa, 0),
      status: order.payment_method === 'cash' ? 'pay_at_counter' : 'awaiting_payment',
    };
    orders.push(saved);
    if (key) byKey.set(key, saved);
    return { status: 201, body: { data: saved } };
  }

  async function send(order, idempotencyKey) {
    calls += 1;
    if (networkDown) throw new Error('Network error: no connection');
    const answer = handle(order, idempotencyKey);
    if (loseAnswers.includes(calls)) throw new Error('Network timeout: no answer after 15 seconds');
    return answer;
  }

  return { send, orders, stock, get calls() { return calls; } };
}

module.exports = { createFakeApi };
