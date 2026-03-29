@extends('layouts.app')

@section('title', 'System Roles')
@section('page_title', 'System Roles')
@section('page_subtitle', 'Manage access levels and permissions')

@section('content')
    <div x-data="{ 
                            roleModalOpen: false, 
                            userModalOpen: false,
                            editMode: false, 

                            role: { modules: [] },
                            user: { modules: [] },

                            modules: {{ json_encode($modules) }},
                            roles: {{ json_encode($roles) }},

                            // Generate list of roles for select dropdown
                            get roleOptions() {
                                return this.roles;
                            }
                        }">

        <div class="grid grid-cols-1 gap-8 overflow-hidden">

            <!-- SECTION 1: ROLES MANAGEMENT -->
            <div
                class="flex flex-col bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                <div
                    class="p-4 border-b border-gray-100 dark:border-gray-700 flex justify-end items-center bg-gray-50 dark:bg-gray-800">
                    <button @click="roleModalOpen = true; editMode = false; role = { modules: [] }"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                        <i class="fas fa-plus"></i> <span>Add Role</span>
                    </button>
                </div>

                <div class="flex-1">
                    <table class="w-full text-left">
                        <thead
                            class="sticky top-0 bg-white dark:bg-gray-800 z-10 text-sm font-bold text-gray-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 border-b border-gray-100 dark:border-gray-700">Role Name</th>
                                <th class="px-6 py-3 border-b border-gray-100 dark:border-gray-700">Access</th>
                                <th class="px-6 py-3 border-b border-gray-100 dark:border-gray-700 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($roles as $r)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center font-bold text-sm">
                                                {{ substr($r->name, 0, 1) }}
                                            </div>
                                            <span
                                                class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ $r->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @php
                                                $roleModules = $r->modules ?? [];
                                                $orderedModules = array_filter(array_keys($modules), fn($m) => in_array($m, $roleModules));
                                            @endphp
                                            @forelse($orderedModules as $mod)
                                                @php
                                                    $rawLabel = $modules[$mod] ?? ucfirst(str_replace('_', ' ', $mod));
                                                    $isSub = str_starts_with($rawLabel, '—');
                                                    $label = $isSub ? trim(substr($rawLabel, 3)) : $rawLabel;
                                                @endphp
                                                <span
                                                    class="px-2 py-0.5 rounded border {{ $isSub ? 'bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 text-xs font-medium px-2.5' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800 text-xs font-bold' }}">
                                                    {{ $label }}
                                                </span>
                                            @empty
                                                <span class="text-sm text-gray-400 italic">No modules</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                @click="roleModalOpen = true; editMode = true; role = { id: {{ $r->id }}, name: '{{ $r->name }}', modules: {{ json_encode($r->modules) }} }"
                                                class="text-blue-600 hover:text-blue-800 p-1 rounded hover:bg-blue-50 transition-colors">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if($r->name !== 'Admin')
                                                <form action="{{ route('roles.destroy', $r->id) }}" method="POST" class="inline"
                                                    onsubmit="event.preventDefault(); showConfirm('Delete Role', 'Are you sure you want to delete this role?', 'bg-red-600 hover:bg-red-700', () => this.submit(), 'Delete')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50 transition-colors">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ROLE MODAL -->
        <div x-show="roleModalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="roleModalOpen = false"></div>
            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg p-6 relative animate-fade-in-up">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-bold text-xl text-gray-800 dark:text-white"
                            x-text="editMode ? 'Edit Role' : 'Create New Role'"></h3>
                        <button @click="roleModalOpen = false" class="text-gray-400 hover:text-gray-600"><i
                                class="fas fa-times"></i></button>
                    </div>

                    <form :action="editMode ? '{{ url('roles') }}/' + role.id : '{{ route('roles.store') }}'" method="POST"
                        class="space-y-6">
                        @csrf
                        <input type="hidden" name="_method" :value="editMode ? 'PUT' : 'POST'">

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Role Name</label>
                            <input type="text" name="name" x-model="role.name" required
                                autocomplete="off"
                                placeholder="e.g., Parish Coordinator"
                                class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Module
                                Access</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start max-h-[50vh] overflow-y-auto custom-scrollbar p-1">
                                @php
                                    $groupedModules = [];
                                    $currentParent = null;
                                    
                                    foreach($modules as $key => $label) {
                                        if(str_starts_with($label, '—')) {
                                            if($currentParent) {
                                                $groupedModules[$currentParent]['children'][$key] = trim(substr($label, 3));
                                            } else {
                                                $groupedModules[$key] = ['label' => trim(substr($label, 3)), 'children' => []];
                                            }
                                        } else {
                                            $currentParent = $key;
                                            $groupedModules[$key] = ['label' => $label, 'children' => []];
                                        }
                                    }
                                @endphp

                                @foreach($groupedModules as $parentKey => $parentData)
                                    <div x-data="{ open: false }" class="flex flex-col border border-gray-100 dark:border-gray-800 rounded-xl bg-white dark:bg-gray-800/20 overflow-hidden hover:border-blue-200 dark:hover:border-blue-900/50 transition-all shadow-sm">
                                        <!-- Parent Checkbox -->
                                        <div class="flex items-center justify-between p-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ count($parentData['children']) > 0 ? 'cursor-pointer' : '' }}" 
                                             @if(count($parentData['children']) > 0) @click="open = !open" @endif>
                                            <label class="flex items-center space-x-3 cursor-pointer" @click.stop>
                                                <input type="checkbox" name="modules[]" value="{{ $parentKey }}" x-model="role.modules"
                                                    class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 shadow-sm transition-colors cursor-pointer">
                                                <span class="text-sm font-bold text-gray-800 dark:text-gray-200 select-none cursor-pointer">
                                                    {{ $parentData['label'] }}
                                                </span>
                                            </label>
                                            
                                            <!-- Dropdown Icon -->
                                            @if(count($parentData['children']) > 0)
                                                <div class="text-gray-400 px-1">
                                                    <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Children Checkboxes -->
                                        @if(count($parentData['children']) > 0)
                                            <div x-show="open" 
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                                 x-transition:enter-end="opacity-100 translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 translate-y-0"
                                                 x-transition:leave-end="opacity-0 -translate-y-2"
                                                 style="display: none;"
                                                 class="flex flex-col bg-gray-50/80 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-800/80 p-3.5 pl-5 space-y-3">
                                                @foreach($parentData['children'] as $childKey => $childLabel)
                                                    <label class="flex items-center space-x-3 cursor-pointer group">
                                                        <input type="checkbox" name="modules[]" value="{{ $childKey }}" x-model="role.modules"
                                                            class="w-4 h-4 rounded border-gray-300 text-blue-500 focus:ring-blue-500 shadow-sm transition-colors cursor-pointer">
                                                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors select-none cursor-pointer">
                                                            {{ $childLabel }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button type="button" @click="roleModalOpen = false"
                                class="px-5 py-2.5 rounded-xl text-gray-500 hover:bg-gray-100 font-bold transition-colors">Cancel</button>
                            <button type="submit"
                                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-500/30 transition-all">Save
                                Role</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection