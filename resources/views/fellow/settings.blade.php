@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-white">Settings</h1>
        <p class="text-dark-400">Manage your account settings and preferences</p>
    </div>

    <!-- Settings Navigation -->
    <div x-data="{ activeTab: 'account' }" class="space-y-6">
        <!-- Tabs -->
        <div class="flex gap-2 overflow-x-auto pb-2 border-b border-dark-700">
            <button @click="activeTab = 'account'" 
                    :class="activeTab === 'account' ? 'text-primary-400 border-primary-400' : 'text-dark-400 border-transparent hover:text-dark-200'"
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-[2px] transition-colors whitespace-nowrap">
                Account
            </button>
            <button @click="activeTab = 'notifications'" 
                    :class="activeTab === 'notifications' ? 'text-primary-400 border-primary-400' : 'text-dark-400 border-transparent hover:text-dark-200'"
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-[2px] transition-colors whitespace-nowrap">
                Notifications
            </button>
            <button @click="activeTab = 'privacy'" 
                    :class="activeTab === 'privacy' ? 'text-primary-400 border-primary-400' : 'text-dark-400 border-transparent hover:text-dark-200'"
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-[2px] transition-colors whitespace-nowrap">
                Privacy
            </button>
            <button @click="activeTab = 'security'" 
                    :class="activeTab === 'security' ? 'text-primary-400 border-primary-400' : 'text-dark-400 border-transparent hover:text-dark-200'"
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-[2px] transition-colors whitespace-nowrap">
                Security
            </button>
            <button @click="activeTab = 'billing'" 
                    :class="activeTab === 'billing' ? 'text-primary-400 border-primary-400' : 'text-dark-400 border-transparent hover:text-dark-200'"
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-[2px] transition-colors whitespace-nowrap">
                Billing
            </button>
        </div>

        <!-- Account Settings -->
        <div x-show="activeTab === 'account'" x-transition class="space-y-6">
            <!-- Email Settings -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Email Address
                </h3>
                <form class="space-y-4">
                    <div>
                        <label class="form-label">Current Email</label>
                        <input type="email" class="form-input" value="{{ auth()->user()->email ?? 'john@example.com' }}" disabled>
                    </div>
                    <div>
                        <label class="form-label">New Email Address</label>
                        <input type="email" class="form-input" placeholder="Enter new email address">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Email</button>
                </form>
            </div>

            <!-- Username Settings -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Username
                </h3>
                <form class="space-y-4">
                    <div>
                        <label class="form-label">Username</label>
                        <div class="flex">
                            <span class="inline-flex items-center px-3 bg-dark-700 border border-r-0 border-dark-600 rounded-l-lg text-dark-400 text-sm">
                                innova.cmr/
                            </span>
                            <input type="text" class="form-input rounded-l-none" value="{{ auth()->user()->username ?? 'johndoe' }}">
                        </div>
                        <p class="text-dark-500 text-xs mt-1">Your public profile URL</p>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Username</button>
                </form>
            </div>

            <!-- Language & Timezone -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Language & Timezone
                </h3>
                <form class="space-y-4">
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Language</label>
                            <select class="form-input">
                                <option value="en" selected>English</option>
                                <option value="fr">Français</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Timezone</label>
                            <select class="form-input">
                                <option value="Africa/Douala" selected>Africa/Douala (WAT)</option>
                                <option value="UTC">UTC</option>
                                <option value="Europe/London">Europe/London (GMT)</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                </form>
            </div>

            <!-- Delete Account -->
            <div class="card p-6 border-red-500/30">
                <h3 class="text-lg font-semibold text-red-400 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Danger Zone
                </h3>
                <p class="text-dark-400 mb-4">Once you delete your account, there is no going back. Please be certain.</p>
                <button class="btn bg-red-600/20 text-red-400 border border-red-500/30 hover:bg-red-600/30">
                    Delete Account
                </button>
            </div>
        </div>

        <!-- Notification Settings -->
        <div x-show="activeTab === 'notifications'" x-transition class="space-y-6">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Email Notifications</h3>
                <div class="space-y-4">
                    @foreach([
                        ['label' => 'Activity Reminders', 'desc' => 'Receive reminders about pending activities', 'checked' => true],
                        ['label' => 'Weekly Progress Reports', 'desc' => 'Get a weekly summary of your progress', 'checked' => true],
                        ['label' => 'Interview Confirmations', 'desc' => 'Receive interview scheduling updates', 'checked' => true],
                        ['label' => 'Leaderboard Updates', 'desc' => 'Get notified when your ranking changes', 'checked' => false],
                        ['label' => 'New Activities', 'desc' => 'Be notified when new activities are available', 'checked' => true],
                        ['label' => 'Recruiter Messages', 'desc' => 'Get notified when recruiters view your profile', 'checked' => true],
                    ] as $notification)
                        <label class="flex items-center justify-between p-4 bg-dark-800 rounded-lg cursor-pointer hover:bg-dark-700 transition-colors">
                            <div>
                                <p class="text-dark-200 font-medium">{{ $notification['label'] }}</p>
                                <p class="text-dark-500 text-sm">{{ $notification['desc'] }}</p>
                            </div>
                            <div class="relative">
                                <input type="checkbox" class="sr-only peer" {{ $notification['checked'] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-dark-600 rounded-full peer-checked:bg-primary-600 transition-colors"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full peer-checked:translate-x-5 transition-transform"></div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Push Notifications</h3>
                <div class="space-y-4">
                    @foreach([
                        ['label' => 'Desktop Notifications', 'desc' => 'Show desktop notifications in browser', 'checked' => false],
                        ['label' => 'Mobile Push', 'desc' => 'Receive push notifications on mobile', 'checked' => true],
                    ] as $notification)
                        <label class="flex items-center justify-between p-4 bg-dark-800 rounded-lg cursor-pointer hover:bg-dark-700 transition-colors">
                            <div>
                                <p class="text-dark-200 font-medium">{{ $notification['label'] }}</p>
                                <p class="text-dark-500 text-sm">{{ $notification['desc'] }}</p>
                            </div>
                            <div class="relative">
                                <input type="checkbox" class="sr-only peer" {{ $notification['checked'] ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-dark-600 rounded-full peer-checked:bg-primary-600 transition-colors"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full peer-checked:translate-x-5 transition-transform"></div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Privacy Settings -->
        <div x-show="activeTab === 'privacy'" x-transition class="space-y-6">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Profile Visibility</h3>
                <div class="space-y-4">
                    <label class="flex items-center justify-between p-4 bg-dark-800 rounded-lg cursor-pointer hover:bg-dark-700 transition-colors">
                        <div>
                            <p class="text-dark-200 font-medium">Public Profile</p>
                            <p class="text-dark-500 text-sm">Allow recruiters to view your profile on the marketplace</p>
                        </div>
                        <div class="relative">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-dark-600 rounded-full peer-checked:bg-primary-600 transition-colors"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full peer-checked:translate-x-5 transition-transform"></div>
                        </div>
                    </label>
                    
                    <label class="flex items-center justify-between p-4 bg-dark-800 rounded-lg cursor-pointer hover:bg-dark-700 transition-colors">
                        <div>
                            <p class="text-dark-200 font-medium">Show on Leaderboard</p>
                            <p class="text-dark-500 text-sm">Display your name on public leaderboards</p>
                        </div>
                        <div class="relative">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-dark-600 rounded-full peer-checked:bg-primary-600 transition-colors"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full peer-checked:translate-x-5 transition-transform"></div>
                        </div>
                    </label>

                    <label class="flex items-center justify-between p-4 bg-dark-800 rounded-lg cursor-pointer hover:bg-dark-700 transition-colors">
                        <div>
                            <p class="text-dark-200 font-medium">Show Career Capital Score</p>
                            <p class="text-dark-500 text-sm">Display your score on your public profile</p>
                        </div>
                        <div class="relative">
                            <input type="checkbox" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-dark-600 rounded-full peer-checked:bg-primary-600 transition-colors"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full peer-checked:translate-x-5 transition-transform"></div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Data & Privacy</h3>
                <div class="space-y-4">
                    <button class="btn btn-outline w-full sm:w-auto">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download My Data
                    </button>
                    <p class="text-dark-500 text-sm">Download a copy of all your data in JSON format.</p>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div x-show="activeTab === 'security'" x-transition class="space-y-6">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Change Password
                </h3>
                <form class="space-y-4">
                    <div>
                        <label class="form-label">Current Password</label>
                        <input type="password" class="form-input" placeholder="Enter current password">
                    </div>
                    <div>
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-input" placeholder="Enter new password">
                    </div>
                    <div>
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-input" placeholder="Confirm new password">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>

            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Two-Factor Authentication
                </h3>
                <p class="text-dark-400 mb-4">Add an extra layer of security to your account.</p>
                <button class="btn btn-outline">
                    Enable 2FA
                </button>
            </div>

            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Active Sessions</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-4 bg-dark-800 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-dark-700 flex items-center justify-center">
                                <svg class="w-5 h-5 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-dark-200 font-medium">Windows PC - Chrome</p>
                                <p class="text-dark-500 text-sm">Douala, Cameroon • Current session</p>
                            </div>
                        </div>
                        <span class="text-green-400 text-sm">Active</span>
                    </div>
                </div>
                <button class="mt-4 text-red-400 text-sm hover:underline">
                    Sign out all other sessions
                </button>
            </div>
        </div>

        <!-- Billing Settings -->
        <div x-show="activeTab === 'billing'" x-transition class="space-y-6">
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Current Plan</h3>
                <div class="p-4 bg-gradient-to-r from-primary-600/20 to-blue-600/20 rounded-lg border border-primary-500/30">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-primary-400 font-semibold">Fellow Access</p>
                            <p class="text-dark-400 text-sm">Full access to all learning activities and features</p>
                        </div>
                        <span class="px-3 py-1 bg-primary-600 text-white text-sm font-medium rounded-full">Active</span>
                    </div>
                </div>
                <p class="text-dark-500 text-sm mt-4">Your access is provided by I-NNOVA CMR through your cohort enrollment.</p>
            </div>

            <div class="card p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Payment History</h3>
                <p class="text-dark-400">No payment history available. Your access is complimentary.</p>
            </div>
        </div>
    </div>
</div>
@endsection
