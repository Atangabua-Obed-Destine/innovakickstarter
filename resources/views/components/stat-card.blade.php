@props([
    'label',
    'value',
    'change' => null,
    'changeType' => 'up', // 'up', 'down', 'neutral'
    'icon' => null,
    'iconBg' => 'primary',
])

@php
    $iconBgColors = [
        'primary' => 'bg-primary-600/20 text-primary-400',
        'accent' => 'bg-accent-600/20 text-accent-400',
        'teal' => 'bg-teal-600/20 text-teal-400',
        'amber' => 'bg-amber-600/20 text-amber-400',
        'green' => 'bg-green-600/20 text-green-400',
        'red' => 'bg-red-600/20 text-red-400',
    ];
    
    $bgColor = $iconBgColors[$iconBg] ?? $iconBgColors['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'stat-card card-hover']) }}>
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="stat-label">{{ $label }}</p>
            <p class="stat-value mt-1">{{ $value }}</p>
            
            @if($change !== null)
                <div class="mt-2 stat-change {{ $changeType === 'up' ? 'stat-change-up' : ($changeType === 'down' ? 'stat-change-down' : 'text-dark-400') }}">
                    @if($changeType === 'up')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                    @elseif($changeType === 'down')
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    @endif
                    <span>{{ $change }}</span>
                </div>
            @endif
        </div>
        
        @if($icon)
            <div class="w-12 h-12 rounded-xl {{ $bgColor }} flex items-center justify-center flex-shrink-0">
                {!! $icon !!}
            </div>
        @endif
    </div>
    
    @if(isset($footer))
        <div class="mt-4 pt-4 border-t border-dark-700">
            {{ $footer }}
        </div>
    @endif
</div>
