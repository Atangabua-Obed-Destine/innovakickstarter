/*
 * Python in a Web Worker (Pyodide), so the page never freezes while Python
 * downloads or runs. Used by the lesson sandbox ("Try it here").
 *
 * Messages in:  {id, cmd: 'run', code} | {id, cmd: 'reset'}
 * Messages out: {id, type: 'status', text} | {id, type: 'result', stdout, value, images, error}
 *
 * Course data: any "<anything>/course-files/..." address in the code is rewritten
 * to a local copy, downloaded from this site first, so notebook cells that read
 * https://innovakickstarter.com/course-files/... work unchanged.
 */
/* global importScripts, loadPyodide */
'use strict';

const PYODIDE_BASE = 'https://cdn.jsdelivr.net/pyodide/v0.26.4/full/';
let py = null;
let ns = null;

const PRELUDE = `
import os, sys, io, base64, shutil, builtins, urllib.request
os.environ["MPLBACKEND"] = "AGG"
_orig_urlretrieve = urllib.request.urlretrieve
def _urlretrieve(url, filename=None, *args, **kwargs):
    if isinstance(url, str) and url.startswith("/course-files/"):
        filename = filename or os.path.basename(url)
        shutil.copyfile(url, filename)
        return filename, None
    return _orig_urlretrieve(url, filename, *args, **kwargs)
urllib.request.urlretrieve = _urlretrieve
def _display(*objs, **kwargs):
    for o in objs:
        print(o)
builtins.display = _display
def _figures():
    if "matplotlib.pyplot" not in sys.modules:
        return []
    import matplotlib.pyplot as plt
    out = []
    for n in plt.get_fignums():
        buf = io.BytesIO()
        plt.figure(n).savefig(buf, format="png", dpi=90, bbox_inches="tight")
        out.append(base64.b64encode(buf.getvalue()).decode())
    plt.close("all")
    return out
`;

function post(id, type, extra) { self.postMessage(Object.assign({ id, type }, extra)); }

async function ensure(id) {
  if (py) return;
  post(id, 'status', { text: 'Downloading Python (about 10 MB, once; your browser keeps it)...' });
  importScripts(PYODIDE_BASE + 'pyodide.js');
  py = await loadPyodide({ indexURL: PYODIDE_BASE });
  py.runPython(PRELUDE);
  ns = py.globals.get('dict')();
}

function mkdirs(path) {
  const parts = path.split('/').filter(Boolean);
  let cur = '';
  for (const p of parts.slice(0, -1)) {
    cur += '/' + p;
    try { py.FS.mkdir(cur); } catch (e) { /* exists */ }
  }
}

/** Download course files the code refers to, and point the code at the local copies. */
async function localiseCourseFiles(id, code) {
  const re = /(?:https?:\/\/[^\s"')]+)?(\/course-files\/[^\s"')]+)/g;
  const paths = new Set();
  const rewritten = code.replace(re, (m, path) => { paths.add(path); return path; });
  for (const path of paths) {
    if (py.FS.analyzePath(path).exists) continue;
    post(id, 'status', { text: 'Loading ' + path.split('/').pop() + '...' });
    const res = await fetch(self.location.origin + path);
    if (!res.ok) throw new Error('Could not load ' + path);
    mkdirs(path);
    py.FS.writeFile(path, new Uint8Array(await res.arrayBuffer()));
  }
  return rewritten;
}

/** Notebook magics (%matplotlib, !pip) aren't Python: comment them out. */
function stripMagics(code) {
  return code.split('\n').map(l => /^\s*[%!]/.test(l) ? '# (notebook command skipped) ' + l.trim() : l).join('\n');
}

self.onmessage = async (event) => {
  const { id, cmd } = event.data;
  let stdout = '';
  try {
    await ensure(id);
    if (cmd === 'reset') {
      ns.destroy();
      ns = py.globals.get('dict')();
      try { py.runPython('_figures()'); } catch (e) { /* nothing to close */ }
      post(id, 'result', { stdout: '', value: null, images: [], error: null, reset: true });
      return;
    }
    let code = stripMagics(String(event.data.code || ''));
    code = await localiseCourseFiles(id, code);
    post(id, 'status', { text: 'Loading libraries...' });
    await py.loadPackagesFromImports(code, { messageCallback: () => {}, errorCallback: () => {} });
    if (/\b(pd|np|plt|sns)\./.test(code)) {
      // Names used without an import in this cell (imported in an earlier cell) still need their packages.
      const needed = [];
      if (/\bpd\./.test(code)) needed.push('pandas');
      if (/\bnp\./.test(code)) needed.push('numpy');
      if (/\bplt\./.test(code)) needed.push('matplotlib');
      if (/\bsns\./.test(code)) needed.push('seaborn');
      await py.loadPackage(needed, { messageCallback: () => {}, errorCallback: () => {} });
    }
    post(id, 'status', { text: 'Running...' });

    py.setStdout({ batched: s => { stdout += s + '\n'; } });
    py.setStderr({ batched: s => { stdout += s + '\n'; } });

    let value = null;
    const result = await py.runPythonAsync(code, { globals: ns });
    if (result !== undefined && result !== null) {
      if (result && typeof result === 'object' && typeof result.toJs === 'function') {
        value = py.globals.get('repr')(result);
        result.destroy();
      } else {
        value = String(result);
      }
    }
    const figs = py.globals.get('_figures')();
    const images = figs.toJs();
    figs.destroy();
    // A plotting call returns an object like <AxesSubplot...>: noise when the chart itself is shown.
    if (images.length && value && /^\[?<(Axes|matplotlib|Figure|seaborn)/.test(String(value))) value = null;
    post(id, 'result', { stdout: stdout.slice(0, 20000), value: value ? String(value).slice(0, 20000) : null, images, error: null });
  } catch (err) {
    const msg = String((err && err.message) || err);
    const lines = msg.trim().split('\n');
    // Keep the useful end of a Python traceback.
    let images = [];
    try { const figs = py && py.globals.get('_figures')(); if (figs) { images = figs.toJs(); figs.destroy(); } } catch (e) { /* none */ }
    post(id, 'result', { stdout: stdout.slice(0, 20000), value: null, images, error: lines.slice(-4).join('\n') });
  }
};
