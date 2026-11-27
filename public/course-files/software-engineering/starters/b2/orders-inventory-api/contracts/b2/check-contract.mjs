// Runs the Nkwen Products API contract against any running API, in any language.
// Usage:  node check-contract.mjs http://127.0.0.1:8000
//         node check-contract.mjs https://your-app.onrender.com
// Needs Node.js 18 or newer (you installed Node 22 in the core chapters).
import { readFileSync } from 'node:fs';

const base = (process.argv[2] || '').replace(/\/+$/, '');
if (!base.startsWith('http')) {
  console.log('Give the base URL of your API, for example: node check-contract.mjs http://127.0.0.1:8000');
  process.exit(1);
}
const contract = JSON.parse(readFileSync(new URL('./contract.json', import.meta.url), 'utf8'));
const vars = { rand: Math.random().toString(36).slice(2, 10).padEnd(8, '0') };

// Replace {{name}} with a saved value.
const fill = (text) => text.replace(/\{\{(\w+)\}\}/g, (all, name) => (name in vars ? String(vars[name]) : all));

// Read "data.0.id" from a JSON value. Returns { found, value }.
function read(json, path) {
  let value = json;
  for (const key of path.split('.')) {
    if (value === null || typeof value !== 'object' || !(key in value)) return { found: false };
    value = value[key];
  }
  return { found: true, value };
}

const same = (a, b) => JSON.stringify(a) === JSON.stringify(b);
let passed = 0;

for (const check of contract.checks) {
  const problems = [];
  const path = fill(check.path);
  const headers = { Accept: 'application/json', 'Content-Type': 'application/json' };
  for (const [k, v] of Object.entries(check.headers || {})) headers[k] = fill(v);
  const body = check.body === null || check.body === undefined ? undefined : fill(JSON.stringify(check.body));

  let status = 0;
  let json = null;
  try {
    const res = await fetch(base + path, { method: check.method, headers, body, signal: AbortSignal.timeout(60000) });
    status = res.status;
    const text = await res.text();
    if (text.trim() !== '') {
      try { json = JSON.parse(text); } catch { problems.push('the response is not JSON (starts with: ' + text.slice(0, 60).replace(/\s+/g, ' ') + ')'); }
    }
  } catch (error) {
    problems.push('could not reach the API (' + error.message + '). Is it running? Is the URL right?');
  }

  const e = check.expect || {};
  const okStatuses = Array.isArray(e.status) ? e.status : [e.status];
  const statusOk = okStatuses.includes(status);
  if (status && !statusOk) {
    const hint = json && typeof json.message === 'string' ? ` (message: "${json.message.slice(0, 80)}")` : '';
    problems.push(`expected status ${okStatuses.join(' or ')}, got ${status}${hint}`);
  }
  if (statusOk) {
    for (const p of e.has || []) if (!read(json, p).found) problems.push(`missing key "${p}"`);
    for (const [p, want] of Object.entries(e.equals || {})) {
      const got = read(json, p);
      if (!got.found) problems.push(`missing key "${p}" (expected ${JSON.stringify(want)})`);
      else if (!same(got.value, want)) problems.push(`"${p}" should be ${JSON.stringify(want)}, got ${JSON.stringify(got.value)}`);
    }
    for (const [p, n] of Object.entries(e.min_count || {})) {
      const got = read(json, p);
      if (!got.found || !Array.isArray(got.value)) problems.push(`"${p}" should be a list`);
      else if (got.value.length < n) problems.push(`"${p}" should have at least ${n} items, got ${got.value.length}`);
    }
  }
  for (const [name, p] of Object.entries(check.save || {})) {
    const got = read(json, p);
    if (got.found) vars[name] = got.value;
  }

  const label = `${check.method} ${path}`;
  if (problems.length === 0) {
    passed++;
    console.log(`PASS  ${check.name}  (${label})`);
  } else {
    console.log(`FAIL  ${check.name}  (${label})`);
    for (const p of problems) console.log(`      - ${p}`);
  }
}

console.log(`\n${passed} of ${contract.checks.length} checks passed.`);
process.exit(passed === contract.checks.length ? 0 : 1);
