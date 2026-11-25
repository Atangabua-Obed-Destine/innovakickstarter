// REPLACE THIS FILE with your finished storage.js from "Saving Data in the Browser".
// Then run  node --test  in this folder: its tests must pass here too.

// Save the basket and the chosen branch in the browser.
// Every function receives "storage" (window.localStorage in the browser,
// a fake object in the tests), so Node can test them. Run:  node --test

const BASKET_KEY = 'nkwen-basket';
const BRANCH_KEY = 'nkwen-branch';

// TODO 1: save the basket (an array of { id, qty }) under BASKET_KEY.
// localStorage only stores TEXT, so use JSON.stringify.
// setItem can throw an error (storage full or blocked): use try/catch.
// Return true when it worked, false when it failed.
function saveBasket(storage, basket) {
  throw new Error('TODO: write saveBasket()');
}

// TODO 2: read the basket back. ALWAYS return an array:
// - nothing saved yet (getItem returns null) -> []
// - text that is not valid JSON (JSON.parse throws) -> []
// - valid JSON that is not an array -> []
// - keep only lines where id and qty are whole numbers and qty > 0
function loadBasket(storage) {
  throw new Error('TODO: write loadBasket()');
}

// TODO 3: remove the saved basket (removeItem).
function clearBasket(storage) {
  throw new Error('TODO: write clearBasket()');
}

// TODO 4: save the branch code (for example 'NKW') under BRANCH_KEY.
// Return true or false, like saveBasket.
function saveBranch(storage, code) {
  throw new Error('TODO: write saveBranch()');
}

// TODO 5: return the saved branch code, but ONLY if it is in allowedCodes.
// Otherwise (nothing saved, or an old code) return fallback.
function loadBranch(storage, allowedCodes, fallback) {
  throw new Error('TODO: write loadBranch()');
}

// This line lets Node's test runner use the functions. The browser ignores it.
if (typeof module !== 'undefined') module.exports = { BASKET_KEY, BRANCH_KEY, saveBasket, loadBasket, clearBasket, saveBranch, loadBranch };
