// app.js: connects the API, the view rules and the basket rules to the page.
// If your Chapter 4 catalogue already does some of this, reuse your own code.
// Functions you can call: getProducts, getBranches, getJson, postOrder (nkwen-api.js),
// formatFcfa, stockStatus, stockText (format.js), branchOptionLabel, pageState (view.js),
// filtersToParams, addToBasket, removeFromBasket, basketTotal, orderPayload, describeOrderResult (catalogue.js).

const $ = (selector) => document.querySelector(selector);
const els = {
  search: $('#search'), category: $('#category'), branch: $('#branch'), sort: $('#sort'), practice: $('#practice'),
  status: $('#status'), retry: $('#retry'), grid: $('#products'),
  lines: $('#basket-lines'), total: $('#basket-total'),
  form: $('#order-form'), name: $('#customer-name'), orderBranch: $('#order-branch'), payment: $('#payment-method'),
  send: $('#send-order'), result: $('#order-result'),
};

const BASKET_KEY = 'nkwen-basket-v1';
let branches = [];
let basket = loadBasket();

// The basket survives a page refresh (you learned this in Chapter 4).
function loadBasket() {
  try {
    const saved = JSON.parse(localStorage.getItem(BASKET_KEY));
    return Array.isArray(saved) ? saved : [];
  } catch (error) {
    return [];
  }
}

function saveBasket() {
  localStorage.setItem(BASKET_KEY, JSON.stringify(basket));
}

function practiceParams() {
  if (els.practice.value === 'slow') return { delay: 2500 };
  if (els.practice.value === 'error') return { fail: 500 };
  return {};
}

// TODO 1: showState(state): same as in "Rendering Real Data into the DOM".
function showState(state) {
  // TODO
}

// TODO 2: renderProducts(list, branchCode): one card per product, as before,
//   plus an "Add to basket" button. The button is disabled when the branch has 0 in stock.
//   On click: basket = addToBasket(basket, product, 1); saveBasket(); renderBasket();
function renderProducts(list, branchCode) {
  // TODO
}

// TODO 3: loadProducts(): read the four filters, build the params with filtersToParams,
//   add practiceParams(), call getProducts, and show loading / error / empty / ready.
//   The API does the filtering: do NOT download everything and filter in the browser.
//   Bonus: if the customer types fast, an old answer can arrive after a new one.
//   Ignore old answers (hint: count requests with a number and compare).
async function loadProducts() {
  // TODO
}

// TODO 4: renderBasket(): one <li> per line ("2 x Rice 50kg = 72,010 FCFA") with a Remove button,
//   "Your basket is empty." when empty, the total in #basket-total,
//   and the Send button disabled when the basket is empty.
function renderBasket() {
  // TODO
}

// TODO 5: showOrderResult(result): show { kind, title, lines } from describeOrderResult in #order-result.
//   Give the box the class 'order-result result-' + kind. Use textContent for the API's messages.
function showOrderResult(result) {
  // TODO
}

// TODO 6: sendOrder(event): stop the form reloading the page (event.preventDefault()),
//   build the payload with orderPayload, disable the button and show "Sending...",
//   call postOrder(payload, practiceParams()), then showOrderResult(describeOrderResult(status, body)).
//   On success (201), empty the basket. If postOrder throws (no network), show the 'error' result.
//   Always put the button back to normal at the end (try / catch / finally).
async function sendOrder(event) {
  // TODO
}

function fillSelect(select, items, toOption) {
  select.replaceChildren();
  for (const item of items) {
    const { value, text } = toOption(item);
    const option = document.createElement('option');
    option.value = value;
    option.textContent = text;
    select.append(option);
  }
}

async function start() {
  renderBasket();
  try {
    const [branchList, categoryData] = await Promise.all([getBranches(), getJson('/categories')]);
    branches = branchList;
    fillSelect(els.branch, branches, (b) => ({ value: b.code, text: branchOptionLabel(b) }));
    fillSelect(els.orderBranch, branches.filter((b) => b.status === 'open'), (b) => ({ value: b.code, text: b.name }));
    fillSelect(els.category, [''].concat(categoryData.categories), (c) => ({ value: c, text: c || 'All categories' }));
  } catch (error) {
    console.error(error);
  }
  await loadProducts();
}

let searchTimer;
els.search.addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadProducts, 400); // wait until the customer stops typing
});
for (const select of [els.category, els.branch, els.sort, els.practice]) {
  select.addEventListener('change', loadProducts);
}
$('#filters').addEventListener('submit', (event) => { event.preventDefault(); loadProducts(); });
els.retry.addEventListener('click', loadProducts);
els.form.addEventListener('submit', sendOrder);
start();
