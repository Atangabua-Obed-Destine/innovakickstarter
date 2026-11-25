// nkwen-api.js: small helper functions for the Nkwen Traders course API.
// Works in the browser (with a <script> tag) and in Node.js 22 (with require).
// You will reuse this file in the next lessons and in the chapter project.

// The API's base address. (For teachers: Node can use another address with NKWEN_API=...)
const API_BASE =
  (typeof process !== 'undefined' && process.env && process.env.NKWEN_API) ||
  'https://innovakickstarter.com/api/course/nkwen';

// 1. buildUrl(path, params): join the base, the path and the query string.
//    buildUrl('/products')                      -> API_BASE + '/products'
//    buildUrl('/products', { category: 'Oils' }) -> API_BASE + '/products?category=Oils'
//    Skip params that are empty: undefined, null or ''. No '?' when there are no params left.
//    Hint: new URLSearchParams() and its .append(name, value) and .toString() methods.
function buildUrl(path, params = {}) {
  // TODO
}

// 2. getJson(path, params): fetch the URL and return the parsed JSON body.
//    - If fetch itself fails (no internet), throw an Error with a friendly message.
//    - If response.ok is false (status 400-599), throw an Error whose message
//      includes the status number and the API's "message", for example:
//      "Request failed (404): No product with id 99."
//      Also set error.status = response.status so callers can check it.
async function getJson(path, params = {}) {
  // TODO
}

// 3. getProducts(params): return the "products" array from GET /products.
async function getProducts(params = {}) {
  // TODO
}

// 4. getBranches(): return the "branches" array from GET /branches.
async function getBranches() {
  // TODO
}

if (typeof module !== 'undefined') {
  module.exports = { API_BASE, buildUrl, getJson, getProducts, getBranches };
}
