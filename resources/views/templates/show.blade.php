@extends('layouts.app')

@section('title', $template->name . ' - Template Detail')

@section('content')
@php
    $initialHtml = $template->html;
@endphp
<div class="space-y-6" x-data="{ 
    htmlCode: @js($initialHtml), 
    originalCode: @js($initialHtml),
    isSaved: false,
    get isModified() {
        return this.htmlCode !== this.originalCode;
    }
}">
    <!-- Top Header: Back Link, Title, Subtitle, Save Button (Matching Screenshot 2) -->
    <div class="flex items-start justify-between">
        <div>
            <a href="{{ route('templates.index') }}" class="inline-flex items-center gap-1.5 text-slate-700 hover:text-slate-900 text-xs font-semibold transition-colors select-none">
                <span class="material-icons-outlined text-base">arrow_back</span>
                <span>Template</span>
            </a>
            <h1 class="text-2xl sm:text-[26px] font-bold text-slate-900 mt-2 tracking-tight">
                {{ $template->name }} ({{ $template->language?->name ?? 'English (United Kingdom)' }})
            </h1>
            <div class="text-xs text-slate-500 mt-0.5 font-normal">Template Detail</div>
        </div>

        <div>
            <button type="submit" 
                    form="templateForm" 
                    :disabled="!isModified"
                    :class="isModified ? 'bg-[#7c3aed] text-white hover:bg-[#6d28d9] shadow-xs cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                    class="px-7 py-2 rounded-full text-xs font-semibold transition select-none">
                Save
            </button>
        </div>
    </div>

    <!-- Navigation Tabs Matching Screenshot 2 -->
    <div class="border-b border-slate-200">
        <div class="border-b-2 border-indigo-600 text-indigo-600 font-semibold text-xs pb-2.5 inline-block select-none">
            Template
        </div>
    </div>

    <!-- Split View: HTML Code Editor (Left) & Live Preview (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Left: HTML Code Editor -->
        <form id="templateForm" method="POST" action="{{ route('templates.update', $template->id) }}" class="space-y-2">
            @csrf
            @method('PUT')

            <div class="relative border border-slate-300 rounded-md p-4 pt-5 bg-white shadow-2xs focus-within:border-indigo-500">
                <label class="absolute -top-2 left-3 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none select-none">
                    HTML Code
                </label>
                <textarea name="html" 
                          x-model="htmlCode" 
                          rows="28" 
                          class="w-full h-[72vh] font-mono text-[11px] leading-relaxed text-slate-800 border-0 p-0 focus:ring-0 resize-none outline-none custom-scrollbar" 
                          spellcheck="false">{{ $initialHtml }}</textarea>
            </div>
        </form>

        <!-- Right: Live Preview -->
        <div class="bg-white border border-slate-300 rounded-md p-4 h-[77vh] flex flex-col shadow-2xs overflow-hidden">
            <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 pb-2 mb-2 border-b border-slate-100 flex items-center justify-between select-none">
                <span>LIVE PREVIEW</span>
                <span class="text-[10px] text-slate-400 tracking-normal">UPDATES IN REAL-TIME</span>
            </div>
            <div class="flex-1 w-full h-full overflow-auto bg-white rounded">
                <iframe class="w-full h-full border-0" 
                        :srcdoc="htmlCode" 
                        sandbox="allow-same-origin allow-scripts"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
