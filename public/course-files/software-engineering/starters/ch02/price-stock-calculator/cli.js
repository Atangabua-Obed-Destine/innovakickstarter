// Nkwen Price & Stock Calculator: the command-line part.
//   node cli.js price 6 2 12 10     (product 6 x 2, product 12 x 10)
//   node cli.js report NKW          (one branch)
//   node cli.js report all          (every open branch, then the company total)
// process.argv holds the words typed after "node cli.js".

const { products, branches } = require('./nkwen.json');
const sales = require('./sales.json');
const reorderLevels = require('./reorder-levels.json');
const { priceBasket } = require('./pricing.js');
const { formatFcfa } = require('./money.js');
const { splitSales } = require('./sales.js');
const { branchReport } = require('./report.js');

const USAGE = 'Usage: node cli.js price <productId> <quantity> [<productId> <quantity> ...]\n'
  + '       node cli.js report <branch code | all>';

function printReceipt(receipt) {
  console.log('Nkwen Traders - receipt');
  for (const line of receipt.lines) {
    console.log(`  ${line.quantity} x ${line.name} @ ${formatFcfa(line.unitPrice)} = ${formatFcfa(line.lineTotal)}`);
  }
  console.log(`Subtotal: ${formatFcfa(receipt.subtotal)}`);
  console.log(`Wholesale discount: ${formatFcfa(receipt.discount)}`);
  console.log(`To pay: ${formatFcfa(receipt.total)} (VAT included: ${formatFcfa(receipt.vat)})`);
}

// TODO: print one branch report. Follow the layout in README.md.
// Use formatFcfa() for money. Print only: do not calculate anything here.
function printReport(report) {
  console.log(`Weekly report - ${report.name} (${report.code})`);
  // TODO
}

function priceCommand(args) {
  if (args.length === 0 || args.length % 2 !== 0) {
    console.error('Give pairs of numbers: <productId> <quantity>.');
    return 1;
  }
  const items = [];
  for (let i = 0; i < args.length; i += 2) {
    const productId = Number(args[i]);
    const quantity = Number(args[i + 1]);
    if (!Number.isInteger(productId) || !Number.isInteger(quantity) || quantity < 1) {
      console.error(`"${args[i]} ${args[i + 1]}" is not a valid product id and quantity.`);
      return 1;
    }
    items.push({ productId, quantity });
  }
  try {
    printReceipt(priceBasket(items, products));
    return 0;
  } catch (error) {
    console.error(error.message);
    return 1;
  }
}

function reportCommand(args) {
  const open = branches.filter((b) => b.status === 'open').map((b) => b.code);
  const wanted = (args[0] || '').toUpperCase();
  const codes = wanted === 'ALL' ? open : [wanted];
  if (!codes.every((code) => open.includes(code))) {
    console.error(`"${args[0] || ''}" is not an open branch. Open branches: ${open.join(', ')} (or "all").`);
    return 1;
  }
  const { valid, invalid } = splitSales(sales, products, branches);
  let companyRevenue = 0;
  for (const code of codes) {
    const report = branchReport(code, products, branches, valid, reorderLevels);
    printReport(report);
    console.log('');
    companyRevenue += report.revenue;
  }
  if (codes.length > 1) {
    console.log(`Company total: ${formatFcfa(companyRevenue)} from ${valid.length} valid sales`);
  }
  console.log(`Skipped records (${invalid.length}):`);
  for (const record of invalid) {
    console.log(`  #${record.id}: ${record.reason}`);
  }
  return 0;
}

function main(args) {
  const [command, ...rest] = args;
  if (command === 'price') return priceCommand(rest);
  if (command === 'report') return reportCommand(rest);
  console.error(USAGE);
  return 1;
}

process.exitCode = main(process.argv.slice(2));
