@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'options' => [],
    'hint' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $hasError = $errors->has($name);
    $selectedValue = old($name, $value);
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
    
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->except('class')->merge([
            'class' => 'select' . 
                ($hasError ? ' input-error' : '') .
                ($disabled ? ' opacity-50 cursor-not-allowed' : '')
        ]) }}
    >
        @if($placeholder)
            <option value="" {{ !$selectedValue ? 'selected' : '' }} disabled>{{ $placeholder }}</option>
        @endif
        
        @if(is_array($options) && count($options) > 0)
            @foreach($options as $optionValue => $optionLabel)
                @if(is_array($optionLabel))
                    {{-- Option group --}}
                    <optgroup label="{{ $optionValue }}">
                        @foreach($optionLabel as $groupValue => $groupLabel)
                            <option 
                                value="{{ $groupValue }}" 
                                {{ (string)$selectedValue === (string)$groupValue ? 'selected' : '' }}
                            >
                                {{ $groupLabel }}
                            </option>
                        @endforeach
                    </optgroup>
                @else
                    <option 
                        value="{{ $optionValue }}" 
                        {{ (string)$selectedValue === (string)$optionValue ? 'selected' : '' }}
                    >
                        {{ $optionLabel }}
                    </option>
                @endif
            @endforeach
        @endif
        
        {{ $slot }}
    </select>
    
    @if($hint && !$hasError)
        <p class="mt-1.5 text-xs text-dark-500">{{ $hint }}</p>
    @endif
    
    @error($name)
        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
    @enderror
</div>
