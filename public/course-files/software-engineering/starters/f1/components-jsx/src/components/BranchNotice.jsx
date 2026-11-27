// TODO 1: this component has FIVE JSX mistakes, so the page cannot start.
// Read the error in the terminal or in the browser: it names the file and
// the line. Fix ONE mistake, save, and read the next error.
// The five JSX rules you need are in the lesson ("Key ideas").
export default function BranchNotice() {
  return (
    <h2 id="notice-title">New: Bafoussam Centre is open</h2>
    <section class="panel" aria-labelledby="notice-title">
      <p style="font-weight: bold">Opened in April 2026. Manager: Mr. Kenfack Roland.</p>
      <p>Our Limbe Down Beach branch opens in 2027.</p>
      <label for="limbe-news">Tell me when Limbe opens</label>
      <input id="limbe-news" type="checkbox">
    </section>
  );
}
