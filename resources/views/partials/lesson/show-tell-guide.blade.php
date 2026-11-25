{{-- Show & Tell: how to record and publish the explainer video. Expects $activity. --}}
<div class="st-guide space-y-4 text-sm text-dark-200">
    <div class="grid gap-3" style="grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));">
        <div class="st-fact"><b>🖥️ Screen + your voice</b><span>Show your notebook, sheet or dashboard and talk through it. Your face is optional.</span></div>
        <div class="st-fact"><b>⏱️ 2 to 4 minutes</b><span>Short is better. A manager gives you about two minutes.</span></div>
        <div class="st-fact"><b>🌍 Public on your channel</b><span>Upload to <em>your own</em> YouTube channel and set Visibility to <b>Public</b>. Nothing is uploaded to IKS.</span></div>
    </div>

    <div>
        <p class="text-white font-semibold mb-2">🗣️ What to say (use these four parts)</p>
        <ol class="st-script">
            <li><b>The problem (20 s).</b> "{{ $activity->track?->name ?? 'This' }} project: I was asked to … for … so they could decide …"</li>
            <li><b>What I did (60–90 s).</b> Show your work on screen. Name each step in plain words: "First I loaded the data, then I removed duplicates because …"</li>
            <li><b>What I found (40 s).</b> Two or three findings with real numbers and units: "March sales were 18% higher than February, mostly from one branch."</li>
            <li><b>What it means and what I'd do next (30 s).</b> One recommendation, and one thing you would improve with more time.</li>
        </ol>
        <p class="text-dark-400 text-xs mt-2">When you use a technical word (mean, outlier, merge, SQL JOIN …), say what it means in one short sentence, as if to a shop owner. That is the skill employers test in interviews.</p>
    </div>

    <div class="grid gap-3" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        <div>
            <p class="text-white font-semibold mb-1">🎙️ Free ways to record</p>
            <ul class="st-list">
                <li><b>Windows:</b> Clipchamp (built in) or Xbox Game Bar: press <kbd>Win</kbd> + <kbd>Alt</kbd> + <kbd>R</kbd>.</li>
                <li><b>Mac:</b> press <kbd>Shift</kbd> + <kbd>Cmd</kbd> + <kbd>5</kbd>, choose "Record", turn on the microphone in Options.</li>
                <li><b>Any computer:</b> OBS Studio (free) or Loom (free plan, then download the video).</li>
                <li><b>Android phone:</b> swipe down, tap "Screen recorder", choose "Media and microphone". <b>iPhone:</b> Control Centre → Screen Recording (hold to turn the mic on).</li>
            </ul>
        </div>
        <div>
            <p class="text-white font-semibold mb-1">📤 Publish on YouTube</p>
            <ol class="st-list" style="list-style: decimal; padding-left: 1.1rem;">
                <li>Open <a href="https://studio.youtube.com" target="_blank" rel="noopener" class="text-primary-400 hover:underline">studio.youtube.com</a> (or the YouTube app) and sign in. The first time, create your channel.</li>
                <li>Create → Upload video. Title: "{{ \Illuminate\Support\Str::limit($activity->title, 60) }} | IKS Show &amp; Tell".</li>
                <li>Audience: "No, it's not made for kids". Visibility: <b>Public</b>.</li>
                <li>When it has finished processing, press Share → Copy link and paste it here.</li>
            </ol>
        </div>
    </div>

    <div class="st-tips">
        <p>📶 <b>Saving data:</b> record at 720p, upload on Wi-Fi, and keep the YouTube app open; uploads resume if the connection drops.</p>
        <p>🔒 <b>Stay safe:</b> close email and chats before recording, and never show passwords or your phone number. The course datasets are made up, so they are safe to show.</p>
        <p>💼 <b>Why public?</b> Your channel becomes a portfolio. Recruiters can watch you explain real work, and your best videos appear on your IKS portfolio.</p>
    </div>
</div>

@once
<style>
    .st-fact { display: flex; flex-direction: column; gap: .25rem; padding: .75rem .9rem; border-radius: .75rem; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.07); }
    .st-fact b { color: #fff; } .st-fact span { color: #a3a3b3; font-size: .8rem; line-height: 1.35; }
    .st-script { list-style: decimal; padding-left: 1.25rem; display: grid; gap: .45rem; }
    .st-script li::marker { color: #f87171; font-weight: 700; }
    .st-list { list-style: disc; padding-left: 1.1rem; display: grid; gap: .35rem; color: #c4c4d0; }
    .st-tips { display: grid; gap: .4rem; padding: .75rem .9rem; border-radius: .75rem; background: rgba(239,68,68,.06); border: 1px solid rgba(239,68,68,.18); font-size: .8rem; color: #d4d4dc; }
    .st-guide kbd { font-family: inherit; font-size: .72rem; padding: .05rem .35rem; border-radius: .3rem; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14); }
</style>
@endonce
