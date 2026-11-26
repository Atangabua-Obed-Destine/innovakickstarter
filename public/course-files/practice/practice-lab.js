/*
 * Practice Lab engine: runs SQL (sql.js) and Python (Pyodide) in the browser and
 * turns the result into a canonical string the server checks against a hash.
 * Mirrors practice_canon.py; keep the two in sync.
 */
(function () {
  'use strict';

  const SQLJS_BASE = 'https://cdnjs.cloudflare.com/ajax/libs/sql.js/1.10.3/';
  const PYODIDE_BASE = 'https://cdn.jsdelivr.net/pyodide/v0.26.4/full/';

  function loadScript(src) {
    return new Promise((resolve, reject) => {
      if (document.querySelector(`script[data-src="${src}"]`)) return resolve();
      const s = document.createElement('script');
      s.src = src;
      s.dataset.src = src;
      s.onload = resolve;
      s.onerror = () => reject(new Error('Could not download ' + src + '. Check your connection and try again.'));
      document.head.appendChild(s);
    });
  }

  // ---------------------------------------------------------------- CSV
  function parseCsv(text) {
    const rows = [];
    let row = [], field = '', inQuotes = false;
    for (let i = 0; i < text.length; i++) {
      const c = text[i];
      if (inQuotes) {
        if (c === '"') {
          if (text[i + 1] === '"') { field += '"'; i++; } else inQuotes = false;
        } else field += c;
      } else if (c === '"') inQuotes = true;
      else if (c === ',') { row.push(field); field = ''; }
      else if (c === '\n' || c === '\r') {
        if (c === '\r' && text[i + 1] === '\n') i++;
        row.push(field); field = '';
        if (row.length > 1 || row[0] !== '') rows.push(row);
        row = [];
      } else field += c;
    }
    if (field !== '' || row.length) { row.push(field); rows.push(row); }
    return rows;
  }

  // Same rule as pandas.read_csv -> to_sql: all ints -> INTEGER, all numbers -> REAL, else TEXT.
  function columnType(values) {
    const present = values.filter(v => v !== '');
    if (present.length === 0) return 'TEXT';
    if (present.every(v => /^-?\d+$/.test(v))) return values.length === present.length ? 'INTEGER' : 'REAL';
    if (present.every(v => /^-?(\d+\.?\d*|\.\d+)([eE][-+]?\d+)?$/.test(v))) return 'REAL';
    return 'TEXT';
  }

  // ---------------------------------------------------------------- canonical form
  function cell(x) {
    if (x === null || x === undefined) return null;
    if (typeof x === 'number') {
      if (!isFinite(x)) return null;
      // Same arithmetic as practice_canon.py: floor(x * 100 + 0.5) / 100.
      return Math.floor(x * 100 + 0.5) / 100;
    }
    return String(x).trim();
  }

  function canonicalSqlRows(rows, ordered) {
    const strings = rows.map(r => JSON.stringify(r.map(cell)));
    if (!ordered) strings.sort();
    return '[' + strings.join(',') + ']';
  }

  // ---------------------------------------------------------------- SQL
  let sqlDbPromise = null;

  async function sqlDb(tables, onStatus) {
    if (sqlDbPromise) return sqlDbPromise;
    sqlDbPromise = (async () => {
      onStatus && onStatus('Loading the SQL engine (about 1 MB, once)...');
      await loadScript(SQLJS_BASE + 'sql-wasm.js');
      const SQL = await window.initSqlJs({ locateFile: f => SQLJS_BASE + f });
      const db = new SQL.Database();
      for (const [name, url] of Object.entries(tables || {})) {
        onStatus && onStatus(`Loading table ${name}...`);
        const res = await fetch(url);
        if (!res.ok) throw new Error(`Could not load the ${name} table.`);
        const [header, ...data] = parseCsv(await res.text());
        const types = header.map((_, i) => columnType(data.map(r => r[i] ?? '')));
        const cols = header.map((h, i) => `"${h.replace(/"/g, '')}" ${types[i]}`).join(', ');
        db.run(`CREATE TABLE "${name}" (${cols})`);
        const stmt = db.prepare(`INSERT INTO "${name}" VALUES (${header.map(() => '?').join(',')})`);
        db.run('BEGIN');
        for (const r of data) {
          stmt.run(header.map((_, i) => {
            const v = r[i] ?? '';
            if (v === '') return null;
            if (types[i] === 'INTEGER') return parseInt(v, 10);
            if (types[i] === 'REAL') return parseFloat(v);
            return v;
          }));
        }
        db.run('COMMIT');
        stmt.free();
      }
      onStatus && onStatus('');
      return db;
    })();
    sqlDbPromise.catch(() => { sqlDbPromise = null; });
    return sqlDbPromise;
  }

  async function runSql(query, { tables, ordered, onStatus }) {
    const db = await sqlDb(tables, onStatus);
    const results = db.exec(query);
    const last = results[results.length - 1];
    if (!last) return { columns: [], rows: [], canonical: '[]' };
    return { columns: last.columns, rows: last.values, canonical: canonicalSqlRows(last.values, ordered) };
  }

  async function sqlSchema(tables, onStatus) {
    const db = await sqlDb(tables, onStatus);
    const out = [];
    for (const name of Object.keys(tables || {})) {
      const info = db.exec(`PRAGMA table_info("${name}")`)[0];
      out.push({ name, columns: info ? info.values.map(v => `${v[1]} ${v[2]}`) : [] });
    }
    return out;
  }

  // ---------------------------------------------------------------- Python
  let pyPromise = null;
  let pandasLoaded = false;

  async function python({ needsPandas, files, canonUrl, onStatus }) {
    if (!pyPromise) {
      pyPromise = (async () => {
        onStatus && onStatus('Downloading Python (about 10 MB, once; it stays in your browser cache)...');
        await loadScript(PYODIDE_BASE + 'pyodide.js');
        const py = await window.loadPyodide({ indexURL: PYODIDE_BASE });
        const canon = await (await fetch(canonUrl)).text();
        py.runPython(canon);
        try { py.FS.mkdir('/data'); } catch (e) { /* exists */ }
        return py;
      })();
      pyPromise.catch(() => { pyPromise = null; });
    }
    const py = await pyPromise;
    if (needsPandas && !pandasLoaded) {
      onStatus && onStatus('Downloading pandas (about 12 MB, once)...');
      await py.loadPackage(['numpy', 'pandas']);
      pandasLoaded = true;
    }
    for (const [path, url] of Object.entries(files || {})) {
      if (py.FS.analyzePath(path).exists) continue;
      onStatus && onStatus('Loading data...');
      const res = await fetch(url);
      if (!res.ok) throw new Error('Could not load ' + path);
      py.FS.writeFile(path, new Uint8Array(await res.arrayBuffer()));
    }
    onStatus && onStatus('');
    return py;
  }

  async function runPython(code, { setup, needsPandas, files, canonUrl, onStatus }) {
    const py = await python({ needsPandas, files, canonUrl, onStatus });
    let output = '';
    py.setStdout({ batched: s => { output += s + '\n'; } });
    py.setStderr({ batched: s => { output += s + '\n'; } });
    const ns = py.globals.get('dict')();
    // Fresh namespace per run, with the canonical helper available.
    ns.set('canonical', py.globals.get('canonical'));
    try {
      if (setup) await py.runPythonAsync(setup, { globals: ns });
      await py.runPythonAsync(code, { globals: ns });
      if (!ns.has('answer')) {
        return { output, canonical: null, error: 'Assign your result to a variable named answer.' };
      }
      const canonical = py.runPython('canonical(answer)', { globals: ns });
      const preview = py.runPython('repr(answer)[:2000]', { globals: ns });
      return { output, canonical, preview };
    } catch (err) {
      const msg = String(err.message || err);
      const lines = msg.trim().split('\n');
      return { output, canonical: null, error: lines.slice(-3).join('\n') };
    } finally {
      ns.destroy();
    }
  }

  // ---------------------------------------------------------------- JavaScript
  // Runs the fellow's code in a throwaway Web Worker (no access to the page), with top-level
  // await, console capture and a time limit. The result in `answer` is canonicalised by
  // practice_canon.js, which mirrors practice_canon.py.
  function runJs(code, { setup, canonUrl, timeoutMs = 5000 }) {
    const src = 'importScripts(' + JSON.stringify(canonUrl) + ');\n'
      + 'const __out = [];\n'
      + 'const __fmt = (a) => a.map((x) => { if (typeof x === "string") return x; try { const s = JSON.stringify(x); return s === undefined ? String(x) : s; } catch (e) { return String(x); } }).join(" ");\n'
      + 'console.log = console.info = console.warn = console.error = (...a) => { if (__out.length < 500) __out.push(__fmt(a)); };\n'
      + 'self.onmessage = async (e) => {\n'
      + '  try {\n'
      + '    const AsyncFunction = Object.getPrototypeOf(async function () {}).constructor;\n'
      + '    const fn = new AsyncFunction(e.data.setup + "\\n" + e.data.code + "\\n;return (typeof answer === \\"undefined\\") ? { __noAnswer: true } : answer;");\n'
      + '    const answer = await fn();\n'
      + '    if (answer && answer.__noAnswer) { self.postMessage({ output: __out.join("\\n"), canonical: null, error: "Assign your result to a variable named answer." }); return; }\n'
      + '    let preview; try { preview = JSON.stringify(answer, null, 1); } catch (x) { preview = String(answer); }\n'
      + '    self.postMessage({ output: __out.join("\\n"), canonical: PracticeCanon.canonical(answer), preview: String(preview).slice(0, 2000) });\n'
      + '  } catch (err) {\n'
      + '    self.postMessage({ output: __out.join("\\n"), canonical: null, error: (err && err.name ? err.name + ": " : "") + (err && err.message ? err.message : String(err)) });\n'
      + '  }\n'
      + '};\n';
    const url = URL.createObjectURL(new Blob([src], { type: 'text/javascript' }));
    const worker = new Worker(url);
    return new Promise((resolve) => {
      const done = (r) => { clearTimeout(timer); worker.terminate(); URL.revokeObjectURL(url); resolve(r); };
      const timer = setTimeout(() => done({ output: '', canonical: null, error: 'Your code ran for more than ' + Math.round(timeoutMs / 1000) + ' seconds (an endless loop?), so it was stopped.' }), timeoutMs);
      worker.onmessage = (e) => done(e.data);
      worker.onerror = (e) => { e.preventDefault(); done({ output: '', canonical: null, error: e.message || 'Your code could not start.' }); };
      worker.postMessage({ code, setup: setup || '' });
    });
  }

  window.PracticeLab = { runSql, sqlSchema, runPython, runJs, canonicalSqlRows, parseCsv };
})();
