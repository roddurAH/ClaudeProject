@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'help' => null, 'required' => false])
@php($id = 'f_'.str_replace(['[', ']', '.'], '_', $name))
@php($current = (string) old($name, $value instanceof \BackedEnum ? $value->value : $value))
<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <select id="{{ $id }}" name="{{ $name }}"
            {{ $attributes->merge(['class' => 'form-select'.($errors->has($name) ? ' is-invalid' : '')]) }}
            @if($required) required @endif>
        @if($placeholder !== null)<option value="">{{ $placeholder }}</option>@endif
        @foreach($options as $key => $text)
            <option value="{{ $key }}" @selected($current === (string) $key)>{{ $text }}</option>
        @endforeach
    </select>
    @if($help)<div class="form-text">{{ $help }}</div>@endif
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
