// shop.js: small helper functions for the Nkwen team site.
// They do not touch the page, so Node can test them. Add your feature's functions at the end.

// 36005 -> "36,005 FCFA"
function formatFcfa(amount) {
  return `${amount.toLocaleString('en-US')} FCFA`;
}

// The products of one category, in A-Z order by name.
function productsInCategory(products, category) {
  return products
    .filter((p) => p.category === category)
    .sort((a, b) => a.name.localeCompare(b.name));
}

if (typeof module !== 'undefined') module.exports = { formatFcfa, productsInCategory };
