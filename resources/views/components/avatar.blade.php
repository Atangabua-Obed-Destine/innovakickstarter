@props([
    'name' => '',
    'src' => null,
    'size' => 'md',
    'status' => null, // 'online', 'offline', 'away', 'busy'
])

@php
    $sizes = [
        'xs' => 'avatar-sm w-6 h-6 text-[10px]',
        'sm' => 'avatar-sm',
        'md' => 'avatar-md',
        'lg' => 'avatar-lg',
        'xl' => 'avatar-xl',
        '2xl' => 'avatar-2xl',
    ];
    
    $statusSizes = [
        'xs' => 'w-1.5 h-1.5 border',
        'sm' => 'w-2 h-2 border',
        'md' => 'w-2.5 h-2.5 border-2',
        'lg' => 'w-3 h-3 border-2',
        'xl' => 'w-4 h-4 border-2',
        '2xl' => 'w-5 h-5 border-2',
    ];
    
    $statusColors = [
        'online' => 'bg-green-500',
        'offline' => 'bg-dark-500',
        'away' => 'bg-amber-500',
        'busy' => 'bg-red-500',
    ];
    
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $statusSizeClass = $statusSizes[$size] ?? $statusSizes['md'];
    $statusColor = $status ? ($statusColors[$status] ?? $statusColors['offline']) : null;
    
    // Get initials from name
    $initials = collect(explode(' ', $name))
        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
        ->take(2)
        ->implode('');
@endphp

<div {{ $attributes->merge(['class' => 'relative inline-flex']) }}>
    <div class="avatar {{ $sizeClass }}">
        @if($src)
            <img src="{{ $src }}" alt="{{ $name }}" class="w-full h-full object-cover">
        @else
            {{ $initials ?: '?' }}
        @endif
    </div>
    
    @if($status)
        <span class="absolute bottom-0 right-0 {{ $statusSizeClass }} {{ $statusColor }} rounded-full border-dark-900"></span>
    @endif
</div>
