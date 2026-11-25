// run.js: clean the real legacy price list and print a report.  Run:  node run.js
const { getJson, getProducts } = require('./nkwen-api.js');
const { cleanPriceList, compareWithCatalogue } = require('./clean.js');

async function main() {
  const { rows } = await getJson('/legacy/price-list');
  const { categories } = await getJson('/categories');
  const catalogue = await getProducts();

  const { clean, rejected } = cleanPriceList(rows, categories);
  console.log(`Rows in the old list: ${rows.length}`);
  console.log(`Clean: ${clean.length}   Rejected: ${rejected.length}\n`);

  console.log('CLEAN ROWS');
  for (const p of clean) {
    const warn = p.warnings.length ? `   [warning: ${p.warnings.join('; ')}]` : '';
    console.log(`  ${p.name} | ${p.price_fcfa} FCFA | ${p.category ?? '(no category)'}${warn}`);
  }

  console.log('\nREJECTED ROWS (send back to a person to fix)');
  for (const r of rejected) {
    console.log(`  ${JSON.stringify(r.row)} -> ${r.reasons.join(', ')}`);
  }

  console.log('\nOLD PRICE vs TODAY');
  for (const c of compareWithCatalogue(clean, catalogue)) {
    console.log(`  ${c.name}: ${c.legacy_price} -> ${c.current_price ?? '?'} (${c.status})`);
  }
}

main().catch((error) => console.error('Could not finish the report:', error.message));
