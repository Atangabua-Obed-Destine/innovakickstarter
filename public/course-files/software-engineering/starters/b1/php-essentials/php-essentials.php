<?php
// PHP Essentials for JavaScript Developers - Nkwen Traders practice file.
// Run it:  php php-essentials.php
// Fill in every TODO. The self-check at the bottom tells you what is still wrong.

// ---------- The data (do not change) ----------
// An array of associative arrays: like a JavaScript array of objects.
$products = [
    ['id' => 6,  'name' => 'Rice 50kg',     'category' => 'Grains',    'price_fcfa' => 36005],
    ['id' => 9,  'name' => 'Sugar 1kg',     'category' => 'Groceries', 'price_fcfa' => 760],
    ['id' => 12, 'name' => 'Matches (box)', 'category' => 'Household', 'price_fcfa' => 100],
    ['id' => 13, 'name' => 'Soap (bar)',    'category' => 'Household', 'price_fcfa' => 410],
    ['id' => 15, 'name' => 'Palm Oil 5L',   'category' => 'Oils',      'price_fcfa' => 6600],
];

// A basket: product id => quantity (an associative array used like a JS object/map).
$basket = [6 => 1, 13 => 4, 12 => 10];

// ---------- TODO 1 ----------
// Return the amount with a comma for thousands and " FCFA" after it.
// Example of the format: 1500 gives "1,500 FCFA". Hint: number_format().
function formatFcfa(int $amount): string
{
    // TODO: write this function.
    return '';
}

// ---------- TODO 2 ----------
// Return a label such as "Name (price)" using string interpolation with double quotes,
// for example "Bread (loaf) (600 FCFA)". Use formatFcfa() for the price.
function productLabel(array $product): string
{
    // TODO: write this function.
    return '';
}

// ---------- TODO 3 ----------
// Return the product with this id, or null if there is none.
function findProduct(array $products, int $id): ?array
{
    // TODO: write this function.
    return null;
}

// ---------- TODO 4 ----------
// Return only the products in this category (any letter case: "household" works too),
// as a list with keys 0, 1, 2... Hint: array_filter() then array_values(), and strtolower().
function productsInCategory(array $products, string $category): array
{
    // TODO: write this function.
    return [];
}

// ---------- TODO 5 ----------
// Return the total price of the basket in FCFA (price x quantity for each line).
// Skip any id that is not in $products. Hint: foreach ($basket as $id => $quantity).
function basketTotal(array $products, array $basket): int
{
    // TODO: write this function.
    return 0;
}

// ---------- TODO 6 ----------
// A Branch class. Use constructor property promotion (public string $code, ...).
// isOpen() returns true when status is "open". label() returns "Code - Name, City",
// for example "ML4 - Mile 4, Bamenda".
// TODO: write the Branch class here.

// ---------- TODO 7 ----------
// Make $branches: two Branch objects, Nkwen (NKW, Bamenda, open) and Limbe Down Beach (LMB, Limbe, planned).
$branches = []; // TODO: two Branch objects.

// ---------- Try it ----------
echo productLabel($products[0]) . PHP_EOL;
echo 'Basket total: ' . formatFcfa(basketTotal($products, $basket)) . PHP_EOL;
foreach ($branches as $branch) {
    echo $branch->label() . ($branch->isOpen() ? ' (open)' : ' (not open yet)') . PHP_EOL;
}

// ======================= SELF-CHECK (do not edit below) =======================
$problems = [];
$check = function (bool $ok, string $message) use (&$problems) {
    if (!$ok) {
        $problems[] = $message;
    }
};
$check(formatFcfa(1500) === '1,500 FCFA', 'formatFcfa(1500) should give "1,500 FCFA".');
$check(formatFcfa(100) === '100 FCFA', 'formatFcfa(100) should give "100 FCFA".');
$bread = ['id' => 1, 'name' => 'Bread (loaf)', 'category' => 'Bakery', 'price_fcfa' => 600];
$check(productLabel($bread) === 'Bread (loaf) (600 FCFA)', 'productLabel() should give "Bread (loaf) (600 FCFA)" for bread.');
$check(findProduct($products, 999) === null, 'findProduct() should return null for an id that does not exist.');
$found = findProduct($products, $products[2]['id']);
$check(is_array($found) && $found['name'] === $products[2]['name'], 'findProduct() should return the whole product array for an id that exists.');
$all = 0;
foreach (array_unique(array_column($products, 'category')) as $cat) {
    $part = productsInCategory($products, $cat);
    $all += count($part);
    $check($part === [] || array_keys($part) === range(0, count($part) - 1), "productsInCategory() should return a list with keys 0, 1, 2... (use array_values).");
}
$check($all === count($products), 'Every product should be in exactly one category list.');
$check(count(productsInCategory($products, 'HOUSEHOLD')) === count(productsInCategory($products, 'Household')), 'productsInCategory() should ignore letter case.');
$check(productsInCategory($products, 'Electronics') === [], 'An unknown category should give an empty array.');
$byHand = 0;
foreach ($basket as $id => $qty) {
    foreach ($products as $p) {
        if ($p['id'] === $id) {
            $byHand += $p['price_fcfa'] * $qty;
        }
    }
}
$check(basketTotal($products, $basket) === $byHand, 'basketTotal() should add price x quantity for every line of the basket.');
$check(basketTotal($products, [999 => 3]) === 0, 'basketTotal() should skip ids that are not in $products.');
$check(class_exists('Branch'), 'Create the Branch class (TODO 6).');
if (class_exists('Branch')) {
    $b = new Branch('ML4', 'Mile 4', 'Bamenda', 'open');
    $check($b->label() === 'ML4 - Mile 4, Bamenda', 'label() should give "ML4 - Mile 4, Bamenda" for Mile 4.');
    $check($b->isOpen() === true && (new Branch('X', 'Y', 'Z', 'planned'))->isOpen() === false, 'isOpen() should be true only when status is "open".');
}
$check(isset($branches) && count($branches) === 2 && $branches[0] instanceof Branch, '$branches should hold two Branch objects (TODO 7).');

echo PHP_EOL;
if ($problems === []) {
    echo 'Self-check passed: every TODO works.' . PHP_EOL;
} else {
    $problems = array_values(array_unique($problems));
    echo 'Self-check: ' . count($problems) . ' problem(s) to fix:' . PHP_EOL;
    foreach ($problems as $p) {
        echo ' - ' . $p . PHP_EOL;
    }
    exit(1);
}
