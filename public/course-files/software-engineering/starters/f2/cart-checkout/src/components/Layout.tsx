import { NavLink, Outlet } from 'react-router';
import type { PracticeMode } from '../api/nkwen.ts';
import { useBasket } from '../basket/BasketContext.tsx';
import { itemCount } from '../lib/basket.ts';

interface LayoutProps {
  mode: PracticeMode;
  onModeChange: (mode: PracticeMode) => void;
}

const MODES: { value: PracticeMode; words: string }[] = [
  { value: 'normal', words: 'Normal' },
  { value: 'slow', words: 'Slow network' },
  { value: 'error', words: 'Server error (500)' },
];

export default function Layout({ mode, onModeChange }: LayoutProps) {
  const { state } = useBasket();
  return (
    <>
      <header className="site-header">
        <p className="logo">Nkwen Traders</p>
        <nav aria-label="Main" className="site-nav">
          <ul>
            <li><NavLink to="/" end>Shop</NavLink></li>
            <li><NavLink to="/basket">Basket ({itemCount(state.lines)} items)</NavLink></li>
          </ul>
        </nav>
      </header>
      <main className="shop">
        <Outlet />
      </main>
      <footer className="site-footer">
        <label htmlFor="mode">Practice mode</label>{' '}
        <select id="mode" value={mode} onChange={(e) => onModeChange(MODES.find((m) => m.value === e.target.value)?.value ?? 'normal')}>
          {MODES.map((m) => (
            <option key={m.value} value={m.value}>{m.words}</option>
          ))}
        </select>
        <p>Practice shop for I-NNOVA Kickstarter. Orders are checked by the course API and never saved; payments are simulated.</p>
      </footer>
    </>
  );
}
