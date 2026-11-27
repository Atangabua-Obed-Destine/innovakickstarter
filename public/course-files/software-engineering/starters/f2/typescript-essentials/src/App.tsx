import { NKWEN } from './data/nkwen.ts';
import { nkwenReport } from './lib/report.ts';

// Ready for you: shows the restock report so you can SEE the bugs (npm run dev).
export default function App() {
  const lines = nkwenReport(NKWEN.products);
  return (
    <main className="shop">
      <h1>Restock report: Nkwen</h1>
      <p>For Mrs. Enow Patience, branch manager. Products that need ordering, most urgent first.</p>
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Alert</th>
            <th>Stock</th>
            <th>Stock value</th>
          </tr>
        </thead>
        <tbody>
          {lines.map((line, index) => (
            <tr key={`${line.name}-${index}`}>
              <td>{line.name}</td>
              <td>{line.level}</td>
              <td>{line.stock}</td>
              <td>{line.value}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </main>
  );
}
