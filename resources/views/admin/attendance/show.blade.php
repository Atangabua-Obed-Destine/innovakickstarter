@extends('layouts.app')

@section('title', 'Attendance Live Board')

@section('content')
<div class="space-y-6" x-data="attendanceBoard('{{ $session->token }}', '{{ $session->status }}', '{{ route('admin.attendance.live-data', $session) }}')">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Attendance: {{ $session->date->format('M d, Y') }}</h1>
            <p class="text-dark-400 mt-1">
                @if($session->status === 'active')
                    <span class="inline-flex items-center text-emerald-400">
                        <span class="w-2 h-2 mr-2 bg-emerald-400 rounded-full animate-pulse"></span>
                        Live Session Active
                    </span>
                @else
                    <span class="inline-flex items-center text-dark-400">
                        Session Closed
                    </span>
                @endif
            </p>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('admin.attendance.index') }}" class="btn border border-dark-600 hover:bg-dark-700 text-white">Back</a>
            @if($session->status === 'active')
                <form action="{{ route('admin.attendance.close', $session) }}" method="POST" onsubmit="return confirm('Are you sure you want to close this session? Any fellow who has not clocked in will be marked Absent, and those who haven\'t clocked out will also be marked Absent.');">
                    @csrf
                    <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white border-transparent">
                        Close Session
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($session->status === 'active')
        <!-- Live QR Board -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- QR Code Section -->
            <div class="lg:col-span-1 card border border-dark-700 bg-dark-800 p-8 flex flex-col items-center justify-center text-center">
                <h2 class="text-xl font-bold text-white mb-2">Clock In QR Code</h2>
                <p class="text-sm text-dark-400 mb-8">Fellows: Scan this code from your dashboard to clock in.</p>
                
                <div class="bg-white p-4 rounded-xl shadow-lg mb-6">
                    <canvas id="qr-code-canvas"></canvas>
                </div>
                
                <div class="flex items-center gap-2 text-sm text-dark-400 bg-dark-900/50 px-4 py-2 rounded-lg">
                    <svg class="w-4 h-4 text-emerald-400 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Code refreshes automatically
                </div>
            </div>

            <!-- Live Feed Section -->
            <div class="lg:col-span-2 card border border-dark-700 bg-dark-800 flex flex-col h-[500px]">
                <div class="px-6 py-4 border-b border-dark-700 flex justify-between items-center bg-dark-900/30">
                    <h2 class="font-semibold text-white">Live Clock-ins</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-500/10 text-primary-400 border border-primary-500/20" x-text="records.length + ' fellows clocked in'"></span>
                </div>
                <div class="flex-1 overflow-y-auto p-6 space-y-3">
                    <template x-if="records.length === 0">
                        <div class="h-full flex flex-col items-center justify-center text-dark-500">
                            <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p>Waiting for fellows to clock in...</p>
                        </div>
                    </template>
                    <template x-for="record in records" :key="record.id">
                        <div class="flex items-center justify-between p-4 rounded-lg border border-dark-700 bg-dark-900/50 animate-fade-in">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-400 font-bold uppercase" x-text="record.fellow_name.charAt(0)"></div>
                                <div>
                                    <p class="font-medium text-white" x-text="record.fellow_name"></p>
                                    <p class="text-xs text-dark-400">Clocked in at <span x-text="record.clock_in_time"></span></p>
                                </div>
                            </div>
                            <div>
                                <template x-if="record.clock_out_time">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-dark-600 text-dark-300">
                                        Clocked out: <span x-text="record.clock_out_time" class="ml-1"></span>
                                    </span>
                                </template>
                                <template x-if="!record.clock_out_time">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Active
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    @else
        <!-- Closed Session Report -->
        <div class="card border border-dark-700 bg-dark-800">
            <div class="px-6 py-5 border-b border-dark-700 flex justify-between items-center">
                <h2 class="font-semibold text-white">Attendance Records</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-dark-900/50">
                            <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Fellow Name</th>
                            <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Clock In</th>
                            <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Clock Out</th>
                            <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider">Notes</th>
                            <th class="px-6 py-4 text-xs font-medium text-dark-300 uppercase tracking-wider text-right">Adjust</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-700">
                        @foreach($records as $record)
                            <tr class="hover:bg-dark-700/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-400 font-bold uppercase text-xs">
                                            {{ substr(optional($record->fellow)->name ?? '?', 0, 1) }}
                                        </div>
                                        <span class="text-white font-medium">{{ optional($record->fellow)->name ?? 'Deleted User' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-dark-300">
                                    {{ $record->clock_in_time ? $record->clock_in_time->format('h:i:s A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-dark-300">
                                    {{ $record->clock_out_time ? $record->clock_out_time->format('h:i:s A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($record->status === 'present')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Present</span>
                                    @elseif($record->status === 'absent')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400 border border-red-500/20">Absent</span>
                                    @elseif($record->status === 'late')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">Late</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">On Leave</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-dark-400 max-w-[200px] truncate">
                                    {{ $record->admin_notes ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <button @click="openAdjustModal('{{ $record->id }}', '{{ $record->status }}', '{{ addslashes($record->admin_notes) }}')" class="text-dark-400 hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Adjust Modal -->
        <div x-show="adjustModal.open" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="adjustModal.open" x-transition.opacity class="fixed inset-0 transition-opacity bg-dark-900/80 backdrop-blur-sm" @click="adjustModal.open = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="adjustModal.open" x-transition.scale class="relative z-10 inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-dark-800 rounded-2xl shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 border border-dark-700">
                    <h3 class="text-lg font-bold text-white mb-4">Adjust Record</h3>
                    <form :action="'{{ route('admin.attendance.index') }}/{{ $session->id }}/records/' + adjustModal.recordId" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-1">Status</label>
                                <select name="status" x-model="adjustModal.status" class="w-full bg-dark-900 border border-dark-600 rounded-lg text-white px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="on_leave">On Leave</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-dark-300 mb-1">Admin Notes (Reason)</label>
                                <textarea name="admin_notes" x-model="adjustModal.notes" rows="3" class="w-full bg-dark-900 border border-dark-600 rounded-lg text-white px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:border-transparent"></textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" @click="adjustModal.open = false" class="btn border border-dark-600 text-white hover:bg-dark-700">Cancel</button>
                            <button type="submit" class="btn bg-primary-600 hover:bg-primary-700 text-white border-transparent">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('attendanceBoard', (initialToken, status, liveDataUrl) => ({
            token: initialToken,
            status: status,
            liveDataUrl: liveDataUrl,
            records: [],
            qr: null,
            pollInterval: null,
            adjustModal: {
                open: false,
                recordId: '',
                status: '',
                notes: ''
            },

            init() {
                if (this.status === 'active') {
                    this.initQR();
                    this.fetchData(false);
                    
                    // Poll for live records every 2 seconds
                    this.pollInterval = setInterval(() => {
                        this.fetchData(false);
                    }, 2000);
                    
                    // Refresh QR token every 15 seconds for security
                    this.tokenInterval = setInterval(() => {
                        this.fetchData(true);
                    }, 15000);
                }
            },

            initQR() {
                this.qr = new QRious({
                    element: document.getElementById('qr-code-canvas'),
                    value: this.token,
                    size: 250,
                    level: 'H'
                });
            },

            fetchData(refreshToken = false) {
                const url = new URL(this.liveDataUrl);
                if (refreshToken) {
                    url.searchParams.append('refresh_token', '1');
                }
                
                fetch(url.toString())
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'closed') {
                            window.location.reload();
                            return;
                        }
                        
                        // Only update token and QR code if it actually changed
                        if (data.token && data.token !== this.token) {
                            this.token = data.token;
                            if (this.qr) {
                                this.qr.value = this.token;
                            }
                        }
                        
                        this.records = data.records;
                    })
                    .catch(err => console.error("Polling error:", err));
            },

            openAdjustModal(id, status, notes) {
                this.adjustModal.recordId = id;
                this.adjustModal.status = status;
                this.adjustModal.notes = notes;
                this.adjustModal.open = true;
            }
        }));
    });
</script>
<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }
    .animate-spin-slow {
        animation: spin 3s linear infinite;
    }
</style>
@endpush
