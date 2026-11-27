import { useState } from 'react';
import { buildUrl, type PracticeMode } from './api/nkwen.ts';
import ProductsPanel from './components/ProductsPanel.tsx';
import { useApi } from './hooks/useApi.ts';
import type { BranchCode, BranchesResponse } from './types.ts';

// Ready for you: it uses YOUR useApi hook to load the branches.
export default function App() {
  const branches = useApi<BranchesResponse>(buildUrl('/branches'));
  const [code, setCode] = useState<BranchCode>('NKW');
  const [mode, setMode] = useState<PracticeMode>('normal');

  if (branches.status === 'loading') return <main className="shop"><p role="status">Loading branches...</p></main>;
  if (branches.status === 'error') {
    return (
      <main className="shop">
        <div className="alert" role="alert">
          <p>Sorry, we could not load the branches. {branches.message}</p>
          <button type="button" onClick={branches.retry}>Try again</button>
        </div>
      </main>
    );
  }

  const list = branches.data.branches;
  const branch = list.find((b) => b.code === code) ?? list[0];
  return (
    <main className="shop">
      <h1>Live stock by branch</h1>
      <div className="filters">
        <div>
          <label htmlFor="branch">Branch</label>
          <select
            id="branch"
            value={branch.code}
            onChange={(e) => setCode(list.find((b) => b.code === e.target.value)?.code ?? 'NKW')}
          >
            {list.map((b) => (
              <option key={b.code} value={b.code}>{b.name}</option>
            ))}
          </select>
        </div>
        <div>
          <label htmlFor="mode">Practice mode</label>
          <select
            id="mode"
            value={mode}
            onChange={(e) => setMode(e.target.value === 'slow' || e.target.value === 'error' ? e.target.value : 'normal')}
          >
            <option value="normal">Normal</option>
            <option value="slow">Slow network</option>
            <option value="error">Server error (500)</option>
          </select>
        </div>
      </div>
      <ProductsPanel branch={branch} mode={mode} />
    </main>
  );
}
