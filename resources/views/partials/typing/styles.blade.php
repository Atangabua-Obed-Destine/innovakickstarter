@once
<script src="{{ asset('js/typing-engine.js') }}"></script>
<style>
    .te-box { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 1.35rem; line-height: 2.1rem; padding: 18px 20px; border-radius: 14px;
        background: #0b0f16; border: 2px solid rgba(148,163,184,.2); color: #e2e8f0; outline: none; cursor: text; white-space: pre-wrap; word-break: break-word; min-height: 120px; }
    .te-box:focus { border-color: var(--color-primary-500); box-shadow: 0 0 0 4px color-mix(in srgb, var(--color-primary-500) 18%, transparent); }
    .te-box.te-blur { filter: blur(1.5px); opacity: .7; }
    .te-done { color: color-mix(in srgb, var(--color-primary-300) 80%, #fff); }
    .te-todo { color: rgb(100 116 139); }
    .te-cur { background: color-mix(in srgb, var(--color-primary-500) 35%, transparent); color: #fff; border-bottom: 3px solid var(--color-primary-300); border-radius: 3px; animation: te-blink 1s steps(2) infinite; }
    .te-cur.te-wrong { background: rgba(239,68,68,.45); border-bottom-color: #f87171; animation: te-shake .18s; }
    .te-nl { color: rgb(71 85 105); font-size: .8em; }
    @keyframes te-blink { 50% { border-bottom-color: transparent; } }
    @keyframes te-shake { 25% { transform: translateX(-2px); } 75% { transform: translateX(2px); } }
    .te-stat { text-align: center; } .te-stat b { display: block; font-size: 1.6rem; color: #fff; line-height: 1.1; } .te-stat span { font-size: 12px; color: rgb(148 163 184); }
    .kb { display: flex; flex-direction: column; align-items: center; gap: 4px; user-select: none; }
    .kb-row { display: flex; gap: 4px; }
    .kb-key { min-width: 30px; height: 30px; padding: 0 6px; display: flex; align-items: center; justify-content: center; border-radius: 6px; font-size: 12px; font-family: ui-monospace, monospace;
        color: #e2e8f0; border-bottom: 3px solid rgba(0,0,0,.35); opacity: .75; }
    .kb-wide { min-width: 58px; background: #1e293b; } .kb-space { min-width: 260px; }
    .kb-lp, .kb-rp { background: #4c1d95; } .kb-lr, .kb-rr { background: #1e3a8a; } .kb-lm, .kb-rm { background: #065f46; } .kb-li, .kb-ri { background: #7c2d12; } .kb-th { background: #334155; }
    .kb-next { opacity: 1; outline: 3px solid #fde047; transform: translateY(-2px); box-shadow: 0 6px 14px rgba(253,224,71,.35); }
    .kb-legend span { display: inline-flex; align-items: center; gap: 4px; margin-right: 10px; font-size: 12px; color: rgb(148 163 184); }
    .kb-legend i { width: 10px; height: 10px; border-radius: 3px; display: inline-block; }
    .te-touch { display: none; }
    @media (hover: none) and (pointer: coarse) { .te-touch { display: block; } }
</style>
<div class="te-touch p-3 mb-3 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-300 text-sm">⌨️ Typing practice needs a physical keyboard. Please use a laptop or desktop computer for this page.</div>
@endonce
