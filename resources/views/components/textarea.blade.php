@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'rows' => 4,
])

@php
    $hasError = $errors->has($name);
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    @if($label)
        <label for="{{ $name }}" class="label">
            {{ $label }}
            @if($required)
                <span class="text-red-400">*</span>
            @endif
        </label>
    @endif
    
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $readonly ? 'readonly' : '' }}
        {{ $attributes->except('class')->merge([
            'class' => 'textarea' . 
                ($hasError ? ' input-error' : '') .
                ($disabled ? ' opacity-50 cursor-not-allowed' : '')
        ]) }}
    >{{ old($name, $value) }}</textarea>
    
    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-dark-500">{{ $hint }}</p>
    @endif
    
    @error($name)
        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
    @enderror
</div>
