import { stockStatus, stockText } from '../lib/format.ts';

// TODO 2: describe the props with an interface, then use it:
//   interface StockBadgeProps { quantity: number }
//   export default function StockBadge({ quantity }: StockBadgeProps) {
export default function StockBadge({ quantity }) {
  return <p className={`stock stock--${stockStatus(quantity)}`}>{stockText(quantity)}</p>;
}
