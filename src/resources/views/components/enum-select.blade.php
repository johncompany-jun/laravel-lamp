@props(['enum', 'selected' => null, 'name', 'id' => null, 'required' => false, 'disabled' => false])

<x-select-input
    :name="$name"
    :id="$id ?? $name"
    :required="$required"
    :disabled="$disabled"
    {{ $attributes->except(['disabled']) }}
>
    @foreach($enum::cases() as $case)
        <option value="{{ $case->value }}" {{ $selected == $case->value ? 'selected' : '' }}>
            {{ method_exists($case, 'translatedLabel') ? $case->translatedLabel() : $case->label() }}
        </option>
    @endforeach
</x-select-input>
