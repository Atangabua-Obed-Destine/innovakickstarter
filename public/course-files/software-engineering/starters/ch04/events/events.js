// Make the page react to the customer. NKWEN comes from products.js;
// MESSAGE_LIMIT, charactersLeft and validateCallback come from validation.js.

// ---------- Part A: price check ----------
const productSelect = document.querySelector('#product');
const quantityInput = document.querySelector('#quantity');
const priceTotal = document.querySelector('#price-total');

// Ready for you: one <option> per product.
NKWEN.products.forEach(product => {
  const option = document.createElement('option');
  option.value = product.id;
  option.textContent = product.name;
  productSelect.append(option);
});

// TODO 1: write updateTotal(). It reads the chosen product and the quantity,
// and shows price x quantity in #price-total, like "1,520 FCFA".
// If the quantity is not a whole number of 1 or more, show "Enter a quantity".
function updateTotal() {
}

// TODO 2: call updateTotal when the product changes ('change' event)
// and every time the quantity changes ('input' event). Then call it once now.


// ---------- Part B: character counter ----------
const messageBox = document.querySelector('#message');
const messageCount = document.querySelector('#message-count');

// TODO 3: on every 'input' event of messageBox, show "N characters left"
// in #message-count (use charactersLeft). When N is below 0, add the class
// "over" to #message-count and say "N characters too many". Remove "over"
// again when the message is short enough (classList.toggle can do both).


// ---------- Part C: the call-back form ----------
const callbackForm = document.querySelector('#callback');
const thanks = document.querySelector('#thanks');

// TODO 4: listen for the form's 'submit' event. Inside the listener:
//   a. event.preventDefault() so the page does not reload.
//   b. Build { name, phone, branch, message } from the fields' .value
//   c. const errors = validateCallback(data)
//   d. For each field: put errors[field] (or '') in #<field>-error, and set
//      the field's aria-invalid attribute to 'true' or 'false'.
//   e. If there are errors: move the keyboard focus to the FIRST wrong field
//      (field.focus()) and stop.
//   f. If there are none: show "Thank you, <name>. The <branch name> branch
//      will call you soon." in #thanks, then callbackForm.reset().
