@extends('layouts.app')

@section('title', isset($group) ? $group->name : 'New AccessGroup')

@section('content')
<div class="space-y-6" x-data="accessGroupForm()">
    <!-- Header with Breadcrumb, Title & Save Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a href="{{ route('accessgroups.index') }}" class="inline-flex items-center gap-1 text-xs text-slate-600 hover:text-indigo-600 font-medium transition mb-2">
                <span class="material-icons text-sm">arrow_back</span>
                <span>AccessGroups</span>
            </a>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                {{ isset($group) ? $group->name : 'New AccessGroup' }}
            </h1>
            <p class="text-xs text-slate-500 font-normal mt-0.5">AccessGroup Detail</p>
        </div>

        <div class="flex items-center gap-3">
            @if(isset($group))
            <form action="{{ route('accessgroups.destroy', $group->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this Access Group?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-[#4f46e5] hover:bg-rose-600 text-white px-5 py-1.5 rounded-full font-semibold text-xs shadow-2xs transition flex items-center gap-1.5">
                    <span class="material-icons text-sm">delete</span>
                    <span>Remove</span>
                </button>
            </form>
            @endif

            <button type="button" 
                    @click="submitForm()"
                    :disabled="!name.trim()"
                    :class="name.trim() ? 'bg-[#4f46e5] hover:bg-[#4338ca] text-white cursor-pointer shadow-2xs' : 'bg-[#e2e8f0] text-slate-400 cursor-not-allowed'"
                    class="px-6 py-1.5 rounded-full font-semibold text-xs transition">
                Save
            </button>
        </div>
    </div>

    <!-- Tabs: Access Group -->
    <div class="border-b border-slate-200">
        <div class="flex gap-6 text-xs font-semibold">
            <button type="button" class="pb-3 border-b-2 border-[#4f46e5] text-[#4f46e5] transition">
                Access Group
            </button>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-100 p-6 md:p-8 space-y-6">
        <form id="accessGroupFormElement" action="{{ isset($group) ? route('accessgroups.update', $group->id) : route('accessgroups.store') }}" method="POST" class="space-y-6">
            @csrf
            @if(isset($group))
                @method('PUT')
            @endif

            <!-- Hidden input for JSON rules -->
            <input type="hidden" name="rules" :value="JSON.stringify(rules)">

            <!-- Row 1: Group Name (Material Notch Field) -->
            <div class="relative border border-slate-300 rounded-md px-3 pt-2.5 pb-2 bg-white focus-within:border-indigo-600 focus-within:ring-1 focus-within:ring-indigo-600 transition">
                <label class="absolute -top-2 left-2 bg-white px-1 text-[11px] text-slate-500 font-medium leading-none">
                    Name *
                </label>
                <input type="text" 
                       name="name" 
                       x-model="name"
                       required 
                       placeholder=""
                       class="w-full bg-transparent text-xs text-slate-900 focus:outline-none">
            </div>

            <!-- Row 2: Module & Access Type Selectors -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Module Dropdown -->
                <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white focus-within:border-indigo-600 transition">
                    <select x-model="selectedModule" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none cursor-pointer appearance-none pr-6">
                        <option value="" disabled selected>Select Module</option>
                        @foreach($modules as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">arrow_drop_down</span>
                </div>

                <!-- Access Type Dropdown -->
                <div class="relative border border-slate-300 rounded-md px-3 pt-2 pb-1.5 bg-white focus-within:border-indigo-600 transition">
                    <select x-model="selectedAccessType" class="w-full bg-transparent text-xs text-slate-800 focus:outline-none cursor-pointer appearance-none pr-6">
                        <option value="" disabled selected>Select Access Type</option>
                        @foreach($accessTypes as $at)
                        <option value="{{ $at }}">{{ $at }}</option>
                        @endforeach
                    </select>
                    <span class="material-icons absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">arrow_drop_down</span>
                </div>
            </div>

            <!-- Row 3: Add Pill Button -->
            <div>
                <button type="button" 
                        @click="addRule()"
                        :disabled="!selectedModule || !selectedAccessType"
                        :class="selectedModule && selectedAccessType ? 'bg-[#4f46e5] text-white hover:bg-[#4338ca] cursor-pointer shadow-2xs' : 'bg-[#e2e8f0] text-slate-400 cursor-not-allowed'"
                        class="px-5 py-1.5 rounded-full font-semibold text-xs transition">
                    Add
                </button>
            </div>

            <!-- Row 4: Permissions Table -->
            <div class="pt-2">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-900 font-bold text-[13px]">
                            <th class="py-2.5 px-2">Module</th>
                            <th class="py-2.5 px-2">Access Type</th>
                            <th class="py-2.5 px-2 w-16 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <template x-for="(rule, idx) in rules" :key="idx">
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-2 font-medium text-slate-800" x-text="rule.module"></td>
                                <td class="py-3 px-2 text-slate-600" x-text="rule.accessType"></td>
                                <td class="py-3 px-2 text-right">
                                    <button type="button" @click="removeRule(idx)" class="text-slate-400 hover:text-rose-600 p-1 rounded transition" title="Delete">
                                        <span class="material-icons text-sm">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="rules.length === 0">
                            <td colspan="3" class="py-6 text-center text-slate-400 text-xs italic">
                                No module access rules added yet. Select a module and access type above and click Add.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<script>
function accessGroupForm() {
    return {
        name: '{{ isset($group) ? $group->name : '' }}',
        selectedModule: '',
        selectedAccessType: '',
        rules: @json(isset($group) ? $group->accessList->map(fn($a) => ['module' => $a->module, 'accessType' => $a->access_type ?? $a->accessType]) : []),

        addRule() {
            if (!this.selectedModule || !this.selectedAccessType) return;

            // Check if already exists
            const existing = this.rules.find(r => r.module === this.selectedModule);
            if (existing) {
                existing.accessType = this.selectedAccessType;
            } else {
                this.rules.push({
                    module: this.selectedModule,
                    accessType: this.selectedAccessType
                });
            }

            this.selectedModule = '';
            this.selectedAccessType = '';
        },

        removeRule(idx) {
            this.rules.splice(idx, 1);
        },

        submitForm() {
            if (!this.name.trim()) return;
            document.getElementById('accessGroupFormElement').submit();
        }
    };
}
</script>
@endsection
