-- Self-check for "Relationships & Normalization". Every line should say PASS.
USE nkwen_sql;
SELECT 'stock has a two-column primary key' AS checking,
       IF(COUNT(*) = 2, 'PASS', 'FIX: ALTER TABLE stock ADD PRIMARY KEY (product_id, branch_code)') AS result
  FROM information_schema.KEY_COLUMN_USAGE
 WHERE TABLE_SCHEMA = 'nkwen_sql' AND TABLE_NAME = 'stock' AND CONSTRAINT_NAME = 'PRIMARY'
UNION ALL
SELECT 'stock has 2 foreign keys',
       IF(COUNT(*) = 2, 'PASS', CONCAT('FIX: expected 2 foreign keys on stock, found ', COUNT(*)))
  FROM information_schema.REFERENTIAL_CONSTRAINTS
 WHERE CONSTRAINT_SCHEMA = 'nkwen_sql' AND TABLE_NAME = 'stock'
UNION ALL
SELECT 'orders points at branches',
       IF(COUNT(*) = 1, 'PASS', 'FIX: orders.branch_code needs a foreign key to branches(code)')
  FROM information_schema.REFERENTIAL_CONSTRAINTS
 WHERE CONSTRAINT_SCHEMA = 'nkwen_sql' AND TABLE_NAME = 'orders' AND REFERENCED_TABLE_NAME = 'branches'
UNION ALL
SELECT 'order_lines points at orders and products',
       IF(COUNT(DISTINCT REFERENCED_TABLE_NAME) = 2, 'PASS', 'FIX: order_lines needs foreign keys to orders and to products')
  FROM information_schema.REFERENTIAL_CONSTRAINTS
 WHERE CONSTRAINT_SCHEMA = 'nkwen_sql' AND TABLE_NAME = 'order_lines'
UNION ALL
SELECT 'deleting an order deletes its lines',
       IF(MAX(DELETE_RULE) = 'CASCADE', 'PASS', 'FIX: add ON DELETE CASCADE to order_lines.order_id')
  FROM information_schema.REFERENTIAL_CONSTRAINTS
 WHERE CONSTRAINT_SCHEMA = 'nkwen_sql' AND TABLE_NAME = 'order_lines' AND REFERENCED_TABLE_NAME = 'orders'
UNION ALL
SELECT 'no repeated product or manager names',
       IF(COUNT(*) = 0, 'PASS', 'FIX: order_lines and orders should not store product names or managers: use ids and codes')
  FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = 'nkwen_sql' AND TABLE_NAME IN ('orders', 'order_lines')
   AND COLUMN_NAME IN ('product', 'product_name', 'name', 'branch_name', 'manager', 'branch_manager');

-- These need your tables, so they run on their own:
SELECT 'the 4 paper orders and their 9 lines are in' AS checking,
       IF((SELECT COUNT(*) FROM orders) = 4 AND (SELECT COUNT(*) FROM order_lines) = 9, 'PASS',
          'FIX: insert the 4 orders and 9 lines from paper-orders.csv') AS result;
SELECT 'every order total equals the sum of its lines' AS checking,
       IF(COUNT(*) = 0, 'PASS', CONCAT('FIX: check total_fcfa of order ', MIN(o.id))) AS result
  FROM orders AS o
  LEFT JOIN (SELECT order_id, SUM(line_total_fcfa) AS lines_total FROM order_lines GROUP BY order_id) AS l
         ON l.order_id = o.id
 WHERE o.total_fcfa <> COALESCE(l.lines_total, 0);
SELECT 'every line total is quantity x unit price' AS checking,
       IF(COUNT(*) = 0, 'PASS', 'FIX: line_total_fcfa must be quantity * unit_price_fcfa') AS result
  FROM order_lines WHERE line_total_fcfa <> quantity * unit_price_fcfa;
