-- MySQL Setup & Your First Table
-- Run this file in the MySQL command line, or paste it into phpMyAdmin's SQL tab.
-- Lines that start with -- are comments: MySQL ignores them.

-- 1. A database for all your hand-written SQL practice.
--    utf8mb4 stores every language correctly (French accents, emojis, the FCFA sign).
CREATE DATABASE IF NOT EXISTS nkwen_sql CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nkwen_sql;

-- 2. TODO: create the table "branches" with these 7 columns:
--    code         exactly 3 letters (CHAR(3)), the PRIMARY KEY
--    name         text up to 60 characters, required (NOT NULL)
--    city         text up to 60 characters, required
--    region       text up to 60 characters, required
--    manager      text up to 80 characters, required
--    opened_year  a year: SMALLINT, required
--    status       text up to 10 characters, required, DEFAULT 'open'
-- CREATE TABLE branches (
--   ...
-- );


-- 3. TODO: insert two branches from the story (see the lesson):
--    Nkwen (NKW) and Limbe Down Beach (LMB). Limbe is not open yet: its status is 'planned'.
-- INSERT INTO branches (code, name, city, region, manager, opened_year, status) VALUES
--   (...),
--   (...);


-- 4. Look at your work. Take your screenshot here.
DESCRIBE branches;
SELECT * FROM branches;

-- 5. TODO: insert NKW a second time (copy your NKW row into a new INSERT).
--    MySQL must refuse it. Copy the error message into your submission.
