// view.js: small "pure" functions that decide WHAT to show.
// They do not touch the page, so Node can test them. app.js uses them to update the page.

// formatFcfa, stockStatus and stockText are in format.js (from Chapter 4).

// 1. branchOptionLabel(branch): the text in the branch drop-down.
//    open branch    -> "Mile 4 (Bamenda)"
//    planned branch -> "Limbe Down Beach (opening 2027)"   (use branch.opened_year)
function branchOptionLabel(branch) {
  // TODO
}

// 2. pageState({ status, products, branch }): which of the four states to show.
//    Return an object { kind, message } where kind is one of:
//    'loading' when status is 'loading'                         (message such as "Loading products...")
//    'error'   when status is 'error'                           (message must say what to do: "...try again")
//    'empty'   when the branch is planned (not open yet)        (message must name the branch)
//    'empty'   when there are no products                       (for example "No products found.")
//    'ready'   otherwise                                        (for example "20 products")
//    Check the planned branch BEFORE checking the products.
function pageState({ status, products = [], branch = null }) {
  // TODO
}

if (typeof module !== 'undefined') {
  module.exports = { branchOptionLabel, pageState };
}
