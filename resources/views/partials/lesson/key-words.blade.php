{{-- Key words in this lesson: plain-English meanings with a Nkwen Traders example, and tap-to-define in the lesson text.
     Expects $glossary (GlossaryService::forActivity). --}}
@php
    $kwShown = 10;
    $kwData = collect($glossary)->map(fn ($t) => ['k' => $t['key'], 't' => $t['term'], 'd' => $t['definition'], 'e' => $t['example'], 'n' => $t['new']])->values();
    $kwMarks = collect($glossary)->where('highlight', true)->map(fn ($t) => ['k' => $t['key'], 'w' => array_values(array_unique([$t['term'], ...$t['aliases']])), 'cs' => $t['case_sensitive']])->values();
@endphp
<div class="card p-6" id="key-words" x-data="{ open: null, all: false, words: @js($kwData) }">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-white font-semibold text-lg">📚 Key words in this lesson</h3>
            <p class="text-dark-400 text-sm mt-1">Tap a word to see what it means. <span class="kw-dot"></span> marks words that are new in this chapter. In the lesson below, <span class="gl-term" style="cursor: default;">underlined words</span> can be tapped too.</p>
        </div>
        <a href="{{ route('curriculum.key-words') }}" class="text-primary-400 hover:text-primary-300 text-sm">All key words →</a>
    </div>
    <div class="flex flex-wrap gap-2 mt-4">
        <template x-for="(w, i) in words" :key="w.k">
            <button type="button" class="kw-chip" x-show="all || i < {{ $kwShown }}" :class="open === w.k && 'is-open'" @click="open = open === w.k ? null : w.k">
                <span x-show="w.n" class="kw-dot"></span><span x-text="w.t"></span>
            </button>
        </template>
        @if(count($glossary) > $kwShown)
            <button type="button" class="kw-chip kw-more" x-show="!all" @click="all = true">+{{ count($glossary) - $kwShown }} more</button>
        @endif
    </div>
    <template x-for="w in words" :key="'d' + w.k">
        <div x-show="open === w.k" x-cloak class="kw-def">
            <p><b class="text-white" x-text="w.t"></b>: <span x-text="w.d"></span></p>
            <p class="text-dark-400 mt-1" x-show="w.e"><span class="text-primary-300">Example:</span> <span x-text="w.e"></span></p>
            <button type="button" class="kw-ask" @click="$dispatch('mentor-ask', { message: 'Explain the word &quot;' + w.t + '&quot; in very simple English, with a small example from the Nkwen Traders data. Then ask me one quick question to check I understood.' })">🧩 Still unclear? Ask the mentor</button>
        </div>
    </template>
</div>

<div id="gl-pop" class="gl-pop" role="dialog" aria-live="polite" hidden></div>

