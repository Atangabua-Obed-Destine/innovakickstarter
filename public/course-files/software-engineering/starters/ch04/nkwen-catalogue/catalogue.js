// Search, filter and sort the catalogue. No page code here, so Node can test it.
// Run every self-check in this folder with:  node --test

// TODO 1: return the products whose NAME contains the search text AND whose
// category matches.
// - Search ignores upper/lower case and spaces around the text ("  RICE " finds rice).
// - An empty search matches every product.
// - category 'All' (or '') means every category.
// - Return a new array; never change the products array.
function filterProducts(products, { search = '', category = 'All' } = {}) {
  throw new Error('TODO: write filterProducts()');
}

// STRETCH (optional): write sortProducts(products, sortKey) that returns a NEW
// array sorted by 'name' (A to Z), 'price' (cheapest first) or '-price'
// (most expensive first). Add it to module.exports below and its tests run.

// This line lets Node's test runner use the functions. The browser ignores it.
if (typeof module !== 'undefined') module.exports = { filterProducts };
