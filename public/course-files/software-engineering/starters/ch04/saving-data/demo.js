// The page code. Most of it is ready. You only connect it to storage.js.
const branchSelect = document.querySelector('#branch');
const basketList = document.querySelector('#basket');
const countText = document.querySelector('#count');
const openCodes = NKWEN.branches.filter(b => b.status === 'open').map(b => b.code);

// TODO 1: start from the saved basket instead of an empty one:
//   let basket = loadBasket(localStorage);
let basket = [];

// TODO 2: start from the saved branch:
//   branchSelect.value = loadBranch(localStorage, openCodes, 'NKW');

function showBasket() {
  basketList.replaceChildren();
  basket.forEach(line => {
    const product = NKWEN.products.find(p => p.id === line.id);
    const item = document.createElement('li');
    item.textContent = `${line.qty} x ${product.name}`;
    basketList.append(item);
  });
  countText.textContent = basket.reduce((sum, line) => sum + line.qty, 0);
}

function addOne(id) {
  const line = basket.find(l => l.id === id);
  if (line) {
    basket = basket.map(l => (l.id === id ? { id, qty: l.qty + 1 } : l));
  } else {
    basket = [...basket, { id, qty: 1 }];
  }
  // TODO 3: save the basket after every change: saveBasket(localStorage, basket)
  showBasket();
}

document.querySelectorAll('.buttons button').forEach(button => {
  button.addEventListener('click', () => addOne(Number(button.dataset.id)));
});

document.querySelector('#clear').addEventListener('click', () => {
  basket = [];
  // TODO 4: remove the saved basket too: clearBasket(localStorage)
  showBasket();
});

branchSelect.addEventListener('change', () => {
  // TODO 5: save the new branch: saveBranch(localStorage, branchSelect.value)
});

showBasket();
