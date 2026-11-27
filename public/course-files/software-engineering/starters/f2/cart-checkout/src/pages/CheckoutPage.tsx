import type { PracticeMode } from '../api/nkwen.ts';
import type { PlacedOrder } from '../orderTypes.ts';

// TODO 6 (user stories 6 to 11): checkout at /checkout.
// - An order summary and the total, then your CheckoutForm.
// - onPlaceOrder: postOrder({ branch, customer_name, payment_method, items }, { idempotencyKey,
//   params: practiceParams(mode) }). Items are { product_id, quantity }: NEVER send prices.
//   Keep ONE key (crypto.randomUUID()) in a useRef until the server answers 201, 422 or 409;
//   after a 500 or no network, pressing again with the SAME order must send the SAME key
//   (a changed order needs a new key: the server answers 422 to a reused key).
// - onCreated: onPlaced({ order, values }), empty the basket, then navigate to /pay for
//   mobile money or /done otherwise (useNavigate from 'react-router').
// - onUseAvailable: dispatch 'setQty' with the available quantity (the 409 answer).
// - A practice checkbox "Practice: skip the browser checks (to see the server's 422)" that
//   passes validate={() => ({})} to your CheckoutForm (add that optional prop to it).
export default function CheckoutPage({ mode, onPlaced }: { mode: PracticeMode; onPlaced: (placed: PlacedOrder) => void }) {
  console.log('TODO', mode, onPlaced);
  return <h1>TODO: CheckoutPage (props: mode, onPlaced)</h1>;
}
