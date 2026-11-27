import { NKWEN } from '../data/nkwen.ts';
import { formatFcfa, stockText } from '../lib/format.ts';

// TODO 5: one product's own page, at /products/6 for example.
// - Read the id from the address:  const { id } = useParams();   (it is TEXT, or undefined)
// - Find the product with Number(id). No such product? Return:
//     <h1>Product not found</h1>, a sentence with the id, and <Link to="/">Back to the shop</Link>
// - Otherwise show: <h1> with the name, the category and price, then a table
//   "Stock by branch" with one row per OPEN branch (name, stockText(stock there)),
//   and a link "More Oils" (the product's category) to /?category=Oils
export default function ProductPage() {
  return <h1>TODO 5: one product</h1>;
}
