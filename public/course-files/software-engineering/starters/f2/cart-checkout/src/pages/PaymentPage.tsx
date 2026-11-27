import type { PlacedOrder } from '../orderTypes.ts';

// TODO 7 (user story 12): /pay shows your PaymentScreen for the placed order.
// No order, or not mobile money? Say "No order to pay" with a link to the shop.
export default function PaymentPage({ placed }: { placed: PlacedOrder | null }) {
  console.log('TODO', placed);
  return <h1>TODO: PaymentPage (props: placed)</h1>;
}
