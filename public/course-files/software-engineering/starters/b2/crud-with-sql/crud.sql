-- CRUD with SQL: Create, Read, Update, Delete on the Nkwen data.
-- First run load-nkwen.sql once. Then write each query under its TODO and run it.
-- Run a write (INSERT, UPDATE, DELETE) ONCE. If you make a mistake, run load-nkwen.sql again and redo the writes.
USE nkwen_sql;

-- ===== READ (you met SELECT in Chapter 6) =====
-- Q1. TODO: name and price_fcfa of every product in the Oils category, most expensive first.

-- Q2. TODO: the 3 cheapest products (name, price_fcfa).

-- Q3. TODO: the names of the products that Mile 4 (ML4) has run out of (quantity 0).
--     Hint: JOIN products with stock ON products.id = stock.product_id.


-- ===== CREATE =====
-- Q4. TODO: head office adds a new product. Insert it:
--     id 21, sku NKT-GRO-021, name Garri 1kg, category Groceries, unit bag, price 650 FCFA.

-- Q5. TODO: in ONE INSERT, add its stock: NKW 40, ML4 25, CAV 30, BAF 0.


-- ===== UPDATE =====
-- Q6. The price of Sugar 1kg (id 9) goes up to 800 FCFA.
--     Safe habit: first SELECT with the same WHERE, check you see only Sugar, then UPDATE.
-- TODO: SELECT ... WHERE id = 9;
-- TODO: UPDATE ... WHERE id = 9;

-- Q7. TODO: a delivery of 10 bags of Rice 50kg (id 6) arrives at ML4. Add 10 to its quantity.
--     Use quantity = quantity + 10 (do not type the new number yourself).

-- Q8. TODO: Nkwen (NKW) sells 2 bags of Rice 50kg. Take 2 away, but ONLY if at least 2 are there:
--     add "AND quantity >= 2" to the WHERE. Read the "rows affected" number MySQL prints.


-- ===== DELETE =====
-- Q9. Someone typed a product with a spelling mistake. Run this INSERT once:
INSERT INTO products (id, sku, name, category, unit, price_fcfa) VALUES (22, 'NKT-GRO-022', 'Sugr 1kg', 'Groceries', 'bag', 760);
--     TODO: delete product 22, and only product 22.

-- Q10. Do NOT run this. In a comment, explain what it would do and why it is dangerous:
-- DELETE FROM stock;


-- ===== SUMMARIES =====
-- Q11. TODO: total units in stock per branch (branch_code, total_units), biggest first.

-- Q12. TODO: the value of everything on the shelves at NKW in FCFA: SUM(quantity * price_fcfa).
