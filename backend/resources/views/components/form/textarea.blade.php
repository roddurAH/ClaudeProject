@props(['name', 'label', 'value' => null, 'rows' => 4, 'help' => null, 'required' => false])
@php($id = 'f_'.str_replace(['[', ']', '.'], '_', $name))
<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
              {{ $attributes->merge(['class' => 'form-control'.($errors->has($name) ? ' is-invalid' : '')]) }}
              @if($required) required @endif>{{ old($name, $value) }}</textarea>
    @if($help)<div class="form-text">{{ $help }}</div>@endif
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
