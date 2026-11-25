// Self-check for storage.js. Run in this folder:  node --test
// Node has no localStorage, so these tests use a small fake one.
const test = require('node:test');
const assert = require('node:assert/strict');
const { BASKET_KEY, BRANCH_KEY, saveBasket, loadBasket, clearBasket, saveBranch, loadBranch } = require('./storage.js');

// A fake localStorage: same three methods, kept in a plain object.
function fakeStorage(start = {}) {
  const data = { ...start };
  return {
    data,
    getItem: key => (key in data ? data[key] : null),
    setItem: (key, value) => { data[key] = String(value); },
    removeItem: key => { delete data[key]; },
  };
}
// A storage that refuses to save, like a full or blocked browser.
const fullStorage = { getItem: () => null, setItem: () => { throw new Error('QuotaExceededError'); }, removeItem: () => {} };

test('saveBasket stores the basket as JSON text', () => {
  const storage = fakeStorage();
  assert.equal(saveBasket(storage, [{ id: 3, qty: 2 }]), true);
  assert.equal(typeof storage.data[BASKET_KEY], 'string', 'localStorage only keeps text: use JSON.stringify');
  assert.deepEqual(JSON.parse(storage.data[BASKET_KEY]), [{ id: 3, qty: 2 }]);
});

test('saveBasket returns false instead of crashing when storage is full', () => {
  assert.equal(saveBasket(fullStorage, [{ id: 3, qty: 2 }]), false, 'Wrap setItem in try/catch');
});

test('loadBasket gives back what was saved', () => {
  const storage = fakeStorage();
  saveBasket(storage, [{ id: 3, qty: 2 }, { id: 17, qty: 1 }]);
  assert.deepEqual(loadBasket(storage), [{ id: 3, qty: 2 }, { id: 17, qty: 1 }]);
});

test('loadBasket returns [] when nothing is saved', () => {
  assert.deepEqual(loadBasket(fakeStorage()), []);
});

test('loadBasket survives broken or strange data', () => {
  assert.deepEqual(loadBasket(fakeStorage({ [BASKET_KEY]: '{not json' })), [], 'JSON.parse throws on broken text: catch it');
  assert.deepEqual(loadBasket(fakeStorage({ [BASKET_KEY]: '"hello"' })), [], 'Valid JSON, but not an array');
  const mixed = JSON.stringify([{ id: 3, qty: 2 }, { id: 'x', qty: 1 }, { id: 4, qty: 0 }, null, { id: 5, qty: 1.5 }]);
  assert.deepEqual(loadBasket(fakeStorage({ [BASKET_KEY]: mixed })), [{ id: 3, qty: 2 }], 'Keep only lines with whole-number id and qty > 0');
});

test('clearBasket removes the saved basket', () => {
  const storage = fakeStorage();
  saveBasket(storage, [{ id: 3, qty: 2 }]);
  clearBasket(storage);
  assert.equal(BASKET_KEY in storage.data, false);
  assert.deepEqual(loadBasket(storage), []);
});

test('saveBranch and loadBranch remember a valid branch', () => {
  const storage = fakeStorage();
  assert.equal(saveBranch(storage, 'BAF'), true);
  assert.equal(storage.data[BRANCH_KEY], 'BAF');
  assert.equal(loadBranch(storage, ['NKW', 'ML4', 'CAV', 'BAF'], 'NKW'), 'BAF');
});

test('loadBranch falls back when nothing or an unknown code is saved', () => {
  assert.equal(loadBranch(fakeStorage(), ['NKW', 'BAF'], 'NKW'), 'NKW', 'Nothing saved: use the fallback');
  assert.equal(loadBranch(fakeStorage({ [BRANCH_KEY]: 'LMB' }), ['NKW', 'BAF'], 'NKW'), 'NKW', 'Limbe is not open yet: use the fallback');
});
