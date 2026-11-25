// Self-check tests for nkwen-api.js. Run:  node --test
// Tests 3-7 call the real course API, so you need internet (about 7 small requests).
const test = require('node:test');
const assert = require('node:assert');
const { API_BASE, buildUrl, getProducts, getBranches } = require('./nkwen-api.js');

test('1. buildUrl joins the base and the path', () => {
  assert.strictEqual(buildUrl('/branches'), `${API_BASE}/branches`);
});

test('2. buildUrl adds query parameters and skips empty ones', () => {
  assert.strictEqual(buildUrl('/products', { category: 'Dairy' }), `${API_BASE}/products?category=Dairy`);
  assert.strictEqual(buildUrl('/products', { category: 'Dairy', search: '' }), `${API_BASE}/products?category=Dairy`,
    'Empty values like search: "" should not appear in the URL.');
  assert.strictEqual(buildUrl('/products', { search: undefined }), `${API_BASE}/products`,
    'With no real params, there should be no "?" at the end.');
  assert.ok(buildUrl('/products', { search: 'palm oil' }).endsWith('search=palm+oil'),
    'Spaces must be encoded. URLSearchParams does this for you.');
});

test('3. getProducts() returns an array of products', async () => {
  const products = await getProducts();
  assert.ok(Array.isArray(products), 'getProducts should return the "products" array, not the whole JSON object.');
  assert.ok(products.length > 0);
  for (const p of products) {
    assert.strictEqual(typeof p.name, 'string');
    assert.strictEqual(typeof p.price_fcfa, 'number');
  }
});

test('4. getProducts({ category }) only returns that category', async () => {
  const products = await getProducts({ category: 'Dairy' });
  assert.ok(products.length > 0);
  assert.ok(products.every((p) => p.category === 'Dairy'), 'Did buildUrl add ?category=... to the URL?');
});

test('5. a server error (500) becomes a thrown Error that mentions 500', async () => {
  await assert.rejects(getProducts({ fail: 500 }), (error) => {
    assert.match(error.message, /500/, 'The error message should include the status number.');
    assert.strictEqual(error.status, 500, 'Set error.status = response.status.');
    return true;
  });
});

test('6. an unknown branch (404) becomes a thrown Error that mentions 404', async () => {
  await assert.rejects(getProducts({ branch: 'XYZ' }), /404/);
});

test('7. getBranches() returns branches that each have a code and a status', async () => {
  const branches = await getBranches();
  assert.ok(Array.isArray(branches), 'getBranches should return the "branches" array.');
  assert.ok(branches.every((b) => typeof b.code === 'string' && typeof b.status === 'string'));
});
