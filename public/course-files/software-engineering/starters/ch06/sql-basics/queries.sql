-- SQL Basics in the Browser: Nkwen Traders
-- Tables: products(id, sku, name, category, unit, price_fcfa)
--         branches(code, name, city, region, manager, opened_year, status)
--         stock(product_id, branch_code, quantity)
-- Write one query under each question. Run each one in the SQL sandbox and
-- copy the result into the comment under it.

-- Warm-up W1: show every column of the first 5 products.
-- TODO

-- Warm-up W2: show all branches (all columns).
-- TODO

-- Q1. Which products are in the Oils category? Show name and price_fcfa, cheapest first.
-- TODO
-- Result:

-- Q2. How many products cost less than 1,000 FCFA? (Name the column cheap_products.)
-- TODO
-- Result:

-- Q3. Which branches are open? Show code, name and opened_year, newest branch first.
-- TODO
-- Result:

-- Q4. How many units of stock (all products added together) are at the Nkwen branch (code NKW)?
-- TODO
-- Result:

-- Q5. Which products are out of stock (quantity 0) at Bafoussam Centre (code BAF)?
--     Show the product NAMES in A-Z order. You need a JOIN.
-- TODO
-- Result:

-- Q6. What is the value of all the stock at Mile 4 (code ML4), in FCFA?
--     Value = price_fcfa x quantity, added up over every product.
-- TODO
-- Result:

-- Bonus (optional). Total units per branch, biggest first. Why does Limbe not appear?
-- TODO
