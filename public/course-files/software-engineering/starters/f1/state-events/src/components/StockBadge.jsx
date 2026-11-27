import { stockStatus, stockText } from '../lib/format.js';

export default function StockBadge({ quantity }) {
  return <p className={`stock stock--${stockStatus(quantity)}`}>{stockText(quantity)}</p>;
}
