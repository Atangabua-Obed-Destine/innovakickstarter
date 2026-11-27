import { NKWEN } from '../data/nkwen.ts';

// Ready for you: a simple page so the menu has two places to go.
export default function BranchesPage() {
  return (
    <>
      <h1>Our branches</h1>
      <ul>
        {NKWEN.branches.map((b) => (
          <li key={b.code}>
            {b.name}, {b.city}: {b.status === 'open' ? `open since ${b.opened_year}` : `opens in ${b.opened_year}`}
          </li>
        ))}
      </ul>
    </>
  );
}
