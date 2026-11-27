import { isOpenBranchCode } from '../lib/branches.ts';

// TODO 6: type the props: branches (Branch[]), value (OpenBranchCode) and onChange,
// a function that receives an OpenBranchCode. event.target.value is only a string,
// so the code below checks it with isOpenBranchCode() before calling onChange.
// (Try deleting the check, run npm run typecheck, and read the error.)
export default function BranchSelect({ branches, value, onChange }) {
  return (
    <>
      <label htmlFor="branch">Collect at</label>
      <select
        id="branch"
        value={value}
        onChange={(event) => {
          const code = event.target.value;
          if (isOpenBranchCode(code)) onChange(code);
        }}
      >
        {branches.map((b) => (
          <option key={b.code} value={b.code}>{b.name}</option>
        ))}
      </select>
    </>
  );
}
