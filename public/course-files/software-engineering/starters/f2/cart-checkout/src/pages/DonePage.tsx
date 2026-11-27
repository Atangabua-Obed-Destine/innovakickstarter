import type { PlacedOrder } from '../orderTypes.ts';

// TODO 8 (user story 8): /done, for cash and bank transfer.
// <h1>Order ORD-... is booked</h1>, each line from the SERVER's answer (order.items), the
// server's total, what to do next, and "nothing was saved". No order: say "No order yet".
export default function DonePage({ placed }: { placed: PlacedOrder | null }) {
  console.log('TODO', placed);
  return <h1>TODO: DonePage (props: placed)</h1>;
}
