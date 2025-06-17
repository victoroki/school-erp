@props([
    'name',
    'label',
    'value' => null,
    'required' => false,
    'maxlength' => null,
])

<div class="form-group col-sm-6">
    <label for="{{ $name }}">{{ $label }}</label>
    <input
        type="text"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        class="form-control datepicker"
        autocomplete="off"
    />
</div>
