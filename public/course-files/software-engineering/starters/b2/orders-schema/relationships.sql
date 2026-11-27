-- Relationships & Normalization: turn the paper order book into related tables.
-- Before you start: run load-nkwen.sql (from CRUD with SQL) to get fresh products, branches and stock.
USE nkwen_sql;

-- ===== Part A: keys for the stock table =====
-- A1. TODO: give stock a PRIMARY KEY made of two columns: (product_id, branch_code).
--     Then add two FOREIGN KEYs: product_id -> products(id) and branch_code -> branches(code).
-- ALTER TABLE stock
--   ADD PRIMARY KEY (...),
--   ADD CONSTRAINT fk_stock_product FOREIGN KEY (...) REFERENCES ...,
--   ADD CONSTRAINT fk_stock_branch  FOREIGN KEY (...) REFERENCES ...;

-- A2. Prove the foreign key works. MySQL must REFUSE this row (there is no product 999).
--     Copy the error message into your submission.
-- INSERT INTO stock (product_id, branch_code, quantity) VALUES (999, 'NKW', 5);


-- ===== Part B: orders and order lines =====
-- B1. TODO: CREATE TABLE orders. One row per order. Use your design from the lesson.
--     It must have: id (INT, primary key), branch_code (a foreign key to branches),
--     customer_name, payment_method, status, total_fcfa (INT UNSIGNED) and created_at (DATETIME).

-- B2. TODO: CREATE TABLE order_lines. One row per product in an order. It must have:
--     id, order_id (foreign key to orders, ON DELETE CASCADE), product_id (foreign key to products),
--     quantity (more than 0: add CHECK (quantity > 0)), unit_price_fcfa, line_total_fcfa,
--     and UNIQUE (order_id, product_id) so a product appears once per order.
--     Do NOT copy the product name or the branch manager into these tables. Why not? (see the lesson)


-- ===== Part C: move the paper orders in =====
-- C1. TODO: insert the 4 orders of paper-orders.csv (ids 1001 to 1004, status 'paid',
--     created_at from the date column, total_fcfa = the sum of the order's lines).
-- C2. TODO: insert the 9 order lines. Use product ids, not names (look them up with SELECT).
--     Keep the unit price written on the paper, even when it differs from today's price.


-- ===== Part D: put the receipt back together =====
-- D1. TODO: the receipt for order 1003: product name, quantity, unit_price_fcfa, line_total_fcfa.
--     JOIN order_lines with products.

-- D2. TODO: one row per order: order id, branch NAME, customer_name, number of lines, total of the lines.
--     JOIN orders, branches and order_lines, then GROUP BY the order.
