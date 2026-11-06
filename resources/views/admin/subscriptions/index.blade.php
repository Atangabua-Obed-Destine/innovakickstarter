@extends('layouts.app')

@section('title', 'Recruiter Plan Requests')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Recruiter Plan Requests</h1>
            <p class="text-dark-400">Paid plans start only after you confirm the payment. Check each reference against your MoMo or bank statement.</p>
        </div>
        <a href="{{ route('admin.recruiters.index') }}" class="btn btn-outline">All recruiters</a>
    </div>

    <!-- Awaiting confirmation -->
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-white">Awaiting payment confirmation</h2>
            <span class="text-sm text-dark-400">{{ $pending->count() }}</span>
        </div>

        @forelse($pending as $request)
            <div class="px-6 py-5 border-b border-dark-700 last:border-b-0" x-data="{ rejecting: false }">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                    <div class="space-y-1">
                        <p class="text-white font-medium">
                            <a href="{{ route('admin.recruiters.show', $request->recruiter) }}" class="hover:text-primary-400">{{ $request->recruiter->name }}</a>
                            @if($request->recruiter->company_name)
                                <span class="text-dark-400 font-normal">· {{ $request->recruiter->company_name }}</span>
                            @endif
                        </p>
                        <p class="text-sm text-dark-300">
                            <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full {{ $request->tier->badgeClass() }}">{{ $request->tier->label() }}</span>
                            @if($request->requested_months)
                                {{ $request->requested_months }} month{{ $request->requested_months > 1 ? 's' : '' }}
                            @else
                                {{ $request->notes ?? 'Upgrade' }}
                            @endif
                        </p>
                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-1 text-sm mt-2">
                            <div>
                                <dt class="text-dark-500">Amount due</dt>
                                <dd class="text-white font-semibold">{{ number_format((int) $request->amount, 0, '.', ' ') }} XAF</dd>
                            </div>
                            <div>
                                <dt class="text-dark-500">Method</dt>
                                <dd class="text-dark-200">{{ $paymentMethods[$request->payment_method] ?? ($request->payment_method ?: '—') }}</dd>
                            </div>
                            <div>
                                <dt class="text-dark-500">Reference</dt>
                                <dd class="text-dark-200 font-mono break-all">{{ $request->payment_reference }}</dd>
                            </div>
                        </dl>
                        <p class="text-xs text-dark-500 mt-1">Requested {{ $request->created_at->diffForHumans() }}</p>
                    </div>

                    <div class="flex gap-2 shrink-0">
                        <form method="POST" action="{{ route('admin.subscriptions.approve', $request) }}"
                              onsubmit="return confirm('Confirm that {{ number_format((int) $request->amount, 0, '.', ' ') }} XAF was received (ref. {{ e($request->payment_reference) }}) and start the plan?')">
                            @csrf
                            <button type="submit" class="btn btn-primary">Confirm payment</button>
                        </form>
                        <button type="button" @click="rejecting = !rejecting" class="btn btn-outline text-red-400 border-red-500/40 hover:bg-red-500/10">Reject</button>
                    </div>
                </div>

                <form x-show="rejecting" x-cloak method="POST" action="{{ route('admin.subscriptions.reject', $request) }}" class="mt-4 flex flex-col sm:flex-row gap-3">
                    @csrf
                    <input type="text" name="reason" required minlength="5" maxlength="500" class="form-input flex-1"
                           placeholder="Reason the recruiter will see, e.g. No payment found with this reference">
                    <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white">Reject request</button>
                </form>
            </div>
        @empty
            <p class="px-6 py-10 text-center text-dark-400">No plan requests waiting.</p>
        @endforelse
    </div>

    <!-- Recent decisions -->
    @if($recent->isNotEmpty())
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-dark-700">
                <h2 class="text-lg font-semibold text-white">Recent decisions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-dark-800">
                        <tr>
                            <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Recruiter</th>
                            <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Plan</th>
                            <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Amount</th>
                            <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Reference</th>
                            <th class="text-left py-3 px-6 text-dark-400 font-medium text-xs uppercase tracking-wider">Decision</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-700">
                        @foreach($recent as $item)
                            <tr>
                                <td class="px-6 py-3 text-white">{{ $item->recruiter?->name ?? 'Deleted recruiter' }}</td>
                                <td class="px-6 py-3 text-dark-300">{{ $item->tier->label() }}</td>
                                <td class="px-6 py-3 text-dark-300">{{ number_format((int) $item->amount, 0, '.', ' ') }} XAF</td>
                                <td class="px-6 py-3 text-dark-300 font-mono">{{ $item->payment_reference ?? '—' }}</td>
                                <td class="px-6 py-3">
                                    @if($item->rejection_reason)
                                        <span class="text-red-400">Rejected</span>
                                        <span class="text-dark-500">· {{ $item->rejection_reason }}</span>
                                    @else
                                        <span class="text-green-400">Confirmed</span>
                                    @endif
                                    <span class="block text-xs text-dark-500">{{ $item->verified_at->format('M j, Y H:i') }} by {{ $item->verifier?->name ?? '—' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
