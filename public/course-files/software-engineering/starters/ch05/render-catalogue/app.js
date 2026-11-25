// app.js: connects the API (nkwen-api.js) and the view rules (view.js) to the page.
// Uses getProducts and getBranches (nkwen-api.js), formatFcfa, stockStatus and stockText (format.js),
// and branchOptionLabel and pageState (view.js).

const branchSelect = document.querySelector('#branch');
const practiceSelect = document.querySelector('#practice');
const statusBox = document.querySelector('#status');
const retryButton = document.querySelector('#retry');
const grid = document.querySelector('#products');

let branches = [];

// Extra query parameters for the practice modes.
function practiceParams() {
  if (practiceSelect.value === 'slow') return { delay: 2500 };
  if (practiceSelect.value === 'error') return { fail: 500 };
  return {};
}

// TODO A: showState(state) updates the status box for a { kind, message } object.
//   - statusBox.textContent = state.message
//   - statusBox.className = 'status status-' + state.kind
//   - show the retry button only for the 'error' kind (retryButton.hidden = ...)
//   - hide the grid unless the kind is 'ready' (grid.hidden = ...)
function showState(state) {
  // TODO
}

// TODO B: renderProducts(products, branchCode) builds one card per product.
//   Start with grid.replaceChildren() to remove old cards.
//   The quantity at the chosen branch is product.stock[branchCode].
//   For each product, create:
//     <article class="card">
//       <h3>name</h3>
//       <p class="category">category</p>
//       <p class="price">formatFcfa(price_fcfa)</p>
//       <span class="badge badge-STATUS">stockText(quantity)</span>   (STATUS = stockStatus(quantity))
//     </article>
//   Use document.createElement and textContent (not innerHTML) for data from the API.
function renderProducts(products, branchCode) {
  // TODO
}

// TODO C: loadProducts() fetches the products for the chosen branch and shows the right state.
//   1. showState(pageState({ status: 'loading' }))
//   2. try: const products = await getProducts({ branch: code, ...practiceParams() })
//      find the branch object for the code, then
//      const state = pageState({ status: 'done', products, branch })
//      showState(state); if state.kind is 'ready', renderProducts(products, code)
//   3. catch (error): console.error(error); showState(pageState({ status: 'error' }))
async function loadProducts() {
  // TODO
}

// Fills the branch drop-down from the API, then loads the products. (Already done for you.)
async function start() {
  try {
    branches = await getBranches();
    branchSelect.replaceChildren();
    for (const branch of branches) {
      const option = document.createElement('option');
      option.value = branch.code;
      option.textContent = branchOptionLabel(branch);
      branchSelect.append(option);
    }
  } catch (error) {
    console.error(error);
  }
  await loadProducts();
}

branchSelect.addEventListener('change', loadProducts);
practiceSelect.addEventListener('change', loadProducts);
retryButton.addEventListener('click', loadProducts);
start();
