@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-xs font-bold text-[#b22e3b] space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
