@props(['name', 'label', 'checked' => false, 'help' => null])
@php($id = 'f_'.str_replace(['[', ']', '.'], '_', $name))
<div class="form-check form-switch mb-3">
    <input type="checkbox" class="form-check-input" id="{{ $id }}" name="{{ $name }}" value="1"
           @checked(old($name, $checked))>
    <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
    @if($help)<div class="form-text">{{ $help }}</div>@endif
</div>
