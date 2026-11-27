// Tests for the rep app's sync. Run them from this folder: node --test
// At the start, 3 of these 5 tests fail. That is the incident.
const { test } = require('node:test');
const assert = require('node:assert/strict');
const { syncQueue } = require('./sync');
const { createFakeApi } = require('./fake-api');

const order = (localId, customer_name, productId, quantity, payment_method = 'cash') => ({
  localId, branch: 'BAF', customer_name, payment_method, channel: 'rep_app',
  items: [{ product_id: productId, quantity }], state: 'queued',
});

test('sends every queued order once when the network is good', async () => {
  const api = createFakeApi();
  const queue = [order('q-1', 'Tchoupo Wholesale', 6, 10), order('q-2', 'Foumban Corner Shop', 9, 12)];
  const left = await syncQueue(queue, api.send);
  assert.equal(api.orders.length, 2);
  assert.deepEqual(left, []);
});

test('keeps every order on the phone when there is no network', async () => {
  const api = createFakeApi({ networkDown: true });
  const queue = [order('q-1', 'Tchoupo Wholesale', 6, 10)];
  const left = await syncQueue(queue, api.send);
  assert.equal(api.orders.length, 0);
  assert.equal(left.length, 1);
  assert.equal(left[0].state, 'queued');
});

test('a lost answer does not create a second order', async () => {
  // The server saves the order, but its answer to the first call never reaches the phone.
  const api = createFakeApi({ loseAnswers: [1] });
  const queue = [order('q-1', 'Tchoupo Wholesale', 6, 10, 'mtn_momo')];
  const left = await syncQueue(queue, api.send);
  assert.equal(api.orders.length, 1, `the server has ${api.orders.length} orders for Tchoupo Wholesale`);
  assert.equal(api.stock['BAF:6'], 11, 'only 10 of the 21 bags of rice should be gone');
  assert.deepEqual(left, []);
});

test('a lost answer does not create a second order, even after the app restarts', async () => {
  // Every answer of the first sync is lost; the rep closes the app; later it syncs again.
  const api = createFakeApi({ loseAnswers: [1, 2, 3] });
  const queue = [order('q-1', 'Tchoupo Wholesale', 6, 10, 'mtn_momo')];
  const left = await syncQueue(queue, api.send);
  assert.equal(left.length, 1, 'with no answer at all, the order stays on the phone');
  await syncQueue(left, api.send);
  assert.equal(api.orders.length, 1, `the server has ${api.orders.length} orders for Tchoupo Wholesale`);
});

test('an order the server refuses stays on the phone, marked for the rep', async () => {
  const api = createFakeApi();
  const queue = [order('q-2', 'Dschang Mini-Market', 6, 30)];
  const left = await syncQueue(queue, api.send);
  assert.equal(left.length, 1, 'the refused order must not disappear from the phone');
  assert.equal(left[0].state, 'needs_attention');
  assert.match(left[0].problem ?? '', /Not enough Rice 50kg/, 'show the rep the server\'s message in order.problem');
  assert.equal(api.calls, 1, 'a 409 will not change by trying again, so do not retry it');
});
