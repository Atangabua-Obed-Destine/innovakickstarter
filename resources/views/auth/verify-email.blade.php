@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')
<div class="w-full max-w-md mx-auto">
    <div class="card p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 mx-auto bg-primary-600/20 rounded-full flex items-center justify-center text-3xl mb-4">
                📧
            </div>
            <h1 class="text-2xl font-bold text-white">Verify Your Email</h1>
            <p class="text-dark-400 mt-2">
                Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.
            </p>
        </div>

        @if (session('message'))
            <div class="mb-6 p-4 bg-green-600/20 border border-green-500/30 rounded-lg text-green-400 text-center">
                {{ session('message') }}
            </div>
        @endif

        <div class="space-y-4">
            <p class="text-dark-300 text-sm text-center">
                If you didn't receive the email, we will gladly send you another.
            </p>

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary w-full">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Resend Verification Email
                </button>
            </form>

            <div class="text-center">
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-dark-400 hover:text-white text-sm transition-colors">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
