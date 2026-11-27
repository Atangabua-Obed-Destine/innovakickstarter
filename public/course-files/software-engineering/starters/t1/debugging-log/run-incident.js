// Replays the rep's sync at Bafoussam on the day of the incident.
// Run it from this folder: node run-incident.js
const { syncQueue } = require('./sync');
const { createFakeApi } = require('./fake-api');

async function main() {
  // The answer to the very first request was lost on the weak network at the market.
  const api = createFakeApi({ loseAnswers: [1] });
  const queue = [
    { localId: 'q-1', branch: 'BAF', customer_name: 'Tchoupo Wholesale', payment_method: 'mtn_momo', channel: 'rep_app', items: [{ product_id: 6, quantity: 10 }], state: 'queued' },
    { localId: 'q-2', branch: 'BAF', customer_name: 'Dschang Mini-Market', payment_method: 'cash', channel: 'rep_app', items: [{ product_id: 6, quantity: 30 }], state: 'queued' },
    { localId: 'q-3', branch: 'BAF', customer_name: 'Foumban Corner Shop', payment_method: 'cash', channel: 'rep_app', items: [{ product_id: 9, quantity: 12 }], state: 'queued' },
  ];

  const left = await syncQueue(queue, api.send);

  console.log(`Requests sent: ${api.calls}`);
  console.log('\nOrders the server saved:');
  for (const o of api.orders) console.log(`  ${o.id}  ${o.customer_name.padEnd(22)} ${o.items.map((i) => `${i.quantity} x ${i.name}`).join(', ')}  ${o.total_fcfa} FCFA`);
  console.log(`\nRice 50kg left at Bafoussam: ${api.stock["BAF:6"]} (there were 21 before the sync)`);
  console.log('\nWhat the rep\'s phone shows after the sync:');
  if (left.length === 0) console.log('  "All orders sent!"');
  for (const o of left) console.log(`  ${o.customer_name}: ${o.state}${o.problem ? ` (${o.problem})` : ''}`);
}

main();
