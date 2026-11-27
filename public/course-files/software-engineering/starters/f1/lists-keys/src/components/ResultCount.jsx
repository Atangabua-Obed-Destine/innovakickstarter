// TODO 4: this component has a famous React bug.
// Search for "zzz" and look just under the search form. What do you see?
// Read the lesson ("Conditional rendering") and fix it with a real true/false test.
export default function ResultCount({ count }) {
  return <>{count && <p className="result-count">{count} products</p>}</>;
}
