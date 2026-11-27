import { useState } from 'react';
import CheckoutForm from './checkout/CheckoutForm.tsx';
import PaymentScreen from './checkout/PaymentScreen.tsx';
import { cleanPhone, needsPhone, type CheckoutValues } from './checkout/validate.ts';
import { NKWEN } from './data/nkwen.ts';
import { formatFcfa } from './lib/format.ts';
import type { OpenBranchCode, Order, OrderResult } from './types.ts';

// Ready for you. There is no network in this lesson: a "Server answer" drop-down
// decides what the pretend server says, so you can see every case on purpose.
type Practice = 'created' | 'invalid' | 'stock' | 'failed';
const openBranches = NKWEN.branches.filter((b) => b.status === 'open');
const byId = (id: number) => NKWEN.products.find((p) => p.id === id)!;

export function fakePlaceOrder(values: CheckoutValues, practice: Practice, lines: { id: number; qty: number }[]): Promise<OrderResult> {
  const items = lines.map((l) => ({ product_id: l.id, name: byId(l.id).name, quantity: l.qty, unit_price_fcfa: byId(l.id).price_fcfa, line_total_fcfa: byId(l.id).price_fcfa * l.qty }));
  const results: Record<Practice, OrderResult> = {
    created: {
      kind: 'created',
      order: {
        id: 'ORD-PRACTICE',
        branch: values.branch as OpenBranchCode,
        customer_name: values.customer_name.trim(),
        payment_method: values.payment_method,
        status: values.payment_method === 'cash' ? 'pay_at_counter' : 'awaiting_payment',
        items,
        total_fcfa: items.reduce((sum, i) => sum + i.line_total_fcfa, 0),
        created_at: '2026-12-03T10:15:00+01:00',
      },
    },
    invalid: { kind: 'invalid', message: 'The order is not valid.', errors: { customer_name: ['The customer name field must be at least 2 characters.'], 'items.0.quantity': ['The items.0.quantity field must be at least 1.'] } },
    stock: { kind: 'stock', message: 'Not enough Rice 50kg at this branch.', product_id: 6, requested: lines.find((l) => l.id === 6)?.qty ?? 0, available: 1 },
    failed: { kind: 'failed', message: 'Sorry, our server had a problem (500).' },
  };
  return new Promise((resolve) => setTimeout(() => resolve(results[practice]), 300));
}

export default function App() {
  const [practice, setPractice] = useState<Practice>('created');
  const [lines, setLines] = useState([{ id: 6, qty: 2 }, { id: 15, qty: 1 }]);
  const [done, setDone] = useState<{ order: Order; values: CheckoutValues } | null>(null);
  const total = lines.reduce((sum, l) => sum + byId(l.id).price_fcfa * l.qty, 0);

  if (done) {
    const { order, values } = done;
    const branch = openBranches.find((b) => b.code === order.branch)?.name ?? order.branch;
    if (needsPhone(values.payment_method) && (values.payment_method === 'mtn_momo' || values.payment_method === 'orange_money')) {
      return <main className="shop"><PaymentScreen order={order} method={values.payment_method} phone={cleanPhone(values.phone)} /></main>;
    }
    return (
      <main className="shop">
        <section className="panel" aria-labelledby="done-title">
          <h2 id="done-title">Order {order.id} is booked</h2>
          {values.payment_method === 'cash'
            ? <p>Pay {formatFcfa(order.total_fcfa)} at the counter when you collect at {branch}.</p>
            : <p>Head office will send the bank details by SMS (simulated). Your order waits for the payment.</p>}
        </section>
      </main>
    );
  }

  return (
    <main className="shop">
      <h1>Checkout</h1>
      <ul>
        {lines.map((l) => <li key={l.id}>{l.qty} x {byId(l.id).name}</li>)}
      </ul>
      <p className="total">Total: <strong>{formatFcfa(total)}</strong> (VAT included)</p>
      <div className="practice">
        <label htmlFor="practice">Server answer (practice)</label>
        <select id="practice" value={practice} onChange={(e) => setPractice(e.target.value as Practice)}>
          <option value="created">201: order created</option>
          <option value="invalid">422: the order is not valid</option>
          <option value="stock">409: not enough stock</option>
          <option value="failed">500: server error</option>
        </select>
      </div>
      <CheckoutForm
        branches={openBranches}
        initialBranch="CAV"
        onPlaceOrder={(values) => fakePlaceOrder(values, practice, lines)}
        onCreated={(order, values) => setDone({ order, values })}
        onUseAvailable={(productId, available) => setLines(lines.map((l) => (l.id === productId ? { ...l, qty: available } : l)))}
      />
    </main>
  );
}
