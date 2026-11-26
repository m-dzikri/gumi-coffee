@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-black uppercase tracking-wider text-[#020202] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
