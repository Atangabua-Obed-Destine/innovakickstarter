// Nkwen Traders data (a copy of the course data, 25 Nov 2026). Prices in FCFA.
const NKWEN = {
  branches: [
    { code: "NKW", name: "Nkwen", city: "Bamenda", region: "North-West", manager: "Mrs. Enow Patience", opened_year: 2019, status: "open" },
    { code: "ML4", name: "Mile 4", city: "Bamenda", region: "North-West", manager: "Mr. Tanyi Godlove", opened_year: 2021, status: "open" },
    { code: "CAV", name: "Commercial Avenue", city: "Bamenda", region: "North-West", manager: "Mrs. Fon Lydia", opened_year: 2022, status: "open" },
    { code: "BAF", name: "Bafoussam Centre", city: "Bafoussam", region: "West", manager: "Mr. Kenfack Roland", opened_year: 2026, status: "open" },
    { code: "LMB", name: "Limbe Down Beach", city: "Limbe", region: "South-West", manager: "Mr. Ekane Joseph", opened_year: 2027, status: "planned" },
  ],
  products: [
    { id: 1, name: "Bread (loaf)", category: "Bakery", unit: "loaf", price_fcfa: 600 },
    { id: 2, name: "Milk Powder 400g", category: "Dairy", unit: "tin", price_fcfa: 2875 },
    { id: 3, name: "Beans (Red)", category: "Grains", unit: "kg", price_fcfa: 930 },
    { id: 4, name: "Beans (White)", category: "Grains", unit: "kg", price_fcfa: 845 },
    { id: 5, name: "Rice 25kg", category: "Grains", unit: "bag", price_fcfa: 19055 },
    { id: 6, name: "Rice 50kg", category: "Grains", unit: "bag", price_fcfa: 36005 },
    { id: 7, name: "Maggi Cubes (pack)", category: "Groceries", unit: "pack", price_fcfa: 510 },
    { id: 8, name: "Salt 1kg", category: "Groceries", unit: "bag", price_fcfa: 305 },
    { id: 9, name: "Sugar 1kg", category: "Groceries", unit: "bag", price_fcfa: 760 },
    { id: 10, name: "Tomato Paste (tin)", category: "Groceries", unit: "tin", price_fcfa: 355 },
    { id: 11, name: "Detergent 1kg", category: "Household", unit: "bag", price_fcfa: 1875 },
    { id: 12, name: "Matches (box)", category: "Household", unit: "box", price_fcfa: 100 },
    { id: 13, name: "Soap (bar)", category: "Household", unit: "bar", price_fcfa: 410 },
    { id: 14, name: "Palm Oil 1L", category: "Oils", unit: "bottle", price_fcfa: 1395 },
    { id: 15, name: "Palm Oil 5L", category: "Oils", unit: "jerrycan", price_fcfa: 6600 },
    { id: 16, name: "Vegetable Oil 5L", category: "Oils", unit: "jerrycan", price_fcfa: 7210 },
    { id: 17, name: "Cassava (bag)", category: "Produce", unit: "bag", price_fcfa: 4140 },
    { id: 18, name: "Onions 1kg", category: "Produce", unit: "kg", price_fcfa: 705 },
    { id: 19, name: "Plantain (bunch)", category: "Produce", unit: "bunch", price_fcfa: 2575 },
    { id: 20, name: "Tomatoes 1kg", category: "Produce", unit: "kg", price_fcfa: 820 },
  ],
};

if (typeof module !== 'undefined') module.exports = { NKWEN };
