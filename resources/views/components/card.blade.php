@props([
    'hover' => false,
    'glass' => false,
    'padding' => 'md',
])

@php
    $paddings = [
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8',
    ];
    
    $paddingClass = $paddings[$padding] ?? $paddings['md'];
@endphp

<div {{ $attributes->merge([
    'class' => ($glass ? 'card-glass' : 'card') . 
               ($hover ? ' card-hover' : '') . 
               ' ' . $paddingClass
]) }}>
    @if(isset($header))
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-dark-700">
            {{ $header }}
        </div>
    @endif
    
    {{ $slot }}
    
    @if(isset($footer))
        <div class="mt-4 pt-4 border-t border-dark-700">
            {{ $footer }}
        </div>
    @endif
</div>
