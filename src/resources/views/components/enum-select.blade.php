@props(['enum', 'selected' => null, 'name', 'id' => null, 'required' => false])

<x-select-input
    :name="$name"
    :id="$id ?? $name"
    :required="$required"
    {{ $attributes }}
>
    @foreach($enum::cases() as $case)
        <option value="{{ $case->value }}" {{ $selected == $case->value ? 'selected' : '' }}>
            {{ method_exists($case, 'translatedLabel') ? $case->translatedLabel() : $case->label() }}
        </option>
    @endforeach
</x-select-input>
