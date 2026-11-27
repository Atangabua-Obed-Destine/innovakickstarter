// Sends the orders a sales rep took offline to the Orders API, oldest first.
// This is a JavaScript copy of the rep app's sync code, so everyone in the squad can debug it.
//
// Each queued order looks like:
//   { localId: 'q-1', branch: 'BAF', customer_name: 'Tchoupo Wholesale', payment_method: 'mtn_momo',
//     channel: 'rep_app', items: [{ product_id: 6, quantity: 10 }], state: 'queued' }
//
// send(order, idempotencyKey) posts one order to the API. It resolves to { status, body },
// or it throws when the network fails and no answer arrives.
const { randomUUID } = require('node:crypto');

async function syncQueue(queue, send, { attempts = 3 } = {}) {
  for (const order of queue) {
    if (order.state !== 'queued') continue;
    for (let attempt = 1; attempt <= attempts; attempt++) {
      try {
        const response = await send(order, randomUUID());
        order.state = 'synced';
        order.serverId = response.body?.data?.id ?? null;
        break;
      } catch (error) {
        // No answer from the network: try again.
        order.lastError = error.message;
      }
    }
  }
  // The phone keeps only the orders that still need something done.
  return queue.filter((order) => order.state !== 'synced');
}

module.exports = { syncQueue };
