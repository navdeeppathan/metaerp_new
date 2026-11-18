@props(['text' => 'Loading', 'class' => ''])

<div {{ $attributes->merge(['class' => 'flex flex-1 flex-col items-center justify-center p-6 ' . $class]) }}>
    @if($text)
        <div class="text-base sm:text-lg font-medium text-slate-500 select-none -mb-1">
            {{ $text }}
        </div>
    @endif
    <div class="fuse-spinner mt-4">
        <div class="bounce1"></div>
        <div class="bounce2"></div>
        <div class="bounce3"></div>
    </div>
</div>
