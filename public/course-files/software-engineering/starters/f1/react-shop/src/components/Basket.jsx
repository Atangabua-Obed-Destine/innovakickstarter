// TODO 14: the basket panel.
// Props: basket, products, branch, onIncrease, onDecrease, onRemove, onClear.
// Return <aside className="basket" aria-labelledby="basket-title"> with
// - <h2 id="basket-title">Your basket</h2>
// - "Your basket is empty." when there are no lines, otherwise a <ul> with one
//   <BasketLine> per line from basketLines(basket, products) (key: the line id).
//   Give each line max={maxAllowed(product, branch)} so its + button knows the limit.
// - <p className="total">Total: <strong>{formatFcfa(basketTotal(basket, products))}</strong></p>
// - an "Empty the basket" button (only when there are lines) that calls onClear.
export default function Basket({ basket }) {
  return <aside className="basket"><p>TODO 14: Basket ({basket.length} lines)</p></aside>;
}
