@props([
    'value' => 0,
    'max' => 100,
    'color' => 'primary',
    'size' => 'md',
    'showLabel' => false,
    'animated' => true,
    'striped' => false,
])

@php
    $percentage = $max > 0 ? min(100, ($value / $max) * 100) : 0;
    
    $colors = [
        'primary' => 'bg-primary-500',
        'accent' => 'bg-accent-500',
        'teal' => 'bg-teal-500',
        'skills' => 'bg-primary-500',
        'experience' => 'bg-accent-500',
        'network' => 'bg-teal-500',
        'credentials' => 'bg-amber-500',
        'success' => 'bg-green-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'gradient' => 'bg-gradient-to-r from-primary-500 via-accent-500 to-teal-500',
    ];
    
    $sizes = [
        'xs' => 'h-1',
        'sm' => 'h-1.5',
        'md' => 'h-2',
        'lg' => 'h-3',
        'xl' => 'h-4',
    ];
    
    $bgColor = $colors[$color] ?? $colors['primary'];
    $height = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => 'w-full']) }}>
    @if($showLabel)
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-sm font-medium text-dark-300">{{ $slot }}</span>
            <span class="text-sm font-semibold text-dark-100">{{ number_format($value, 0) }}%</span>
        </div>
    @endif
    
    <div class="progress-bar {{ $height }} {{ $animated ? 'progress-bar-animated' : '' }}">
        <div 
            class="progress-bar-fill {{ $bgColor }} {{ $striped ? 'progress-bar-striped' : '' }}"
            style="--progress-value: {{ $percentage }}%; {{ $animated ? '' : 'width: ' . $percentage . '%;' }}"
            @if($animated)
                x-data
                x-init="setTimeout(() => $el.style.width = '{{ $percentage }}%', 100)"
            @endif
        ></div>
    </div>
</div>
