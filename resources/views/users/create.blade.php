@extends('layouts.app')

@section('title', (isset($user) ? $user->name : 'New User') . ' - MetaERP')

@section('content')
<div class="max-w-7xl mx-auto space-y-4" x-data="{
    activeTab: 'user-info',
    name: '{{ old('name', $user->name ?? '') }}',
    active: {{ old('active', isset($user) ? ($user->active ? 'true' : 'false') : 'true') }},
    displayName: '{{ old('displayName', $user->displayName ?? '') }}',
    occupation: '{{ old('occupation', $user->occupation ?? '') }}',
    emailPrefix: '{{ old('email_prefix', isset($user) ? explode('@', $user->username)[0] : '') }}',
    domain: '{{ old('domain', isset($user) && str_contains($user->username, '@') ? ('@' . explode('@', $user->username)[1]) : '@metaenterprices.co.uk') }}',
    enable2fa: {{ old('enable2fa', isset($user) && $user->enable2fa ? 'true' : 'false') }},
    password: '',
    passwordConfirmation: '',
    selectedCompanyId: '',
    selectedCompanyName: '',
    selectedAccessGroupId: '',
    selectedAccessGroupName: '',
    permissions: [
        @if(isset($user) && $user->permissions)
            @foreach($user->permissions as $p)
                {
                    company_id: '{{ $p->company_id }}',
                    company_name: '{{ $p->company?->name ?? 'Company #' . $p->company_id }}',
                    accessgroup_id: '{{ $p->accessgroup_id }}',
                    accessgroup_name: '{{ $p->accessGroup?->name ?? 'Access Group #' . $p->accessgroup_id }}'
                },
            @endforeach
        @endif
    ],
    addPermission() {
        if (!this.selectedCompanyId || !this.selectedAccessGroupId) return;
        this.permissions.push({
            company_id: this.selectedCompanyId,
            company_name: this.selectedCompanyName,
            accessgroup_id: this.selectedAccessGroupId,
            accessgroup_name: this.selectedAccessGroupName
        });
        this.selectedCompanyId = '';
        this.selectedCompanyName = '';
        this.selectedAccessGroupId = '';
        this.selectedAccessGroupName = '';
    },
    removePermission(index) {
        this.permissions.splice(index, 1);
    },
    get isFormValid() {
        return this.name.trim().length > 0 &&
               this.displayName.trim().length > 0 &&
               this.occupation.trim().length > 0 &&
               (this.emailPrefix.trim().length > 0 || '{{ isset($user) ? 'yes' : 'no' }}' === 'yes');
    }
}">

    <form action="{{ isset($user) ? route('users.update', $user->id) : route('users.store') }}" method="POST" id="userForm">
        @csrf
        @if(isset($user))
            @method('PUT')
        @endif
        <input type="hidden" name="permissions_json" :value="JSON.stringify(permissions)">

        <!-- Top Header: Back Link, Title, Subtitle, Save Button -->
        <div class="flex items-center justify-between pb-2">
            <div>
                <a href="{{ route('users.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 hover:text-slate-900 transition-colors mb-2">
                    <span class="material-icons text-base">arrow_back</span>
                    <span>Users</span>
                </a>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight leading-tight">
                    {{ isset($user) ? $user->name : 'New User' }}
                </h1>
                <p class="text-xs text-slate-500 font-normal mt-0.5">
                    User Detail
                </p>
            </div>

            <div>
                <button
                    type="submit"
                    class="rounded-full px-6 py-2 text-xs font-semibold text-white bg-[#4f46e5] hover:bg-[#4338ca] shadow-xs transition-all cursor-pointer"
                >
                    Save
                </button>
            </div>
        </div>

        @if (isset($errors) && $errors->any())
            <div class="p-3.5 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs flex items-start gap-2">
                <span class="material-icons-outlined text-base text-red-500 mt-0.5">error_outline</span>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Card Container with Tabs & Content -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 mt-4">
            <!-- Tabs Row -->
            <div class="border-b border-slate-200 flex items-center gap-6 text-xs font-medium pb-px">
                <button
                    type="button"
                    @click="activeTab = 'user-info'"
                    :class="activeTab === 'user-info' ? 'text-blue-600 font-semibold border-b-2 border-blue-600 pb-2.5 -mb-[1px]' : 'text-slate-500 hover:text-slate-800 pb-2.5 transition-colors'"
                    class="focus:outline-none"
                >
                    User Info
                </button>
                <button
                    type="button"
                    @click="activeTab = 'permissions'"
                    :class="activeTab === 'permissions' ? 'text-blue-600 font-semibold border-b-2 border-blue-600 pb-2.5 -mb-[1px]' : 'text-slate-500 hover:text-slate-800 pb-2.5 transition-colors'"
                    class="focus:outline-none"
                >
                    Permissions
                </button>
            </div>

            <!-- Tab 1: User Info -->
            <div x-show="activeTab === 'user-info'" class="pt-6 space-y-6 max-w-4xl">
                <!-- Avatar circle with Camera and Trash overlay icons -->
                <div class="flex items-center">
                    <div class="w-24 h-24 rounded-full bg-slate-500/90 relative flex items-center justify-center overflow-hidden shadow-inner select-none group">
                        <span class="text-white text-3xl font-bold opacity-30 tracking-wider">ame</span>
                        <div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-3">
                            <label class="cursor-pointer text-white hover:text-slate-200 transition-colors" title="Upload Photo">
                                <span class="material-icons-outlined text-xl leading-none">photo_camera</span>
                                <input type="file" name="photo" class="hidden">
                            </label>
                            <button type="button" class="text-white hover:text-red-300 transition-colors" title="Remove Photo">
                                <span class="material-icons-outlined text-xl leading-none">delete</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Field 1: Name * (Outlined Notch Style) -->
                <div class="relative">
                    <input
                        type="text"
                        name="name"
                        id="name"
                        x-model="name"
                        required
                        class="peer w-full px-3.5 py-3 rounded border border-indigo-600 bg-white text-slate-800 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-600 transition-colors"
                        placeholder=" "
                    />
                    <label
                        for="name"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-indigo-600 pointer-events-none transition-all"
                    >
                        Name <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 2: Active Checkbox -->
                <div class="flex items-center gap-2 pt-1">
                    <input
                        type="checkbox"
                        name="active"
                        id="active"
                        x-model="active"
                        value="1"
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                    />
                    <label for="active" class="text-sm font-normal text-slate-800 cursor-pointer select-none">
                        Active
                    </label>
                </div>

                <!-- Field 3: Display Name * (Outlined Notch Style) -->
                <div class="relative">
                    <input
                        type="text"
                        name="displayName"
                        id="displayName"
                        x-model="displayName"
                        required
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 bg-white text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-colors"
                        placeholder=" "
                    />
                    <label
                        for="displayName"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Display Name <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 4: Occupation * (Outlined Notch Style) -->
                <div class="relative">
                    <input
                        type="text"
                        name="occupation"
                        id="occupation"
                        x-model="occupation"
                        required
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 bg-white text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-colors"
                        placeholder=" "
                    />
                    <label
                        for="occupation"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Occupation <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 5: e-mail * & Domain Selector (2 Columns) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- e-mail Prefix -->
                    <div class="relative">
                        <input
                            type="text"
                            name="email_prefix"
                            id="email_prefix"
                            x-model="emailPrefix"
                            required
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 bg-white text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-colors"
                            placeholder=" "
                        />
                        <label
                            for="email_prefix"
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            e-mail <span class="text-slate-400">*</span>
                        </label>
                    </div>

                    <!-- Domain Dropdown -->
                    <div class="relative">
                        <select
                            name="domain"
                            id="domain"
                            x-model="domain"
                            class="w-full px-3.5 py-3 rounded border border-slate-300 bg-white text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-colors cursor-pointer"
                        >
                            <option value="@metaenterprices.co.uk">@metaenterprices.co.uk</option>
                            <option value="@smetatrading.com">@smetatrading.com</option>
                            <option value="@metazn.co.uk">@metazn.co.uk</option>
                            <option value="@dometal.pl">@dometal.pl</option>
                            <option value="@sofinish.co.uk">@sofinish.co.uk</option>
                            <option value="@metaenterprices.rs">@metaenterprices.rs</option>
                            <option value="@metaenterprices.com.tr">@metaenterprices.com.tr</option>
                        </select>
                    </div>
                </div>

                <!-- Field 6: Enable 2 Factor Authentication Checkbox -->
                <div class="flex items-center gap-2 pt-1">
                    <input
                        type="checkbox"
                        name="enable2fa"
                        id="enable2fa"
                        x-model="enable2fa"
                        value="1"
                        class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                    />
                    <label for="enable2fa" class="text-sm font-normal text-slate-800 cursor-pointer select-none">
                        Enable 2 Factor Authentication
                    </label>
                </div>

                <!-- Field 7: Password * (Outlined Notch Style with Light-Blue Tint) -->
                <div class="relative">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        x-model="password"
                        {{ isset($user) ? '' : 'required' }}
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 bg-[#edf5ff] text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-colors"
                        placeholder=" "
                    />
                    <label
                        for="password"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Password <span class="text-slate-400">*</span>
                    </label>
                </div>

                <!-- Field 8: Password Confirmation * (Outlined Notch Style) -->
                <div class="relative">
                    <input
                        type="password"
                        name="password_confirmation"
                        id="passwordConfirmation"
                        x-model="passwordConfirmation"
                        {{ isset($user) ? '' : 'required' }}
                        class="peer w-full px-3.5 py-3 rounded border border-slate-300 bg-white text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-colors"
                        placeholder=" "
                    />
                    <label
                        for="passwordConfirmation"
                        class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                    >
                        Password Confirmation <span class="text-slate-400">*</span>
                    </label>
                </div>
            </div>

            <!-- Tab 2: Permissions -->
            <div x-show="activeTab === 'permissions'" class="pt-6 space-y-6 max-w-4xl" style="display: none;">
                <!-- Company & Access Group Selectors -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Company Dropdown -->
                    <div class="relative">
                        <select
                            id="company_select"
                            x-model="selectedCompanyId"
                            @change="selectedCompanyName = $event.target.options[$event.target.selectedIndex].text"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 bg-white text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-colors cursor-pointer"
                        >
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        <label
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-indigo-600 pointer-events-none transition-all"
                        >
                            Company
                        </label>
                    </div>

                    <!-- Access Group Dropdown -->
                    <div class="relative">
                        <select
                            id="access_group_select"
                            x-model="selectedAccessGroupId"
                            @change="selectedAccessGroupName = $event.target.options[$event.target.selectedIndex].text"
                            class="peer w-full px-3.5 py-3 rounded border border-slate-300 bg-white text-slate-800 text-sm focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition-colors cursor-pointer"
                        >
                            <option value="">Select Access Group</option>
                            @foreach($accessGroups as $ag)
                                <option value="{{ $ag->id }}">{{ $ag->name }}</option>
                            @endforeach
                        </select>
                        <label
                            class="absolute left-2.5 -top-2.5 bg-white px-1 text-xs font-normal text-slate-500 peer-focus:text-indigo-600 pointer-events-none transition-all"
                        >
                            Access Group
                        </label>
                    </div>
                </div>

                <!-- Add Button -->
                <div>
                    <button
                        type="button"
                        @click="addPermission()"
                        :disabled="!selectedCompanyId || !selectedAccessGroupId"
                        :class="selectedCompanyId && selectedAccessGroupId ? 'bg-[#4f46e5] text-white hover:bg-[#4338ca] cursor-pointer' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                        class="rounded-full px-6 py-1.5 text-xs font-semibold transition-colors duration-150"
                    >
                        Add
                    </button>
                </div>

                <!-- Assigned Permissions Table -->
                <div class="border-t border-slate-200/80 pt-4">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-bold text-slate-900">
                                <th class="py-3 px-4 w-1/2">Company</th>
                                <th class="py-3 px-4 w-1/2">Access Group</th>
                                <th class="py-3 px-4 w-16 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                            <template x-for="(perm, index) in permissions" :key="index">
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="py-3 px-4 text-slate-800 font-medium" x-text="perm.company_name"></td>
                                    <td class="py-3 px-4 text-slate-700" x-text="perm.accessgroup_name"></td>
                                    <td class="py-3 px-4 text-right">
                                        <button
                                            type="button"
                                            @click="removePermission(index)"
                                            class="text-slate-400 hover:text-red-500 p-1 transition-colors"
                                            title="Delete"
                                        >
                                            <span class="material-icons-outlined text-base">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="permissions.length === 0">
                                <td colspan="3" class="py-8 text-center text-slate-400 text-xs">
                                    No permissions assigned yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
