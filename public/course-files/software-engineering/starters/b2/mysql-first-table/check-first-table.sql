-- Self-check for "MySQL Setup & Your First Table". Run it after first-table.sql.
-- Every line should say PASS. A FIX line tells you what to change.
SELECT 'The branches table exists' AS checking,
       IF(COUNT(*) = 1, 'PASS', 'FIX: create the table branches inside nkwen_sql') AS result
  FROM information_schema.TABLES
 WHERE TABLE_SCHEMA = 'nkwen_sql' AND TABLE_NAME = 'branches'
UNION ALL
SELECT 'It has the 7 columns',
       IF(COUNT(*) = 7, 'PASS', CONCAT('FIX: expected 7 columns, found ', COUNT(*)))
  FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = 'nkwen_sql' AND TABLE_NAME = 'branches'
   AND COLUMN_NAME IN ('code', 'name', 'city', 'region', 'manager', 'opened_year', 'status')
UNION ALL
SELECT 'code is the primary key',
       IF(COUNT(*) = 1, 'PASS', 'FIX: add PRIMARY KEY to the code column')
  FROM information_schema.KEY_COLUMN_USAGE
 WHERE TABLE_SCHEMA = 'nkwen_sql' AND TABLE_NAME = 'branches'
   AND CONSTRAINT_NAME = 'PRIMARY' AND COLUMN_NAME = 'code'
UNION ALL
SELECT 'name, city and manager are required',
       IF(SUM(IS_NULLABLE = 'NO') = 3, 'PASS', 'FIX: add NOT NULL to name, city and manager')
  FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = 'nkwen_sql' AND TABLE_NAME = 'branches'
   AND COLUMN_NAME IN ('name', 'city', 'manager')
UNION ALL
SELECT 'status has a default of open',
       IF(MAX(COLUMN_DEFAULT) IN ('open', '''open'''), 'PASS', 'FIX: add DEFAULT ''open'' to status')
  FROM information_schema.COLUMNS
 WHERE TABLE_SCHEMA = 'nkwen_sql' AND TABLE_NAME = 'branches' AND COLUMN_NAME = 'status';

-- This one needs the table, so it runs on its own:
SELECT 'NKW and LMB are in the table' AS checking,
       IF(COUNT(*) = 2, 'PASS', 'FIX: insert the NKW and LMB rows') AS result
  FROM nkwen_sql.branches
 WHERE code IN ('NKW', 'LMB');
