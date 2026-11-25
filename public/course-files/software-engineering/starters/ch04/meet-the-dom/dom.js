// Meet the DOM. Open index.html, then the Console (F12 > Console).
// Use EXACTLY these variable names: self-check.js looks for them.

// TODO 1: select the <h1> by its id with getElementById.
// Log its text: console.log(branchName.textContent)
const branchName = null;

// TODO 2: select the FIRST element with the class "product" (querySelector).
// Log its text.
const firstProduct = null;

// TODO 3: select ALL elements with the class "product" (querySelectorAll).
// Log how many there are (.length), then loop with forEach and log each name.
const allProducts = null;

// TODO 4: select only the products that have BOTH classes, "product" and "low".
// Hint: in CSS, ".a.b" means "has class a and class b".
const lowStock = null;

// TODO 5: select the link with the id "directions" (querySelector with '#...').
// Log mapLink.href AND mapLink.getAttribute('href'). Why are they different?
const mapLink = null;

// TODO 6: find the name of the most expensive product on the shelf.
// Loop over allProducts. Read each price with Number(item.dataset.price).
// Keep the name (textContent) of the highest price in mostExpensive.
let mostExpensive = '';

// TODO 7: your first change to the page. Set the text of #status to
// "DOM ready: N products found", where N comes from allProducts.length.
