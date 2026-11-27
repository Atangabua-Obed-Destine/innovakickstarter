// TODO 6: show "Basket: 3 items" from the SHARED basket. No props needed:
//   const { state } = useBasket();   then itemCount(state.lines)
// (import useBasket from '../basket/BasketContext.tsx' and itemCount from '../lib/basket.ts')
export default function Header() {
  return (
    <header className="site-header">
      <p className="logo">Nkwen Traders</p>
      <p className="basket-badge">TODO 6: Basket count</p>
    </header>
  );
}
