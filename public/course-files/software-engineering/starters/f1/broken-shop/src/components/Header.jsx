export default function Header({ basket }) {
  const count = basket.reduce((sum, line) => sum + line.qty, 0);
  return (
    <header className="site-header">
      <p className="logo">Nkwen Traders</p>
      <p className="basket-badge">
        {count && `Basket: ${count} ${count === 1 ? 'item' : 'items'}`}
        {!count && 'Your basket is empty'}
      </p>
    </header>
  );
}
