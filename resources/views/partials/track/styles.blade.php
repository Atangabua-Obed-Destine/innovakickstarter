@once
<style>
    .tx-hero { position: relative; overflow: hidden; border-radius: 16px; padding: 22px; border: 1px solid color-mix(in srgb, var(--color-primary-500) 35%, transparent);
        background: radial-gradient(120% 140% at 0% 0%, color-mix(in srgb, var(--color-primary-500) 28%, transparent), transparent 60%),
                    linear-gradient(135deg, color-mix(in srgb, var(--color-primary-700) 35%, #0b0f17), #0f141d); }
    .tx-hero::after { content: ""; position: absolute; right: -40px; top: -40px; width: 180px; height: 180px; border-radius: 50%;
        background: color-mix(in srgb, var(--color-primary-400) 18%, transparent); filter: blur(6px); }
    .tx-emblem { width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 30px; flex-shrink: 0;
        background: color-mix(in srgb, var(--color-primary-500) 25%, transparent); border: 1px solid color-mix(in srgb, var(--color-primary-400) 45%, transparent); }
    .tx-chip { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;
        color: var(--color-primary-200); background: color-mix(in srgb, var(--color-primary-500) 22%, transparent); border: 1px solid color-mix(in srgb, var(--color-primary-400) 35%, transparent); }
    .tx-avatar { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; flex-shrink: 0;
        background: linear-gradient(135deg, var(--color-primary-500), var(--color-primary-700)); }
    .tx-brief { border-radius: 14px; padding: 18px; background: rgba(15, 20, 29, 0.6); border: 1px solid color-mix(in srgb, var(--color-primary-500) 30%, transparent); }
    .tx-journey { display: flex; gap: 0; overflow-x: auto; padding: 6px 2px 10px; scrollbar-width: thin; }
    .tx-step { flex: 1 0 110px; min-width: 110px; position: relative; text-align: center; }
    .tx-step::before { content: ""; position: absolute; top: 20px; left: -50%; width: 100%; height: 3px; background: rgba(148, 163, 184, 0.25); z-index: 0; }
    .tx-step:first-child::before { display: none; }
    .tx-step.is-done::before, .tx-step.is-current::before { background: var(--color-primary-500); }
    .tx-node { position: relative; z-index: 1; width: 42px; height: 42px; margin: 0 auto; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 15px; background: #1a212d; border: 2px solid rgba(148, 163, 184, 0.35); color: rgb(148 163 184); }
    .tx-step.is-done .tx-node { background: var(--color-primary-600); border-color: var(--color-primary-400); color: #fff; }
    .tx-step.is-current .tx-node { background: color-mix(in srgb, var(--color-primary-500) 25%, #1a212d); border-color: var(--color-primary-400); color: #fff;
        box-shadow: 0 0 0 6px color-mix(in srgb, var(--color-primary-500) 22%, transparent); animation: tx-pulse 2.2s ease-in-out infinite; }
    @keyframes tx-pulse { 50% { box-shadow: 0 0 0 10px color-mix(in srgb, var(--color-primary-500) 8%, transparent); } }
    @media (prefers-reduced-motion: reduce) { .tx-step.is-current .tx-node { animation: none; } }
    .tx-step-label { margin-top: 8px; font-size: 12px; line-height: 1.3; color: rgb(148 163 184); padding: 0 6px; }
    .tx-step.is-current .tx-step-label { color: #fff; font-weight: 600; }
    .tx-skill-bar { height: 7px; border-radius: 999px; background: rgba(148, 163, 184, 0.18); overflow: hidden; }
    .tx-skill-bar > span { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg, var(--color-primary-600), var(--color-primary-400)); }
    .tx-radar text { fill: rgb(203 213 225); font-size: 11px; font-family: inherit; }
    .lesson-md { color: rgb(203 213 225); font-size: 0.9rem; line-height: 1.65; }
    .lesson-md > * + * { margin-top: 0.6rem; }
    .lesson-md strong { color: #fff; font-weight: 600; }
    .lesson-md a { color: rgb(96 165 250); text-decoration: underline; }
    .lesson-md ul { list-style: disc; padding-left: 1.4rem; } .lesson-md ol { list-style: decimal; padding-left: 1.4rem; }
</style>
@endonce
