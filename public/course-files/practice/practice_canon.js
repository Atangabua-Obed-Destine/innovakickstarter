/*
 * Practice Lab answer canonicalisation for JavaScript challenges. Mirrors practice_canon.py
 * exactly, so the same answer gives the same canonical string (and hash) in both languages:
 * numbers rounded to 2 decimals with floor(x * 100 + 0.5) / 100, whole numbers as integers,
 * NaN/Infinity -> null, strings trimmed, objects/Maps key-sorted, Sets sorted, arrays in order.
 * Works in the browser (self.PracticeCanon), in a Web Worker (importScripts) and in Node
 * (import it, then use globalThis.PracticeCanon), which is how expected answers are computed offline.
 */
(function (root) {
  function round2(x) {
    const r = Math.floor(Number(x) * 100 + 0.5) / 100;
    return Object.is(r, -0) ? 0 : r;
  }
  function canon(v) {
    if (v === null || v === undefined) return null;
    if (typeof v === 'boolean') return v;
    if (typeof v === 'number') return Number.isFinite(v) ? round2(v) : null;
    if (typeof v === 'bigint') return Number(v);
    if (typeof v === 'string') return v.trim();
    if (v instanceof Date) return v.toISOString();
    if (v instanceof Map) {
      const o = {};
      for (const [k, x] of v) o[String(k).trim()] = canon(x);
      return o;
    }
    if (v instanceof Set) {
      return [...v].map(canon).sort((a, b) => (stringify(a) < stringify(b) ? -1 : stringify(a) > stringify(b) ? 1 : 0));
    }
    if (Array.isArray(v) || ArrayBuffer.isView(v)) return Array.from(v, canon);
    if (typeof v === 'object') {
      const o = {};
      for (const k of Object.keys(v)) o[String(k).trim()] = canon(v[k]);
      return o;
    }
    return String(v).trim();
  }
  // JSON with sorted keys and no spaces: the same text Python's json.dumps(..., separators=(",", ":"), sort_keys=True) gives.
  function stringify(v) {
    if (v === null || typeof v !== 'object') return JSON.stringify(v);
    if (Array.isArray(v)) return '[' + v.map(stringify).join(',') + ']';
    return '{' + Object.keys(v).sort().map((k) => JSON.stringify(k) + ':' + stringify(v[k])).join(',') + '}';
  }
  const api = { canonical: (v) => stringify(canon(v)), canon, stringify };
  root.PracticeCanon = api;
  if (typeof module !== 'undefined' && module && module.exports) module.exports = api;
})(typeof globalThis !== 'undefined' ? globalThis : self);
