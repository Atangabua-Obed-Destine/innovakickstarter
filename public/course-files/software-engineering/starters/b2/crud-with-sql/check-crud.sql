-- Self-check for "CRUD with SQL". Run it after your write tasks (Q4 to Q9).
-- It checks that the data is in the state the tasks describe. Every line should say PASS.
USE nkwen_sql;
SELECT 'Q4 Garri 1kg was added' AS checking,
       IF(COUNT(*) = 1, 'PASS', 'FIX: insert product 21 with exactly the values in Q4') AS result
  FROM products
 WHERE id = 21 AND sku = 'NKT-GRO-021' AND name = 'Garri 1kg' AND category = 'Groceries' AND unit = 'bag' AND price_fcfa = 650
UNION ALL
SELECT 'Q5 Garri has stock at the 4 open branches',
       IF(COUNT(*) = 4 AND SUM(quantity) = 95, 'PASS', 'FIX: one INSERT with 4 rows for product 21')
  FROM stock WHERE product_id = 21
UNION ALL
SELECT 'Q6 only Sugar changed price',
       IF(SUM(id = 9 AND price_fcfa = 800) = 1 AND SUM(price_fcfa = 800) = 1, 'PASS',
          'FIX: Sugar (id 9) should cost 800, and no other product. Reload and redo the writes.')
  FROM products
UNION ALL
SELECT 'Q7 and Q8 used quantity = quantity +/- n',
       IF((SELECT quantity FROM stock WHERE product_id = 6 AND branch_code = 'ML4') = 13
          AND (SELECT quantity FROM stock WHERE product_id = 6 AND branch_code = 'NKW') = 40,
          'PASS', 'FIX: run each write once on fresh data (load-nkwen.sql gives ML4 3 bags and NKW 42 bags)')
UNION ALL
SELECT 'Q9 the typo product is gone, and only it',
       IF(SUM(id = 22) = 0 AND COUNT(*) = 21, 'PASS', 'FIX: DELETE FROM products WHERE id = 22')
  FROM products
UNION ALL
SELECT 'No stock row went below zero or was deleted',
       IF(COUNT(*) = 84, 'PASS', CONCAT('FIX: expected 84 stock rows (80 + 4 for Garri), found ', COUNT(*)))
  FROM stock;
