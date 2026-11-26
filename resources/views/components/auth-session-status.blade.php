@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'p-3 bg-[#e9bfd2] border-2 border-[#020202] font-black text-xs uppercase tracking-wider text-[#020202]']) }}>
        {{ $status }}
    </div>
@endif
