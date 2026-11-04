@extends('layouts.app')

@section('title', $template->name . ' - Template Detail')

@section('content')
<div class="space-y-6" x-data="{ htmlCode: @js($template->html), isSaved: false }">
    <!-- Top Header: Back Link, Title, Subtitle, Save Button -->
    <div class="flex items-start justify-between">
        <div>
            <a href="{{ route('templates.index') }}" class="text-xs font-semibold text-slate-700 hover:text-indigo-600 flex items-center gap-1 transition select-none">
                <span>←</span>
                <span>Template</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900 mt-2">
                {{ $template->name }} ({{ $template->language?->name ?? 'English (United Kingdom)' }})
            </h1>
            <div class="text-xs text-slate-500 mt-0.5">Template Detail</div>
        </div>

        <div>
            <button type="submit" 
                    form="templateForm" 
                    class="px-6 py-2 rounded-full text-xs font-medium text-slate-700 bg-slate-200 hover:bg-indigo-600 hover:text-white transition shadow-2xs cursor-pointer">
                Save
            </button>
        </div>
    </div>

    <!-- Navigation Tabs Matching Screenshot -->
    <div class="border-b border-slate-200">
        <div class="border-b-2 border-indigo-600 text-indigo-600 font-semibold text-xs pb-2.5 inline-block">
            Template
        </div>
    </div>

    <!-- Split View: HTML Code Editor (Left) & Live Preview (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Left: HTML Code Editor -->
        <form id="templateForm" method="POST" action="{{ route('templates.update', $template->id) }}" class="space-y-2">
            @csrf
            @method('PUT')

            <div class="relative border border-slate-300 rounded-md p-3.5 pt-4 bg-white shadow-2xs">
                <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                    HTML Code
                </label>
                <textarea name="html" 
                          x-model="htmlCode" 
                          rows="28" 
                          class="w-full h-[70vh] font-mono text-[11px] leading-relaxed text-slate-800 border-0 p-1 focus:ring-0 resize-none outline-none custom-scrollbar" 
                          spellcheck="false">{{ $template->html }}</textarea>
            </div>
        </form>

        <!-- Right: Live Preview -->
        <div class="bg-white border border-slate-300 rounded-md p-4 h-[75vh] flex flex-col shadow-2xs overflow-hidden">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 pb-2 mb-2 border-b border-slate-100 flex items-center justify-between select-none">
                <span>Live Preview</span>
                <span class="text-[10px] text-slate-400">Updates in real-time</span>
            </div>
            <div class="flex-1 w-full h-full overflow-auto bg-white rounded">
                <iframe class="w-full h-full border-0" 
                        :srcdoc="htmlCode" 
                        sandbox="allow-same-origin"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
