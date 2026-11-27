// TODO 15: one basket line. Props: line (from basketLines: id, name, qty,
// price_fcfa, line_total), max, onIncrease, onDecrease, onRemove.
// Return an <li className="basket-line"> with the name, the line total
// (formatFcfa), and three buttons with clear names for screen readers:
//   "-"      aria-label "One less Rice 50kg"   -> onDecrease(line.id)
//   "+"      aria-label "One more Rice 50kg"   -> onIncrease(line.id), disabled at max
//   Remove   aria-label "Remove Rice 50kg"     -> onRemove(line.id)
// and the words "2 x 36,005 FCFA" between - and +.
export default function BasketLine({ line }) {
  return <li>TODO 15: BasketLine {line.name}</li>;
}
