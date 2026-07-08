@props([
    'type' => 'default',
    'size' => 'md',
    'dot' => false,
    'removable' => false,
])

@php
    $types = [
        'default' => 'bg-dark-600/50 text-dark-300 border-dark-500/30',
        'primary' => 'bg-primary-600/20 text-primary-400 border-primary-500/30',
        'accent' => 'bg-accent-600/20 text-accent-400 border-accent-500/30',
        'teal' => 'bg-teal-600/20 text-teal-400 border-teal-500/30',
        'success' => 'bg-green-600/20 text-green-400 border-green-500/30',
        'warning' => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
        'danger' => 'bg-red-600/20 text-red-400 border-red-500/30',
        'info' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
        
        // Status badges
        'pending' => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
        'approved' => 'bg-green-600/20 text-green-400 border-green-500/30',
        'rejected' => 'bg-red-600/20 text-red-400 border-red-500/30',
        'revision' => 'bg-orange-600/20 text-orange-400 border-orange-500/30',
        'completed' => 'bg-green-600/20 text-green-400 border-green-500/30',
        'scheduled' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
        'in_progress' => 'bg-purple-600/20 text-purple-400 border-purple-500/30',
        
        // Tier badges
        'rookie' => 'bg-gray-600/20 text-gray-400 border-gray-500/30',
        'intern' => 'bg-blue-600/20 text-blue-400 border-blue-500/30',
        'professional' => 'bg-purple-600/20 text-purple-400 border-purple-500/30',
        'elite' => 'bg-amber-600/20 text-amber-400 border-amber-500/30',
    ];
    
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];
    
    $dotColors = [
        'default' => 'bg-dark-400',
        'primary' => 'bg-primary-400',
        'accent' => 'bg-accent-400',
        'teal' => 'bg-teal-400',
        'success' => 'bg-green-400',
        'warning' => 'bg-amber-400',
        'danger' => 'bg-red-400',
        'info' => 'bg-blue-400',
        'pending' => 'bg-amber-400',
        'approved' => 'bg-green-400',
        'rejected' => 'bg-red-400',
        'revision' => 'bg-orange-400',
        'completed' => 'bg-green-400',
        'scheduled' => 'bg-blue-400',
        'in_progress' => 'bg-purple-400',
        'rookie' => 'bg-gray-400',
        'intern' => 'bg-blue-400',
        'professional' => 'bg-purple-400',
        'elite' => 'bg-amber-400',
    ];
    
    $typeClass = $types[$type] ?? $types['default'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
    $dotColor = $dotColors[$type] ?? $dotColors['default'];
@endphp

<span {{ $attributes->merge(['class' => "badge border $typeClass $sizeClass"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
    @endif
    
    {{ $slot }}
    
    @if($removable)
        <button type="button" class="ml-1 -mr-1 hover:opacity-75 transition-opacity">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    @endif
</span>
