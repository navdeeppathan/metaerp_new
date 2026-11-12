@extends('layouts.app')

@section('title', 'Home - MetaERP')

@section('content')
<div class="flex flex-1 items-center justify-center min-h-[calc(100vh-140px)] py-12 sm:py-24 select-none">
    <div class="flex flex-col items-center justify-center opacity-10 hover:opacity-20 transition-opacity">
        <img
            src="{{ asset('assets/images/logo/meta-new.png') }}"
            alt="Meta ERP"
            style="max-width: 640px; width: 100%;"
            class="rounded-xl shadow-none"
        />
        <h1 class="py-4 text-3xl font-semibold text-slate-800 dark:text-slate-100">ERP</h1>
        <h1 class="py-2 text-2xl font-semibold text-slate-800 dark:text-slate-100">Version: {{ $version }}</h1>
    </div>
</div>
@endsection
