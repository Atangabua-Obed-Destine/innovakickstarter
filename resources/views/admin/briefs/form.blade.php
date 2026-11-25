@extends('layouts.app')

@section('title', $brief->exists ? 'Edit brief' : 'New brief')

@section('content')
@php $input = 'w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500'; @endphp
<div class="max-w-3xl mx-auto space-y-5">
    <a href="{{ route('admin.briefs.index') }}" class="text-dark-400 hover:text-white">← Employer briefs</a>
    <h1 class="text-2xl font-bold text-white">{{ $brief->exists ? 'Edit brief' : 'New employer brief' }}</h1>
    @if($errors->any())
        <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm"><ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" action="{{ $brief->exists ? route('admin.briefs.update', $brief) : route('admin.briefs.store') }}" class="card p-6 space-y-4">
        @csrf
        @if($brief->exists) @method('PUT') @endif
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-dark-300 text-sm mb-1">Title</label><input name="title" required maxlength="200" value="{{ old('title', $brief->title) }}" class="{{ $input }}" placeholder="Which products should we stock for the rainy season?"></div>
            <div><label class="block text-dark-300 text-sm mb-1">Company</label><input name="company_name" required maxlength="150" value="{{ old('company_name', $brief->company_name) }}" class="{{ $input }}"></div>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div><label class="block text-dark-300 text-sm mb-1">Track</label>
                <select name="track_id" class="{{ $input }}"><option value="">All tracks</option>
                    @foreach($tracks as $t)<option value="{{ $t->id }}" @selected(old('track_id', $brief->track_id) === $t->id)>{{ $t->name }}</option>@endforeach
                </select></div>
            <div><label class="block text-dark-300 text-sm mb-1">Linked recruiter account (sees the shortlist)</label>
                <select name="recruiter_id" class="{{ $input }}"><option value="">None</option>
                    @foreach($recruiters as $r)<option value="{{ $r->id }}" @selected((string) old('recruiter_id', $brief->recruiter_id) === (string) $r->id)>{{ $r->company_name ?: $r->name }} ({{ $r->name }})</option>@endforeach
                </select></div>
        </div>
        <div><label class="block text-dark-300 text-sm mb-1">The challenge (Markdown)</label><textarea name="summary" rows="6" required class="{{ $input }}" placeholder="The business context, the question, any constraints.">{{ old('summary', $brief->summary) }}</textarea></div>
        <div><label class="block text-dark-300 text-sm mb-1">What to submit (Markdown)</label><textarea name="deliverable" rows="4" required class="{{ $input }}" placeholder="e.g. A notebook or dashboard link plus 3 recommendations.">{{ old('deliverable', $brief->deliverable) }}</textarea></div>
        <div class="grid sm:grid-cols-3 gap-4">
            <div><label class="block text-dark-300 text-sm mb-1">Data / resources link</label><input type="url" name="resources_url" maxlength="500" value="{{ old('resources_url', $brief->resources_url) }}" class="{{ $input }}"></div>
            <div><label class="block text-dark-300 text-sm mb-1">Deadline</label><input type="date" name="deadline_on" required value="{{ old('deadline_on', $brief->deadline_on?->toDateString()) }}" class="{{ $input }}"></div>
            <div><label class="block text-dark-300 text-sm mb-1">Reward (optional)</label><input name="reward" maxlength="200" value="{{ old('reward', $brief->reward) }}" class="{{ $input }}" placeholder="Interview, internship, 50,000 FCFA..."></div>
        </div>
        <div><label class="block text-dark-300 text-sm mb-1">Status</label>
            <select name="status" class="{{ $input }}">@foreach(\App\Models\EmployerBrief::STATUSES as $k => $v)<option value="{{ $k }}" @selected(old('status', $brief->status) === $k)>{{ $v }}</option>@endforeach</select>
            <p class="text-dark-500 text-xs mt-1">Draft: only admins see it. Open: fellows can submit until the deadline. Closed: no new submissions.</p></div>
        <button type="submit" class="btn btn-primary">Save brief</button>
    </form>
</div>
@endsection
