// run-sql.js: a backup for when you cannot use the SQL sandbox (for example, offline).
// It loads the three Nkwen tables from the data/ folder into SQLite and runs your queries.
// Run:  node run-sql.js            (runs queries.sql)
//       node run-sql.js other.sql  (runs another file)
// Needs Node.js 22.13 or newer. Ignore the "SQLite is an experimental feature" warning.
const fs = require('node:fs');
const path = require('node:path');
const { DatabaseSync } = require('node:sqlite');

const db = new DatabaseSync(':memory:');
const tables = {
  products: 'id INTEGER, sku TEXT, name TEXT, category TEXT, unit TEXT, price_fcfa INTEGER',
  branches: 'code TEXT, name TEXT, city TEXT, region TEXT, manager TEXT, opened_year INTEGER, status TEXT',
  stock: 'product_id INTEGER, branch_code TEXT, quantity INTEGER',
};

for (const [table, columns] of Object.entries(tables)) {
  db.exec(`CREATE TABLE ${table} (${columns})`);
  const [header, ...lines] = fs.readFileSync(path.join(__dirname, 'data', `${table}.csv`), 'utf8').trim().split(/\r?\n/);
  const names = header.split(',');
  const insert = db.prepare(`INSERT INTO ${table} VALUES (${names.map(() => '?').join(', ')})`);
  for (const line of lines) {
    insert.run(...line.split(','));
  }
}

const file = process.argv[2] || 'queries.sql';
const sql = fs.readFileSync(path.join(__dirname, file), 'utf8').replace(/--[^\n]*/g, '');
const statements = sql.split(';').map((s) => s.trim()).filter(Boolean);
if (statements.length === 0) console.log(`No queries found in ${file} yet.`);

for (const statement of statements) {
  console.log(`\n> ${statement.replace(/\s+/g, ' ')}`);
  try {
    console.table(db.prepare(statement).all());
  } catch (error) {
    console.log(`  SQL error: ${error.message}`);
  }
}
