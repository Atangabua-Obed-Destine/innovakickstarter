@props([
    'score' => 0,
    'size' => 'md',
    'showLabel' => true,
    'tier' => null,
    'animated' => true
])

@php
    $sizes = [
        'sm' => ['container' => 'w-16 h-16', 'ring' => 48, 'stroke' => 4, 'text' => 'text-sm'],
        'md' => ['container' => 'w-24 h-24', 'ring' => 72, 'stroke' => 6, 'text' => 'text-xl'],
        'lg' => ['container' => 'w-32 h-32', 'ring' => 96, 'stroke' => 8, 'text' => 'text-2xl'],
        'xl' => ['container' => 'w-40 h-40', 'ring' => 120, 'stroke' => 10, 'text' => 'text-3xl'],
        '2xl' => ['container' => 'w-48 h-48', 'ring' => 144, 'stroke' => 12, 'text' => 'text-4xl'],
    ];
    
    $config = $sizes[$size] ?? $sizes['md'];
    $radius = ($config['ring'] - $config['stroke']) / 2;
    $circumference = 2 * pi() * $radius;
    $progress = ($score / 100) * $circumference;
    $dashOffset = $circumference - $progress;
    
    // Determine color based on score or tier
    $tierColors = [
        'rookie' => 'text-gray-500',
        'intern' => 'text-blue-500',
        'professional' => 'text-purple-500',
        'elite' => 'text-amber-500',
    ];
    
    $strokeColors = [
        'rookie' => 'stroke-gray-500',
        'intern' => 'stroke-blue-500',
        'professional' => 'stroke-purple-500',
        'elite' => 'stroke-amber-500',
    ];
    
    $color = $tier ? ($tierColors[$tier] ?? 'text-primary-500') : 'text-primary-500';
    $strokeColor = $tier ? ($strokeColors[$tier] ?? 'stroke-primary-500') : 'stroke-primary-500';
@endphp

<div class="score-circle {{ $config['container'] }}" {{ $attributes }}>
    <svg class="score-circle-ring w-full h-full" viewBox="0 0 {{ $config['ring'] }} {{ $config['ring'] }}">
        {{-- Background circle --}}
        <circle
            cx="{{ $config['ring'] / 2 }}"
            cy="{{ $config['ring'] / 2 }}"
            r="{{ $radius }}"
            fill="none"
            stroke="currentColor"
            stroke-width="{{ $config['stroke'] }}"
            class="text-dark-700"
        />
        
        {{-- Progress circle --}}
        <circle
            cx="{{ $config['ring'] / 2 }}"
            cy="{{ $config['ring'] / 2 }}"
            r="{{ $radius }}"
            fill="none"
            stroke="currentColor"
            stroke-width="{{ $config['stroke'] }}"
            stroke-linecap="round"
            stroke-dasharray="{{ $circumference }}"
            stroke-dashoffset="{{ $animated ? $circumference : $dashOffset }}"
            class="{{ $strokeColor }} transition-all duration-1000 ease-out"
            @if($animated)
                x-data
                x-init="setTimeout(() => $el.style.strokeDashoffset = '{{ $dashOffset }}', 100)"
            @endif
        />
    </svg>
    
    <div class="score-circle-value flex-col {{ $config['text'] }} {{ $color }}">
        <span class="font-bold">{{ number_format($score, 0) }}%</span>
        @if($showLabel && $tier)
            <span class="text-xs font-medium text-dark-400 capitalize">{{ $tier }}</span>
        @endif
    </div>
</div>
