{{--
    "Try it here": a small notebook-like sandbox in the lesson. Python runs in a Web Worker
    (the page never freezes), SQL runs on the track's practice tables, and the lesson's
    starter notebook cells can be loaded with one click.
    @param array $sandbox  ['notebook' => ?path, 'sqlTables' => [...], 'pythonData' => ?url, 'startWith' => 'python'|'sql']
    @param string|null $colabUrl
--}}
@php
    $langs = $sandbox['languages'] ?? ['python'];
    $pyStart = $sandbox['pythonData']
        ? "import pandas as pd\n\nsales = pd.read_csv(\"" . url($sandbox['pythonData']) . "\")\nsales.head()"
        : "print(\"Hello from Python!\")";
    $sqlStart = $sandbox['sqlTables'] ? 'SELECT * FROM ' . array_key_first($sandbox['sqlTables']) . ' LIMIT 5;' : '';
    $cfg = [
        'workerUrl' => asset('course-files/practice/python-worker.js'),
        'notebook' => $sandbox['notebook'] ? url($sandbox['notebook']) : null,
        'tables' => collect($sandbox['sqlTables'])->map(fn ($u) => url($u))->all(),
        'langs' => $langs,
        'tab' => $sandbox['sqlTables'] && $sandbox['startWith'] === 'sql' ? 'sql' : (in_array('web', $langs, true) && $sandbox['startWith'] !== 'python' ? 'web' : 'python'),
        'web' => ['html' => "<h1>Nkwen Traders</h1>\n<p id=\"msg\">Loading products...</p>", 'css' => "body { font-family: system-ui, sans-serif; padding: 12px; }\nh1 { color: #0f766e; }",
            'js' => "const res = await fetch('" . url('/api/course/nkwen/products?category=Grains') . "');\nconst data = await res.json();\nconsole.log(data.count, 'products');\ndocument.getElementById('msg').textContent = data.products.map(p => p.name).join(', ');"],
        'py' => $pyStart, 'sql' => $sqlStart,
    ];
