import { useBasket } from '../basket/BasketContext.tsx';
import { NKWEN } from '../data/nkwen.ts';

const openBranches = NKWEN.branches.filter((b) => b.status === 'open');

// Ready for you: changing branch is one more action on the shared basket.
export default function BranchSelect() {
  const { state, dispatch } = useBasket();
  return (
    <div className="filters">
      <div>
        <label htmlFor="branch">Collect at</label>
        <select
          id="branch"
          value={state.branch}
          onChange={(e) => {
            const branch = openBranches.find((b) => b.code === e.target.value);
            if (branch && branch.code !== 'LMB') {
              dispatch({ type: 'changeBranch', branch: branch.code, branchName: branch.name, products: NKWEN.products });
            }
          }}
        >
          {openBranches.map((b) => (
            <option key={b.code} value={b.code}>{b.name}</option>
          ))}
        </select>
      </div>
    </div>
  );
}
