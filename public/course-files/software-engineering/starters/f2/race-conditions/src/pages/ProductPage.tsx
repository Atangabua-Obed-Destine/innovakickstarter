import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router';
import { buildUrl, getJson } from '../api/nkwen.ts';
import { postOrder } from '../api/orders.ts';
import { formatFcfa } from '../lib/format.ts';
import type { OrderResult, Product } from '../types.ts';

export default function ProductPage() {
  const { id } = useParams();
  const [product, setProduct] = useState<Product | null>(null);
  const [notFound, setNotFound] = useState(false);
  const [qty, setQty] = useState(1);
  const [sending, setSending] = useState(false);
  const [result, setResult] = useState<OrderResult | null>(null);

  useEffect(() => {
    getJson<Product>(buildUrl(`/products/${id}`))
      .then((data) => setProduct(data))
      .catch(() => setNotFound(true));
  }, []);

  function addFive() {
    for (let i = 0; i < 5; i += 1) setQty(qty + 1);
  }

  async function placeOrder() {
    setSending(true);
    const answer = await postOrder({ branch: 'CAV', customer_name: 'Race test', payment_method: 'cash', items: [{ product_id: Number(id), quantity: qty }] });
    setResult(answer);
    setSending(false);
  }

  if (notFound) return <h1>Product not found</h1>;
  if (!product) return <p className="empty">Loading product...</p>;

  return (
    <>
      <h1>{product.name}</h1>
      <p className="price">{formatFcfa(product.price_fcfa)} / {product.unit}</p>
      <p>
        <Link to={`/products/${product.id + 1}`}>Next product</Link>
      </p>
      <section className="panel" aria-labelledby="quick-title">
        <h2 id="quick-title">Quick test order (collect at Commercial Avenue)</h2>
        <p>Quantity: <output aria-label="Quantity">{qty}</output></p>
        <button type="button" className="secondary" onClick={() => setQty((q) => q + 1)}>Add 1</button>{' '}
        <button type="button" className="secondary" onClick={addFive}>Add 5</button>{' '}
        <button type="button" onClick={placeOrder}>Place test order</button>
        <div role="status">
          {result?.kind === 'created' && <p>Order {result.order.id} checked: {formatFcfa(result.order.total_fcfa)}. Nothing was saved.</p>}
          {result && result.kind !== 'created' && <p>{result.message}</p>}
        </div>
      </section>
    </>
  );
}
