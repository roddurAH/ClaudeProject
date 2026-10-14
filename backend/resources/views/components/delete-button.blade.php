@props(['action', 'confirm' => 'Delete this item? This cannot be undone.', 'label' => 'Delete'])
<form method="POST" action="{{ $action }}" class="d-inline" onsubmit="return confirm(@js($confirm))">
    @csrf
    @method('DELETE')
    <button type="submit" {{ $attributes->merge(['class' => 'btn btn-sm btn-outline-danger']) }}>
        <i class="bi bi-trash"></i> {{ $label }}
    </button>
</form>
