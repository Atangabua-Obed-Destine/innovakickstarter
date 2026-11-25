// clean.js: clean and check the rows of the old 2019 price list.
// Each function does one small job. Run the self-checks with:  node --test

// 1. parsePrice(value): turn a messy price into a number, or null if impossible.
//    Numbers stay numbers:          1250.0 -> 1250,  -300 -> -300
//    Strings lose "FCFA", "F", commas and spaces: "2,500 FCFA" -> 2500,  "75 F" -> 75
//    null, undefined, "" or text with no number -> null
//    Hint: value.replace(/FCFA|F|,|\s/gi, '') then Number(...). Check with Number.isFinite.
function parsePrice(value) {
  // TODO
}

// 2. cleanName(value): trim spaces at both ends and squeeze double spaces.
//    Anything that is not a string becomes ''.
function cleanName(value) {
  // TODO
}

// 3. cleanCategory(value, allowed): match the category to the official list,
//    ignoring upper/lower case: ('oILS', ['Oils', 'Dairy']) -> 'Oils'.
//    Return null when the value is missing or not in the list.
function cleanCategory(value, allowed) {
  // TODO
}

// 4. cleanRow(row, allowed): check one row.
//    Row shape from the API: { item, price, cat, note? }
//    REJECT the row (it cannot be used) when:
//      - the name is empty                      -> reason 'missing name'
//      - the price is null                      -> reason 'missing or unreadable price'
//      - the price is 0 or less                 -> reason 'price must be more than 0'
//      - the price is not a whole number        -> reason 'price must be a whole number'
//    Return { ok: false, row, reasons: [...] } for a rejected row (list every reason).
//    Otherwise return { ok: true, product: { name, price_fcfa, category }, warnings: [...] }
//    where warnings are problems you can live with, such as a missing category
//    or a note on the row. YOU decide which warnings to add; explain it in DECISIONS.md.
function cleanRow(row, allowed) {
  // TODO
}

// 5. cleanPriceList(rows, allowed): run cleanRow on every row.
//    Return { clean: [product objects with their warnings], rejected: [rejected results] }.
//    Every row must end up in exactly one of the two lists.
function cleanPriceList(rows, allowed) {
  // TODO
}

// 6. compareWithCatalogue(cleanProducts, catalogue): compare old prices with today's.
//    Match names ignoring case and extra spaces. For each clean product return
//    { name, legacy_price, current_price, status } where status is
//    'same', 'changed' or 'not found' (current_price is null when not found).
function compareWithCatalogue(cleanProducts, catalogue) {
  // TODO
}

if (typeof module !== 'undefined') {
  module.exports = { parsePrice, cleanName, cleanCategory, cleanRow, cleanPriceList, compareWithCatalogue };
}
