// The shop page. It joins the parts you built in this chapter:
// products.js (data), format.js, basket.js, storage.js and catalogue.js.
// Work through the TODOs in order. Refresh the page after each one.

const openBranches = NKWEN.branches.filter(b => b.status === 'open');
const openCodes = openBranches.map(b => b.code);

const el = {
  branch: document.querySelector('#branch'),
  search: document.querySelector('#search'),
  category: document.querySelector('#category'),
  status: document.querySelector('#status'),
  resultCount: document.querySelector('#result-count'),
  noResults: document.querySelector('#no-results'),
  productList: document.querySelector('#product-list'),
  basketList: document.querySelector('#basket-list'),
  basketEmpty: document.querySelector('#basket-empty'),
  basketTotal: document.querySelector('#basket-total'),
  basketCount: document.querySelector('#basket-count'),
  clear: document.querySelector('#clear-basket'),
};

// One object holds everything the page needs to draw itself (the "state").
// The page is always drawn FROM the state: change the state, then draw again.
const state = {
  branch: 'NKW',   // TODO 10 replaces this with the saved branch
  search: '',
  category: 'All',
  basket: [],
};

const findProduct = id => NKWEN.products.find(p => p.id === id);
const branchName = code => openBranches.find(b => b.code === code).name;

// Ready for you: show a message in #status (screen readers read it aloud).
function announce(message, isWarning = false) {
  el.status.textContent = message;
  el.status.classList.toggle('warning', isWarning);
}

// Ready for you: drawing again replaces the buttons, so a keyboard user would
// lose their place. This remembers which button had focus and puts it back.
// Give every button you create a unique data-focus-key, e.g. 'add-6'.
function keepFocus(draw) {
  const active = document.activeElement;
  const key = active && active.dataset ? active.dataset.focusKey : null;
  draw();
  if (key) {
    const again = document.querySelector(`[data-focus-key="${key}"]`);
    if (again && !again.disabled) again.focus();
    else el.basketList.closest('aside').querySelector('h2').focus();
  }
}

// Ready for you: a small button for the basket lines.
function makeButton(text, action, id, label) {
  const button = document.createElement('button');
  button.type = 'button';
  button.textContent = text;
  button.dataset.action = action;
  button.dataset.id = id;
  button.dataset.focusKey = action + '-' + id;
  button.setAttribute('aria-label', label);
  return button;
}

// ---------- Drawing ----------

// TODO 1: build and RETURN one product card (like the Building & Removing lesson):
//   <li class="product-card">
//     <h3>name</h3>  <p class="category">  <p class="price">36,005 FCFA / bag</p>
//     <p class="stock stock--low">Only 3 left</p>   (stock at state.branch)
//     <button data-action="add" data-id="6" data-focus-key="add-6">Add</button>
//   </li>
// Disable the button when the basket already holds maxAllowed for this branch,
// and change its text to "Out of stock" or "Limit reached".
// Every "Add" button needs a different accessible name: add a
// <span class="visually-hidden"> with the product name inside the button.
function createProductCard(product) {
}

// TODO 2: draw the products that match state.search and state.category
// (filterProducts). Show the count in #result-count, e.g. "(20)".
// When nothing matches, show #no-results with a helpful message such as
// No products match "pizza". Try another word or category.
function renderProducts() {
}

// TODO 3: draw the basket from basketLines(state.basket, NKWEN.products).
// Each line: the name, "qty x unit price", the line total, and the buttons
// makeButton('-', 'decrease', id, ...), makeButton('+', 'increase', id, ...)
// and makeButton('Remove', 'remove', id, ...). Disable "+" at the limit.
// Then: show #basket-empty only when the basket is empty, and fill
// #basket-total (formatFcfa) and #basket-count (itemCount).
function renderBasket() {
}

function renderAll() {
  keepFocus(() => {
    renderProducts();
    renderBasket();
  });
}

// TODO 4: EVERY basket change goes through this function:
// put newBasket in state.basket, save it (saveBasket(localStorage, ...)),
// call renderAll(), then announce(message, isWarning) if there is a message.
function changeBasket(newBasket, message, isWarning = false) {
}

// ---------- Events ----------

// TODO 5: 'input' on #search and 'change' on #category: update the state,
// then call renderProducts().

// TODO 6: 'change' on #branch: update state.branch and save it (saveBranch).
// Then fit the basket to the new branch with revalidate(...). Pass the new
// basket to changeBasket with a message that names every change, e.g.
// "Now collecting at Mile 4. Stock is different here: Sugar 1kg removed."

// TODO 7: event delegation. Add ONE 'click' listener to #product-list.
// Inside it: const button = event.target.closest('button[data-action="add"]');
// If there is no button, return. Otherwise add the product with addItem
// and call changeBasket with a short message ("Rice 50kg added. ...").

// TODO 8: ONE 'click' listener on #basket-list for the 'increase',
// 'decrease' and 'remove' buttons (read button.dataset.action).
// Use setQuantity and removeItem, then changeBasket.

// TODO 9: #clear-basket empties the basket (clearBasket and changeBasket([])).

// ---------- Start ----------
// Ready for you: fill the branch and category lists.
openBranches.forEach(b => {
  const option = document.createElement('option');
  option.value = b.code;
  option.textContent = `${b.name}, ${b.city}`;
  el.branch.append(option);
});
NKWEN.categories.forEach(c => {
  const option = document.createElement('option');
  option.value = c;
  option.textContent = c;
  el.category.append(option);
});
el.basketList.closest('aside').querySelector('h2').tabIndex = -1;

// TODO 10: load the saved branch (loadBranch) into state.branch and el.branch.
// Load the saved basket (loadBasket), then pass it through revalidate,
// because stock may have changed since it was saved. Save the result.
el.branch.value = state.branch;
renderAll();