@endphp
@once
<style>
    .sb-tabs button { padding: 6px 14px; border-radius: 99px; font-size: 13px; color: rgb(148 163 184); border: 1px solid transparent; }
    .sb-tabs button.on { color: #fff; background: color-mix(in srgb, var(--color-primary-600) 30%, transparent); border-color: color-mix(in srgb, var(--color-primary-500) 45%, transparent); }
    .sb-editor { width: 100%; min-height: 170px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; line-height: 1.55; tab-size: 4;
        background: #0b0f16; color: #e2e8f0; border: 1px solid rgba(148,163,184,.25); border-radius: 10px; padding: 12px; resize: vertical; white-space: pre; }
    .sb-editor:focus { outline: none; border-color: var(--color-primary-500); }
    .sb-out { background: #0b0f16; border: 1px solid rgba(148,163,184,.15); border-radius: 10px; padding: 10px 12px; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 12.5px;
        color: #cbd5e1; white-space: pre; overflow-x: auto; max-height: 360px; overflow-y: auto; }
    .sb-preview { width: 100%; height: 320px; background: #fff; border-radius: 10px; border: 1px solid rgba(148,163,184,.25); }
    .sb-cells { max-height: 260px; overflow-y: auto; display: flex; flex-direction: column; gap: 6px; }
    .sb-cell { text-align: left; padding: 8px 10px; border-radius: 8px; border: 1px solid rgba(148,163,184,.18); background: rgba(15,20,29,.6); font-size: 13px; color: rgb(203 213 225); }
    .sb-cell:hover { border-color: var(--color-primary-400); color: #fff; }
    .sb-table { border-collapse: collapse; font-size: 12.5px; }
    .sb-table th, .sb-table td { border: 1px solid rgba(148,163,184,.18); padding: 4px 8px; text-align: left; white-space: nowrap; }
    .sb-table th { color: #fff; background: rgba(148,163,184,.08); }
    .ls-stuck-btn { font-size: 12px; color: rgb(148 163 184); border: 1px solid rgba(148,163,184,.25); border-radius: 99px; padding: 2px 10px; }
    .ls-stuck-btn:hover { color: #fff; border-color: var(--color-primary-400); }
</style>
@endonce
<div id="try-it" class="card p-6" x-data="lessonSandbox(@js($cfg))" @sandbox-load.window="load($event.detail)">
    <span hidden data-langs="{{ implode(',', $langs) }}"></span>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-white font-semibold text-lg flex items-center gap-2"><span>🧪</span> Try it here</h3>
            <p class="text-dark-400 text-sm">Run code right in the lesson, on the course data. Nothing to install; your work isn't graded here.</p>
        </div>
        <div class="sb-tabs flex gap-1">
            @if(in_array('web', $langs, true))<button type="button" :class="tab === 'web' && 'on'" @click="tab = 'web'">🌐 Web (HTML · CSS · JS)</button>@endif
            @if(in_array('python', $langs, true))<button type="button" :class="tab === 'python' && 'on'" @click="tab = 'python'">🐍 Python</button>@endif
            @if($sandbox['sqlTables'])<button type="button" :class="tab === 'sql' && 'on'" @click="tab = 'sql'">🗄️ SQL</button>@endif
        </div>
    </div>

    {{-- WEB: HTML/CSS/JS with live preview (sandboxed iframe) and console --}}
    @if(in_array('web', $langs, true))
    <div x-show="tab === 'web'" x-cloak class="mt-4 space-y-3">
        <div class="sb-tabs flex gap-1">
            <button type="button" :class="webPane === 'html' && 'on'" @click="webPane = 'html'">HTML</button>
            <button type="button" :class="webPane === 'css' && 'on'" @click="webPane = 'css'">CSS</button>
            <button type="button" :class="webPane === 'js' && 'on'" @click="webPane = 'js'">JavaScript</button>
        </div>
        <textarea class="sb-editor" x-show="webPane === 'html'" x-model="web.html" spellcheck="false" aria-label="HTML" @keydown.tab.prevent="indent($event)" @keydown.ctrl.enter.prevent="runWeb()" @keydown.meta.enter.prevent="runWeb()"></textarea>
        <textarea class="sb-editor" x-show="webPane === 'css'" x-cloak x-model="web.css" spellcheck="false" aria-label="CSS" @keydown.tab.prevent="indent($event)" @keydown.ctrl.enter.prevent="runWeb()" @keydown.meta.enter.prevent="runWeb()"></textarea>
        <textarea class="sb-editor" x-show="webPane === 'js'" x-cloak x-model="web.js" spellcheck="false" aria-label="JavaScript" @keydown.tab.prevent="indent($event)" @keydown.ctrl.enter.prevent="runWeb()" @keydown.meta.enter.prevent="runWeb()"></textarea>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" class="btn-primary text-sm px-4 py-2" @click="runWeb()">▶ Run <span class="opacity-60 text-xs">(Ctrl+Enter)</span></button>
            <span class="text-dark-400 text-xs">Your page runs in a safe box. <code>await</code> works at the top level of your JavaScript. Practice API: <code>{{ url('/api/course/nkwen') }}</code></span>
        </div>
        <iframe x-ref="preview" class="sb-preview" title="Preview of your page" sandbox="allow-scripts allow-modals" x-show="ranWeb"></iframe>
        <div class="sb-out" x-show="logs.length" x-cloak><template x-for="(l, i) in logs" :key="i"><div :style="l.kind === 'error' ? 'color:#fca5a5' : (l.kind === 'warn' ? 'color:#fcd34d' : '')" x-text="(l.kind === 'error' ? '✖ ' : '› ') + l.text"></div></template></div>
        <div x-show="logs.some(l => l.kind === 'error')" x-cloak><button type="button" class="ls-stuck-btn" @click="explainWebError()">🆘 Explain this error</button></div>
    </div>
    @endif

    {{-- PYTHON --}}
    <div x-show="tab === 'python'" @if(!in_array('python', $langs, true)) x-cloak @endif class="mt-4 space-y-3">
        @if($sandbox['notebook'])
            <div class="rounded-xl border border-dark-700 p-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-dark-200 text-sm">📓 <span class="text-white font-medium">Starter notebook</span>: load any of its code cells, then run.</p>
                    <div class="flex gap-2">
                        <button type="button" class="btn-secondary text-xs px-3 py-1.5" @click="toggleCells()" x-text="showCells ? 'Hide cells' : 'Show cells'"></button>
                        @if($colabUrl)<a href="{{ $colabUrl }}" target="_blank" rel="noopener" class="btn-secondary text-xs px-3 py-1.5">Open in Colab ↗</a>@endif
                    </div>
                </div>
                <div class="sb-cells mt-3" x-show="showCells" x-cloak>
                    <p class="text-dark-500 text-xs" x-show="cellsError" x-text="cellsError"></p>
                    <template x-for="(cell, i) in cells" :key="i">
                        <button type="button" class="sb-cell" @click="loadCell(i)">
                            <span class="text-primary-300 font-mono text-xs" x-text="'Cell ' + (i + 1)"></span>
                            <span x-text="' · ' + cell.label"></span>
                        </button>
                    </template>
                </div>
            </div>
        @endif
        <textarea class="sb-editor" x-model="py" spellcheck="false" aria-label="Python code" @keydown.tab.prevent="indent($event)" @keydown.ctrl.enter.prevent="runPy()" @keydown.meta.enter.prevent="runPy()"></textarea>
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" class="btn-primary text-sm px-4 py-2" @click="runPy()" :disabled="running">▶ Run <span class="opacity-60 text-xs">(Ctrl+Enter)</span></button>
            <button type="button" class="btn-secondary text-xs px-3 py-1.5" x-show="running" @click="stop()">■ Stop</button>
            <button type="button" class="btn-secondary text-xs px-3 py-1.5" x-show="!running" @click="reset()" title="Forget all variables">↺ Reset variables</button>
            <span class="text-dark-400 text-xs" x-text="status"></span>
        </div>
        <p class="text-dark-500 text-xs">Variables stay between runs, like a notebook. The very first run downloads Python and its libraries (up to 30 MB, once; on a slow connection this can take a minute or two). Your browser keeps them.</p>
        <template x-if="out">
            <div class="space-y-2">
                <div class="sb-out" x-show="out.stdout" x-text="out.stdout"></div>
                <div class="sb-out" x-show="out.value" x-text="out.value" style="color:#a5f3fc"></div>
                <template x-for="(img, i) in out.images" :key="i"><img :src="'data:image/png;base64,' + img" alt="Chart from your code" class="rounded-lg bg-white max-w-full"></template>
                <div class="sb-out" x-show="out.error" x-text="out.error" style="color:#fca5a5"></div>
                <div x-show="out.error" class="flex flex-wrap gap-2">
                    <button type="button" class="ls-stuck-btn" @click="explainError()">🆘 Explain this error</button>
                </div>
                <p class="text-dark-500 text-xs" x-show="!out.stdout && !out.value && !out.error && !out.images.length">Ran with no output. Tip: put a variable name on the last line to see it.</p>
            </div>
        </template>
    </div>

    {{-- SQL --}}
    @if($sandbox['sqlTables'])
    <div x-show="tab === 'sql'" x-cloak class="mt-4 space-y-3">
        <p class="text-dark-400 text-sm">Tables: @foreach(array_keys($sandbox['sqlTables']) as $t)<code class="text-primary-300">{{ $t }}</code>{{ !$loop->last ? ', ' : '' }}@endforeach. Try <code>PRAGMA table_info(sales);</code> to see the columns.</p>
        <textarea class="sb-editor" x-model="sql" spellcheck="false" aria-label="SQL query" @keydown.tab.prevent="indent($event)" @keydown.ctrl.enter.prevent="runSql()" @keydown.meta.enter.prevent="runSql()"></textarea>
        <div class="flex items-center gap-2">
            <button type="button" class="btn-primary text-sm px-4 py-2" @click="runSql()" :disabled="running">▶ Run query</button>
            <span class="text-dark-400 text-xs" x-text="status"></span>
        </div>
        <template x-if="sqlOut">
            <div>
                <p class="text-red-300 text-sm font-mono" x-show="sqlOut.error" x-text="sqlOut.error"></p>
                <div class="overflow-x-auto" x-show="!sqlOut.error">
                    <table class="sb-table">
                        <thead><tr><template x-for="c in sqlOut.columns" :key="c"><th x-text="c"></th></template></tr></thead>
                        <tbody><template x-for="(r, i) in sqlOut.rows" :key="i"><tr><template x-for="(v, j) in r" :key="j"><td x-text="v === null ? 'NULL' : v"></td></template></tr></template></tbody>
                    </table>
                </div>
                <p class="text-dark-500 text-xs mt-1" x-show="!sqlOut.error" x-text="sqlOut.total + ' row' + (sqlOut.total === 1 ? '' : 's') + (sqlOut.total > sqlOut.rows.length ? ' (showing the first ' + sqlOut.rows.length + ')' : '')"></p>
            </div>
        </template>
    </div>
    @endif
</div>

@once
@push('scripts')
<script src="{{ asset('course-files/practice/practice-lab.js') }}"></script>
<script>
function lessonSandbox(cfg) {
    let worker = null, seq = 0;
    let frameWin = null; // the preview frame's window: kept OUT of Alpine's reactive state (it is cross-origin)
    const pending = {};
    return {
        tab: cfg.tab, py: cfg.py, sql: cfg.sql, running: false, status: '', out: null, sqlOut: null,
        web: cfg.web, webPane: 'js', ranWeb: false, logs: [],
        init() {
            window.addEventListener('message', (e) => {
                if (!frameWin || e.source !== frameWin || !e.data || !e.data.__sb) return;
                if (this.logs.length < 200) this.logs.push({ kind: e.data.kind, text: String(e.data.text).slice(0, 2000) });
            });
        },
        runWeb() {
            const esc = (s) => String(s || '').replace(/<\/script/gi, '<\\/script');
            const capture = "<script>(function(){var send=function(k,a){try{parent.postMessage({__sb:1,kind:k,text:Array.prototype.map.call(a,function(x){if(x instanceof Error)return x.name+': '+x.message;if(typeof x==='object'){try{return JSON.stringify(x,null,1)}catch(e){return String(x)}}return String(x)}).join(' ')},'*')}catch(e){}};"
                + "['log','info','warn','error'].forEach(function(k){var o=console[k];console[k]=function(){send(k==='info'?'log':k,arguments);o.apply(console,arguments)}});"
                + "window.addEventListener('error',function(e){send('error',[e.message+(e.lineno?' (line '+e.lineno+')':'')])});"
                + "window.addEventListener('unhandledrejection',function(e){send('error',[e.reason&&e.reason.message?e.reason.name+': '+e.reason.message:String(e.reason)])});})();<\/script>";
            const doc = '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' + capture
                + '<style>' + String(this.web.css || '').replace(/<\/style/gi, '') + '</style></head><body>' + this.web.html + '<script type="module">' + esc(this.web.js) + '<\/script></body></html>';
            this.logs = []; this.ranWeb = true;
            this.$nextTick(() => { this.$refs.preview.srcdoc = doc; frameWin = this.$refs.preview.contentWindow; });
        },
        explainWebError() {
            const err = this.logs.filter(l => l.kind === 'error').map(l => l.text).join('\n').slice(0, 500);
            this.$dispatch('mentor-ask', { message: 'I ran this JavaScript in the lesson sandbox:\n' + String(this.web.js).slice(0, 700) + '\n\nand got this error:\n' + err + '\n\nWhat does the error mean, and what should I check? Please give me a hint, not the fixed code.' });
        },
        cells: [], showCells: false, cellsError: '',
        startWorker() {
            if (worker) return worker;
            worker = new Worker(cfg.workerUrl);
            worker.onmessage = (e) => {
                const m = e.data, p = pending[m.id];
                if (!p) return;
                if (m.type === 'status') { this.status = m.text; return; }
                delete pending[m.id]; p(m);
            };
            worker.onerror = () => { this.status = 'Python could not start. Check your connection and try again.'; this.running = false; worker = null; };
            return worker;
        },
        call(msg) {
            return new Promise((resolve) => { const id = ++seq; pending[id] = resolve; this.startWorker().postMessage(Object.assign({ id }, msg)); });
        },
        async runPy() {
            if (this.running || !this.py.trim()) return;
            this.running = true; this.status = 'Starting Python...';
            const r = await this.call({ cmd: 'run', code: this.py });
            this.out = { stdout: r.stdout || '', value: r.value || '', images: r.images || [], error: r.error || '' };
            this.running = false; this.status = r.error ? '' : 'Done.';
        },
        stop() {
            if (worker) { worker.terminate(); worker = null; }
            Object.keys(pending).forEach(k => delete pending[k]);
            this.running = false; this.status = 'Stopped. Variables were cleared; the next run starts fresh.';
        },
        async reset() { if (!worker) { this.status = 'Variables cleared.'; return; } await this.call({ cmd: 'reset' }); this.out = null; this.status = 'Variables cleared.'; },
        async runSql() {
            if (this.running || !this.sql.trim()) return;
            this.running = true; this.status = '';
            try {
                const r = await window.PracticeLab.runSql(this.sql, { tables: cfg.tables, ordered: true, onStatus: s => this.status = s });
                this.sqlOut = { columns: r.columns, rows: r.rows.slice(0, 200), total: r.rows.length, error: '' };
                this.status = '';
            } catch (e) { this.sqlOut = { columns: [], rows: [], total: 0, error: String(e.message || e) }; }
            this.running = false;
        },
        async toggleCells() {
            this.showCells = !this.showCells;
            if (!this.showCells || this.cells.length || !cfg.notebook) return;
            try {
                const nb = await (await fetch(cfg.notebook)).json();
                let label = 'Setup';
                for (const c of nb.cells || []) {
                    const src = Array.isArray(c.source) ? c.source.join('') : String(c.source || '');
                    if (c.cell_type === 'markdown') {
                        const h = src.split('\n').find(l => l.trim().startsWith('#')) || src.split('\n')[0];
                        label = h.replace(/^#+\s*/, '').replace(/\*\*/g, '').slice(0, 70) || label;
                    } else if (c.cell_type === 'code' && src.trim()) {
                        this.cells.push({ label, code: src });
                    }
                }
                if (!this.cells.length) this.cellsError = 'This notebook has no code cells.';
            } catch (e) { this.cellsError = 'Could not load the notebook. Check your connection.'; }
        },
        loadCell(i) { this.py = this.cells[i].code; this.tab = 'python'; this.status = 'Cell ' + (i + 1) + ' loaded. Press Run.'; },
        load(d) {
            if (!d || !d.code) return;
            if (d.lang === 'sql' && cfg.tables && Object.keys(cfg.tables).length) { this.tab = 'sql'; this.sql = d.code; }
            else if (['js', 'html', 'css'].includes(d.lang) && cfg.langs.includes('web')) { this.tab = 'web'; this.webPane = d.lang; this.web[d.lang] = d.code; }
            else { this.tab = 'python'; this.py = d.code; }
            this.$el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        explainError() {
            if (!this.out || !this.out.error) return;
            this.$dispatch('mentor-ask', { message: 'I ran this code in the lesson sandbox:\n' + this.py.slice(0, 700) + '\n\nand got this error:\n' + this.out.error.slice(0, 500) + '\n\nWhat does the error mean, and what should I check? Please give me a hint, not the fixed code.' });
        },
        indent(e) {
            const t = e.target, s = t.selectionStart, end = t.selectionEnd;
            t.value = t.value.slice(0, s) + '    ' + t.value.slice(end);
            t.selectionStart = t.selectionEnd = s + 4;
            t.dispatchEvent(new Event('input'));
        },
    };
}
// Code examples inside a lesson get a "Try it" button that loads them into the sandbox.
document.addEventListener('DOMContentLoaded', () => {
    if (!document.getElementById('try-it')) return;
    document.querySelectorAll('.lesson-md pre > code').forEach((code) => {
        const cls = code.className;
        const langs = (document.querySelector('#try-it [data-langs]')?.dataset.langs || 'python').split(',');
        const lang = /language-sql/.test(cls) ? 'sql'
            : /language-(js|javascript)\b/.test(cls) ? 'js' : /language-html\b/.test(cls) ? 'html' : /language-css\b/.test(cls) ? 'css'
            : (/language-(python|py)\b/.test(cls) || !cls) ? 'python' : null;
        if (!lang || (['js', 'html', 'css'].includes(lang) && !langs.includes('web')) || (lang === 'python' && !langs.includes('python'))) return;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = '▶ Try it';
        btn.className = 'ls-stuck-btn';
        btn.style.marginTop = '6px';
        btn.addEventListener('click', () => window.dispatchEvent(new CustomEvent('sandbox-load', { detail: { lang, code: code.textContent } })));
        code.parentElement.insertAdjacentElement('afterend', btn);
    });
});
</script>
@endpush
@endonce
