// Self-check tests for view.js (no internet needed). Run:  node --test
const test = require('node:test');
const assert = require('node:assert');
const { branchOptionLabel, pageState } = require('./view.js');

test('1. branchOptionLabel shows the city, or the opening year for a planned branch', () => {
  const open = { code: 'TST', name: 'Test Market', city: 'Buea', status: 'open', opened_year: 2020 };
  const planned = { code: 'NEW', name: 'New Town', city: 'Kumba', status: 'planned', opened_year: 2030 };
  assert.strictEqual(branchOptionLabel(open), 'Test Market (Buea)');
  assert.strictEqual(branchOptionLabel(planned), 'New Town (opening 2030)');
});

test('2. pageState picks the right state', () => {
  const open = { name: 'Test Market', status: 'open', opened_year: 2020 };
  const planned = { name: 'New Town', status: 'planned', opened_year: 2030 };
  const products = [{ id: 1 }, { id: 2 }];

  assert.strictEqual(pageState({ status: 'loading' }).kind, 'loading');

  const error = pageState({ status: 'error' });
  assert.strictEqual(error.kind, 'error');
  assert.match(error.message, /try again/i, 'Tell the customer what to do next.');

  const notOpen = pageState({ status: 'done', products: [], branch: planned });
  assert.strictEqual(notOpen.kind, 'empty');
  assert.match(notOpen.message, /New Town/, 'Name the branch that is not open yet.');

  assert.strictEqual(pageState({ status: 'done', products: [], branch: open }).kind, 'empty');
  assert.strictEqual(pageState({ status: 'done', products, branch: open }).kind, 'ready');
});

test('3. every state has a message for the customer', () => {
  for (const input of [{ status: 'loading' }, { status: 'error' }, { status: 'done', products: [] }, { status: 'done', products: [{ id: 1 }] }]) {
    const state = pageState(input);
    assert.ok(state && typeof state.message === 'string' && state.message.length > 0, `No message for ${JSON.stringify(input)}`);
  }
});
