@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'loading' => false,
    'disabled' => false,
    'iconOnly' => false,
])

@php
    $variants = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'accent' => 'btn-accent',
        'teal' => 'btn-teal',
        'ghost' => 'btn-ghost',
        'danger' => 'btn-danger',
        'gradient' => 'btn-gradient',
    ];
    
    $sizes = [
        'xs' => 'px-2 py-1 text-xs',
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];
    
    $iconOnlySizes = [
        'xs' => 'p-1',
        'sm' => 'p-1.5',
        'md' => 'p-2.5',
        'lg' => 'p-3',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $iconOnly ? ($iconOnlySizes[$size] ?? $iconOnlySizes['md']) : ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a 
        href="{{ $href }}" 
        {{ $attributes->merge(['class' => "btn $variantClass $sizeClass" . ($disabled ? ' opacity-50 pointer-events-none' : '')]) }}
    >
        @if($loading)
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </a>
@else
    <button 
        type="{{ $type }}"
        {{ $disabled || $loading ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => "btn $variantClass $sizeClass"]) }}
    >
        @if($loading)
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        {{ $slot }}
    </button>
@endif
