-- load-nkwen.sql: the Nkwen Traders practice data for MySQL (or MariaDB).
-- Same tables as the SQL sandbox: products, branches, stock.
-- It REPLACES these three tables in the nkwen_sql database, so you can run it again to start over.
CREATE DATABASE IF NOT EXISTS nkwen_sql CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nkwen_sql;

DROP TABLE IF EXISTS order_lines, orders, stock, products, branches;

CREATE TABLE branches (
  code        CHAR(3)      NOT NULL PRIMARY KEY,
  name        VARCHAR(60)  NOT NULL,
  city        VARCHAR(60)  NOT NULL,
  region      VARCHAR(60)  NOT NULL,
  manager     VARCHAR(80)  NOT NULL,
  opened_year SMALLINT     NOT NULL,
  status      VARCHAR(10)  NOT NULL DEFAULT 'open'
);

CREATE TABLE products (
  id         INT          NOT NULL PRIMARY KEY,
  sku        VARCHAR(30)  NOT NULL UNIQUE,
  name       VARCHAR(100) NOT NULL,
  category   VARCHAR(30)  NOT NULL,
  unit       VARCHAR(20)  NOT NULL,
  price_fcfa INT UNSIGNED NOT NULL
);

-- No keys on stock yet: you add them in "Relationships & Normalization".
CREATE TABLE stock (
  product_id  INT          NOT NULL,
  branch_code CHAR(3)      NOT NULL,
  quantity    INT UNSIGNED NOT NULL
);

INSERT INTO branches (code, name, city, region, manager, opened_year, status) VALUES
  ('NKW', 'Nkwen', 'Bamenda', 'North-West', 'Mrs. Enow Patience', 2019, 'open'),
  ('ML4', 'Mile 4', 'Bamenda', 'North-West', 'Mr. Tanyi Godlove', 2021, 'open'),
  ('CAV', 'Commercial Avenue', 'Bamenda', 'North-West', 'Mrs. Fon Lydia', 2022, 'open'),
  ('BAF', 'Bafoussam Centre', 'Bafoussam', 'West', 'Mr. Kenfack Roland', 2026, 'open'),
  ('LMB', 'Limbe Down Beach', 'Limbe', 'South-West', 'Mr. Ekane Joseph', 2027, 'planned');

INSERT INTO products (id, sku, name, category, unit, price_fcfa) VALUES
  (1, 'NKT-BAK-001', 'Bread (loaf)', 'Bakery', 'loaf', 600),
  (2, 'NKT-DAI-002', 'Milk Powder 400g', 'Dairy', 'tin', 2875),
  (3, 'NKT-GRA-003', 'Beans (Red)', 'Grains', 'kg', 930),
  (4, 'NKT-GRA-004', 'Beans (White)', 'Grains', 'kg', 845),
  (5, 'NKT-GRA-005', 'Rice 25kg', 'Grains', 'bag', 19055),
  (6, 'NKT-GRA-006', 'Rice 50kg', 'Grains', 'bag', 36005),
  (7, 'NKT-GRO-007', 'Maggi Cubes (pack)', 'Groceries', 'pack', 510),
  (8, 'NKT-GRO-008', 'Salt 1kg', 'Groceries', 'bag', 305),
  (9, 'NKT-GRO-009', 'Sugar 1kg', 'Groceries', 'bag', 760),
  (10, 'NKT-GRO-010', 'Tomato Paste (tin)', 'Groceries', 'tin', 355),
  (11, 'NKT-HOU-011', 'Detergent 1kg', 'Household', 'bag', 1875),
  (12, 'NKT-HOU-012', 'Matches (box)', 'Household', 'box', 100),
  (13, 'NKT-HOU-013', 'Soap (bar)', 'Household', 'bar', 410),
  (14, 'NKT-OIL-014', 'Palm Oil 1L', 'Oils', 'bottle', 1395),
  (15, 'NKT-OIL-015', 'Palm Oil 5L', 'Oils', 'jerrycan', 6600),
  (16, 'NKT-OIL-016', 'Vegetable Oil 5L', 'Oils', 'jerrycan', 7210),
  (17, 'NKT-PRO-017', 'Cassava (bag)', 'Produce', 'bag', 4140),
  (18, 'NKT-PRO-018', 'Onions 1kg', 'Produce', 'kg', 705),
  (19, 'NKT-PRO-019', 'Plantain (bunch)', 'Produce', 'bunch', 2575),
  (20, 'NKT-PRO-020', 'Tomatoes 1kg', 'Produce', 'kg', 820);

INSERT INTO stock (product_id, branch_code, quantity) VALUES
  (1, 'NKW', 37),
  (1, 'ML4', 0),
  (1, 'CAV', 53),
  (1, 'BAF', 16),
  (2, 'NKW', 74),
  (2, 'ML4', 37),
  (2, 'CAV', 0),
  (2, 'BAF', 53),
  (3, 'NKW', 21),
  (3, 'ML4', 74),
  (3, 'CAV', 37),
  (3, 'BAF', 0),
  (4, 'NKW', 58),
  (4, 'ML4', 21),
  (4, 'CAV', 74),
  (4, 'BAF', 3),
  (5, 'NKW', 5),
  (5, 'ML4', 58),
  (5, 'CAV', 3),
  (5, 'BAF', 74),
  (6, 'NKW', 42),
  (6, 'ML4', 3),
  (6, 'CAV', 58),
  (6, 'BAF', 21),
  (7, 'NKW', 3),
  (7, 'ML4', 42),
  (7, 'CAV', 5),
  (7, 'BAF', 58),
  (8, 'NKW', 26),
  (8, 'ML4', 79),
  (8, 'CAV', 42),
  (8, 'BAF', 0),
  (9, 'NKW', 63),
  (9, 'ML4', 26),
  (9, 'CAV', 0),
  (9, 'BAF', 42),
  (10, 'NKW', 10),
  (10, 'ML4', 0),
  (10, 'CAV', 26),
  (10, 'BAF', 79),
  (11, 'NKW', 0),
  (11, 'ML4', 10),
  (11, 'CAV', 63),
  (11, 'BAF', 3),
  (12, 'NKW', 84),
  (12, 'ML4', 47),
  (12, 'CAV', 3),
  (12, 'BAF', 63),
  (13, 'NKW', 31),
  (13, 'ML4', 3),
  (13, 'CAV', 47),
  (13, 'BAF', 10),
  (14, 'NKW', 3),
  (14, 'ML4', 31),
  (14, 'CAV', 84),
  (14, 'BAF', 47),
  (15, 'NKW', 15),
  (15, 'ML4', 68),
  (15, 'CAV', 31),
  (15, 'BAF', 84),
  (16, 'NKW', 52),
  (16, 'ML4', 15),
  (16, 'CAV', 68),
  (16, 'BAF', 31),
  (17, 'NKW', 89),
  (17, 'ML4', 52),
  (17, 'CAV', 15),
  (17, 'BAF', 68),
  (18, 'NKW', 36),
  (18, 'ML4', 89),
  (18, 'CAV', 52),
  (18, 'BAF', 3),
  (19, 'NKW', 73),
  (19, 'ML4', 36),
  (19, 'CAV', 3),
  (19, 'BAF', 0),
  (20, 'NKW', 20),
  (20, 'ML4', 3),
  (20, 'CAV', 0),
  (20, 'BAF', 89);

SELECT (SELECT COUNT(*) FROM branches) AS branches, (SELECT COUNT(*) FROM products) AS products, (SELECT COUNT(*) FROM stock) AS stock_rows;