@once
<style>
    .kw-chip { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .7rem; border-radius: 999px; font-size: .82rem; color: #e4e4ea; background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); }
    .kw-chip:hover, .kw-chip.is-open { background: rgba(99,102,241,.18); border-color: rgba(129,140,248,.5); color: #fff; }
    .kw-more { color: #a5b4fc; border-style: dashed; }
    .kw-dot { display: inline-block; width: .45rem; height: .45rem; border-radius: 999px; background: #f59e0b; vertical-align: middle; }
    .kw-def { margin-top: .9rem; padding: .85rem 1rem; border-radius: .75rem; background: rgba(255,255,255,.04); border-left: 3px solid #818cf8; font-size: .88rem; color: #d4d4dc; }
    .kw-ask { margin-top: .5rem; font-size: .78rem; color: #a5b4fc; } .kw-ask:hover { color: #fff; }
    .gl-term { all: unset; cursor: help; border-bottom: 1px dotted #a5b4fc; color: inherit; }
    .gl-term:hover, .gl-term:focus-visible { color: #fff; border-bottom-style: solid; outline: none; }
    .lesson-md code .gl-term { border-bottom-color: #a5b4fc; }
    .gl-pop { position: absolute; z-index: 60; max-width: min(340px, calc(100vw - 24px)); padding: .8rem .95rem; border-radius: .75rem; background: #1c1c26; border: 1px solid rgba(129,140,248,.45); box-shadow: 0 12px 30px rgba(0,0,0,.45); font-size: .85rem; line-height: 1.45; color: #d4d4dc; }
    .gl-pop b { color: #fff; } .gl-pop .gl-ex { color: #a3a3b3; margin-top: .35rem; } .gl-pop .gl-ex span { color: #a5b4fc; }
    .gl-pop button { margin-top: .45rem; font-size: .78rem; color: #a5b4fc; } .gl-pop button:hover { color: #fff; }
</style>
<script>
(() => {
    const marks = @json($kwMarks);
    const info = Object.fromEntries(@json($kwData).map(w => [w.k, w]));
    if (!marks.length) return;
    const esc = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const build = (cs) => {
        const words = marks.filter(m => m.cs === cs).flatMap(m => m.w.map(w => [w, m.k])).sort((a, b) => b[0].length - a[0].length);
        if (!words.length) return null;
        const lookup = new Map(words.map(([w, k]) => [cs ? w : w.toLowerCase(), k]));
        return { re: new RegExp('(?<![\\p{L}\\p{N}_])(' + words.map(w => esc(w[0])).join('|') + ')(?![\\p{L}\\p{N}_])', 'gu' + (cs ? '' : 'i')), lookup, cs };
    };
    const finders = [build(true), build(false)].filter(Boolean);
    const used = new Set();
    const skip = el => el.closest('pre, a, button, textarea, input, select, .gl-term, h1, h2, h3, h4, [x-text], [x-html]');

    function wrapNode(node) {
        const text = node.nodeValue;
        const hits = [];
        for (const f of finders) {
            f.re.lastIndex = 0;
            let m;
            while ((m = f.re.exec(text))) {
                const k = f.lookup.get(f.cs ? m[1] : m[1].toLowerCase());
                if (k) hits.push({ i: m.index, len: m[1].length, k });
            }
        }
        hits.sort((a, b) => a.i - b.i || b.len - a.len);
        const frag = document.createDocumentFragment();
        let at = 0, changed = false;
        for (const h of hits) {
            if (h.i < at || used.has(h.k)) continue;
            used.add(h.k); changed = true;
            frag.append(text.slice(at, h.i));
            const b = document.createElement('button');
            b.type = 'button'; b.className = 'gl-term'; b.dataset.k = h.k; b.textContent = text.slice(h.i, h.i + h.len);
            b.setAttribute('aria-label', 'What does ' + b.textContent + ' mean?');
            frag.append(b);
            at = h.i + h.len;
        }
        if (!changed) return;
        frag.append(text.slice(at));
        node.replaceWith(frag);
    }

    function run() {
        document.querySelectorAll('.lesson-md').forEach(box => {
            const walker = document.createTreeWalker(box, NodeFilter.SHOW_TEXT, { acceptNode: n => n.nodeValue.trim() && !skip(n.parentElement) ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT });
            const nodes = []; while (walker.nextNode()) nodes.push(walker.currentNode);
            nodes.forEach(wrapNode);
        });
    }

    const pop = document.getElementById('gl-pop');
    let current = null;
    function show(btn) {
        const w = info[btn.dataset.k]; if (!w) return;
        current = btn;
        pop.innerHTML = '';
        const p1 = document.createElement('p'); const b = document.createElement('b'); b.textContent = w.t; p1.append(b, ': ' + w.d); pop.append(p1);
        if (w.e) { const p2 = document.createElement('p'); p2.className = 'gl-ex'; const s = document.createElement('span'); s.textContent = 'Example: '; p2.append(s, w.e); pop.append(p2); }
        const ask = document.createElement('button'); ask.type = 'button'; ask.textContent = '🧩 Still unclear? Ask the mentor';
        ask.onclick = () => { window.dispatchEvent(new CustomEvent('mentor-ask', { detail: { message: 'Explain the word "' + w.t + '" in very simple English, with a small example from the Nkwen Traders data. Then ask me one quick question to check I understood.' } })); hide(); };
        pop.append(ask);
        pop.hidden = false;
        const r = btn.getBoundingClientRect();
        const width = pop.offsetWidth;
        pop.style.left = Math.max(12, Math.min(window.scrollX + r.left, window.scrollX + document.documentElement.clientWidth - width - 12)) + 'px';
        pop.style.top = (window.scrollY + r.bottom + 8) + 'px';
    }
    function hide() { pop.hidden = true; current = null; }
    document.addEventListener('click', e => {
        const btn = e.target.closest('.gl-term[data-k]');
        if (btn) { e.preventDefault(); current === btn ? hide() : show(btn); return; }
        if (!e.target.closest('#gl-pop')) hide();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') hide(); });
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', run) : run();
})();
</script>
@endonce
