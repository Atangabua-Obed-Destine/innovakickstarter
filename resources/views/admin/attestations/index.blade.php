@extends('layouts.app')

@section('title', 'Internship Attestations')

@section('content')
@php
    $tabs = [
        'ready' => ['Ready to issue', 'Internship ended and fees settled. Review and issue.'],
        'hold' => ['On hold', 'Waiting on a requirement, usually an unpaid fee.'],
        'issued' => ['Issued', 'Attestations that have been delivered.'],
        'revoked' => ['Revoked', 'Attestations withdrawn after issue.'],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Internship Attestations</h1>
            <p class="text-dark-400">Drafts are compiled automatically when an internship ends. Nothing is issued until you review it.</p>
        </div>
        <a href="{{ route('admin.attestations.settings') }}" class="btn btn-outline">Signatory &amp; document settings</a>
    </div>

    <!-- Tabs -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($tabs as $key => [$label, $hint])
            <a href="{{ route('admin.attestations.index', ['tab' => $key]) }}"
               class="card p-4 hover:border-primary-500/50 transition-colors {{ $tab === $key ? 'border-primary-500' : '' }}">
                <p class="text-dark-400 text-sm">{{ $label }}</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $counts[$key] }}</p>
            </a>
        @endforeach
    </div>

    <p class="text-dark-400 text-sm">{{ $tabs[$tab][1] }}</p>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800">
                    <tr>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Fellow</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Institution</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">Period</th>
                        <th class="text-left py-3 px-4 text-dark-400 font-medium">
                            {{ in_array($tab, ['issued', 'revoked']) ? 'Reference' : 'Status' }}
                        </th>
                        <th class="text-right py-3 px-4 text-dark-400 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse($rows as $row)
                        @php $internship = $row->internshipProfile; @endphp
                        <tr class="hover:bg-dark-800/50">
                            <td class="py-3 px-4">
                                <p class="text-white font-medium">{{ $row->fellow?->name }}</p>
                                <p class="text-dark-500 text-xs">{{ $row->fellow?->email }}</p>
                            </td>
                            <td class="py-3 px-4 text-dark-200 text-sm">{{ $internship?->institution_name }}</td>
                            <td class="py-3 px-4 text-dark-300 text-sm">
                                {{ $internship?->approved_start_date?->format('M j') }} – {{ $internship?->approved_end_date?->format('M j, Y') }}
                            </td>
                            <td class="py-3 px-4 text-sm">
                                @if($tab === 'issued' || $tab === 'revoked')
                                    <span class="font-mono text-white">{{ $row->reference }}</span>
                                    <span class="badge {{ $row->status_badge_class }} ml-2">{{ $row->status_label }}</span>
                                    <p class="text-dark-500 text-xs mt-1">
                                        {{ $tab === 'revoked' ? 'Revoked ' . $row->revoked_at?->format('M j, Y') : 'Issued ' . $row->issued_at?->format('M j, Y') }}
                                    </p>
                                @else
                                    @foreach($row->eligibility['checks'] as $check)
                                        <p class="{{ $check['passed'] ? 'text-emerald-400' : 'text-amber-400' }} text-xs">
                                            {{ $check['passed'] ? '✓' : '•' }} {{ $check['label'] }}@unless($check['passed']): {{ $check['detail'] }}@endunless
                                        </p>
                                    @endforeach
                                    @if($row->version > 1)
                                        <p class="text-dark-500 text-xs mt-1">Version {{ $row->version }} (reissue)</p>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <a href="{{ route('admin.attestations.show', $row) }}" class="btn btn-primary btn-sm">
                                    {{ $tab === 'ready' ? 'Review & issue' : 'Open' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-dark-400">Nothing here.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($rows, 'links'))
        {{ $rows->links() }}
    @endif
</div>
@endsection
