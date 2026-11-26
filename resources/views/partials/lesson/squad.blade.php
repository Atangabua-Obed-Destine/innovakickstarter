{{-- Squad chapters: the fellow's squad (or a "being formed" note). Expects $squad (?Squad) and $squadWaiting (int). --}}
<div class="card p-6" id="squad" style="border-color: rgba(16,185,129,.35);">
    @if($squad)
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="text-white font-semibold text-lg">👥 {{ $squad->name }}</h3>
                <p class="text-dark-400 text-sm mt-1">You build this chapter as a team. Agree your sprint plan, share one repository, review each other's pull requests, and keep your own commits and reviews visible: you are graded on your part and on the team result.</p>
            </div>
        </div>
        <div class="grid gap-2 mt-4" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
            @foreach($squad->members as $m)
                <div class="sq-member">
                    <span class="sq-avatar">{{ collect(explode(' ', (string) $m->fellow?->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode('') }}</span>
                    <div class="min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ $m->fellow?->name }}{{ $m->fellow_id === auth()->id() ? ' (you)' : '' }}</p>
                        <p class="text-dark-400 text-xs">{{ $m->pathway ? ucfirst($m->pathway) : 'Pathway not chosen' }}
                            @if($m->fellow?->github_url) · <a href="{{ $m->fellow->github_url }}" target="_blank" rel="noopener" class="text-primary-400">GitHub</a>@endif</p>
                    </div>
                </div>
            @endforeach
        </div>
        <form method="POST" action="{{ route('curriculum.squad.repo', $squad) }}" class="flex flex-wrap items-center gap-2 mt-4">
            @csrf
            <label for="squad-repo" class="text-dark-300 text-sm">Squad repository:</label>
            <input id="squad-repo" type="url" name="repo_url" value="{{ $squad->repo_url }}" placeholder="https://github.com/your-squad/nkwen-orders-platform" maxlength="300"
                   class="flex-1 min-w-0 bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm" style="min-width: 220px;">
            <button type="submit" class="btn-secondary text-sm">Save</button>
            @if($squad->repo_url)<a href="{{ $squad->repo_url }}" target="_blank" rel="noopener" class="text-primary-400 text-sm">Open ↗</a>@endif
        </form>
    @else
        <h3 class="text-white font-semibold text-lg">👥 Your squad is being formed</h3>
        <p class="text-dark-400 text-sm mt-1">This chapter is team work. The I-NNOVA team groups fellows who reach it into squads of 3 to 5 that mix pathways (backend, frontend, mobile, full-stack). You will get a notification with your squad's names.
            @if($squadWaiting > 1) {{ $squadWaiting }} fellows are waiting for a squad right now.@endif
            Meanwhile, read the chapter's lessons and set up your tools.</p>
    @endif
</div>
@once
<style>
    .sq-member { display: flex; align-items: center; gap: .6rem; padding: .55rem .7rem; border-radius: .7rem; background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08); }
    .sq-avatar { width: 34px; height: 34px; border-radius: 999px; display: flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; color: #fff; background: rgba(16,185,129,.35); flex-shrink: 0; }
</style>
@endonce
