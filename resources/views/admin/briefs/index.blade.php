@extends('layouts.app')

@section('title', 'Employer Briefs')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Employer briefs</h1>
            <p class="text-dark-400 mt-1">Short real-world challenges from employers. Open briefs are shown to fellows of the chosen track and announced in their notifications.</p>
        </div>
        <a href="{{ route('admin.briefs.create') }}" class="btn btn-primary">+ New brief</a>
    </div>
    @if(session('success'))<div class="p-4 bg-green-500/10 border border-green-500/30 rounded-xl text-green-400">{{ session('success') }}</div>@endif
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead><tr class="border-b border-dark-700 text-left">
                    @foreach(['Brief', 'Company', 'Track', 'Deadline', 'Status', 'Submissions'] as $h)<th class="px-6 py-3 text-dark-400 text-xs font-medium uppercase tracking-wider">{{ $h }}</th>@endforeach
                </tr></thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($briefs as $brief)
                        <tr class="hover:bg-dark-800/50">
                            <td class="px-6 py-4"><a href="{{ route('admin.briefs.show', $brief) }}" class="text-white hover:text-primary-300 font-medium">{{ $brief->title }}</a></td>
                            <td class="px-6 py-4 text-dark-300 text-sm">{{ $brief->company_name }}</td>
                            <td class="px-6 py-4 text-dark-300 text-sm">{{ $brief->track?->name ?? 'All tracks' }}</td>
                            <td class="px-6 py-4 text-dark-300 text-sm">{{ $brief->deadline_on->format('j M Y') }}</td>
                            <td class="px-6 py-4 text-sm"><span class="badge {{ $brief->status === 'open' ? 'bg-green-600/20 text-green-400 border-green-500/30' : 'bg-dark-600/20 text-dark-400 border-dark-500/30' }}">{{ \App\Models\EmployerBrief::STATUSES[$brief->status] }}</span></td>
                            <td class="px-6 py-4 text-dark-300 text-sm">{{ $brief->submissions_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-dark-400">No briefs yet. Create one from an employer's real problem.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
