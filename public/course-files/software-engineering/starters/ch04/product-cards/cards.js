// Build and remove elements with JavaScript. NKWEN comes from products.js.
const productList = document.querySelector('#product-list');
const categorySelect = document.querySelector('#category');
const shoppingList = document.querySelector('#shopping-list');
const listEmpty = document.querySelector('#list-empty');

// TODO 1: createProductCard(product) builds ONE card and RETURNS it.
// It must not add the card to the page. Build this with createElement:
//   <li class="product-card">
//     <h3>Rice 50kg</h3>
//     <p class="category">Grains</p>
//     <p class="price">36,005 FCFA</p>
//     <button type="button">Add to list</button>
//   </li>
// Use textContent for every piece of data.
// Give the button a click listener that calls addToList(product).
function createProductCard(product) {
}

// TODO 2: renderProducts(products) empties #product-list
// (productList.replaceChildren() removes every child), then appends
// one card per product.
function renderProducts(products) {
}

// TODO 3: addToList(product) adds an <li> to #shopping-list:
//   <li data-id="6">Rice 50kg <button type="button">Remove</button></li>
// - If the product is already in the list, do nothing.
//   Hint: shoppingList.querySelector(`[data-id="${product.id}"]`)
// - The Remove button removes ITS OWN <li> (li.remove()) and calls updateEmptyMessage().
// - At the end, call updateEmptyMessage().
function addToList(product) {
}

// TODO 4: show #list-empty only when the list has no items.
// Hint: listEmpty.hidden = shoppingList.children.length > 0
function updateEmptyMessage() {
}

// Ready for you: one <option> per category.
NKWEN.categories.forEach(category => {
  const option = document.createElement('option');
  option.value = category;
  option.textContent = category;
  categorySelect.append(option);
});

// TODO 5: when the category changes, render only the products of that
// category, or all of them for "All". Use .filter().

// TODO 6: show all products when the page loads.
