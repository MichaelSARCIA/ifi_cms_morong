@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page_title', 'Activity Logs')
@section('page_subtitle', 'System activity timeline')
@section('role_label', Auth::user()->role)

@section('content')
<div class="flex flex-col search-results-container relative" x-data="tableSearch()" @click="handlePagination">
    <!-- Removed blur loading state for instant feel -->

    {{-- Toolbar --}}
    <div class="flex flex-col gap-4 mb-4 shrink-0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl px-5 py-4 shadow-sm border border-gray-100 dark:border-gray-700
                        flex flex-col sm:flex-row justify-between items-center gap-3">

            {{-- Title --}}
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 dark:text-white text-base">Activity Logs</h3>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">Tracking system events and user actions</p>
                </div>
            </div>

            {{-- Controls --}}
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <form id="perPageForm" method="GET" action="{{ route('audit-trail') }}" class="flex items-center gap-2">
                    
                    <label for="per_page"
                        class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Show</label>
                    <div class="relative">
                        <select name="per_page" id="per_page" @change="submitSearch"
                            class="dropdown-btn w-20 px-3 py-1.5 h-10">
                            @foreach([5, 10, 15, 20] as $n)
                                <option value="{{ $n }}" {{ request('per_page', 15) == $n ? 'selected' : '' }}>{{ $n }}</option>
                            @endforeach
                        </select>
                    </div>
                    <span
                        class="text-sm font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider hidden sm:inline">Entries</span>
                </form>

                <a href="{{ route('audit-trail', array_merge(request()->query(), ['export' => 'true'])) }}"
                    class="text-sm px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700
                               text-white rounded-lg flex items-center gap-2 font-semibold transition-all shadow-md shadow-blue-500/30 whitespace-nowrap">
                    <i class="fas fa-file-export"></i> Export to Text
                </a>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col flex-1 min-h-0">
        
        <!-- SEARCH & FILTERS -->
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 shrink-0">
            <form action="{{ route('audit-trail') }}" method="GET" class="flex flex-wrap items-center gap-4 w-full search-form" @submit.prevent="submitSearch">
                
                <!-- Search Input -->
                <div class="relative max-w-xs w-full lg:w-auto">
                    <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search logs..."
                        @input.debounce.300ms="submitSearch"
                        class="w-full lg:w-64 pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm font-medium text-gray-700 dark:text-gray-300">
                </div>

                <div class="relative max-w-[200px] w-full lg:w-auto">
                    <select name="role" @change="submitSearch"
                        class="dropdown-btn w-full lg:w-auto">
                        <option value="">All Roles</option>
                        <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                        <option value="Priest" {{ request('role') == 'Priest' ? 'selected' : '' }}>Priest</option>
                        <option value="Treasurer" {{ request('role') == 'Treasurer' ? 'selected' : '' }}>Treasurer</option>
                        <option value="Parishioner" {{ request('role') == 'Parishioner' ? 'selected' : '' }}>Parishioner</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    @if(request()->anyFilled(['search', 'role']))
                        <a href="{{ route('audit-trail', array_filter(['per_page' => request('per_page')])) }}"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-sm font-medium transition-all px-2">
                            <i class="fas fa-times-circle mr-1"></i>Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($audit_logs->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div
                    class="w-16 h-16 bg-gray-50 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 animate-pulse">
                    <i class="fas fa-history text-gray-300 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-400">No activity logs found</h3>
                <p class="text-sm text-gray-400 mt-1 max-w-xs">System activities will appear here once users interact with the
                    system.</p>
            </div>
        @else

            {{-- Table with sticky heading using native HTML table --}}
            <div class="w-full overflow-x-auto flex-1">
                <table class="w-full min-w-[800px] text-sm text-left">

                    {{-- Sticky thead --}}
                    <thead
                        class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-700/80 border-b-2 border-gray-100 dark:border-gray-700">
                        <tr class="text-sm font-bold text-gray-400 dark:text-gray-400 uppercase tracking-wider">
                            <th class="px-5 py-3 text-center" style="width:44px">#</th>
                            <th class="px-5 py-3" style="width:160px">Date &amp; Time</th>
                            <th class="px-5 py-3" style="width:190px">User</th>
                            <th class="px-5 py-3" style="width:120px">Role</th>
                            <th class="px-5 py-3" style="width:105px">Action</th>
                            <th class="px-5 py-3">Details</th>
                            <th class="px-5 py-3 text-right" style="width:145px">IP Address</th>
                        </tr>
                    </thead>

                    {{-- Scrollable tbody — NOTE: only tbody scrolls via the outer overflow-x-auto wrapper --}}
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach($audit_logs as $index => $log)
                            @php
                                $actionLower = strtolower($log->action);
                                if (str_contains($actionLower, 'add') || str_contains($actionLower, 'create')) {
                                    $badgeBg = 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:border-blue-700/50';
                                    $dot = 'bg-blue-500';
                                } elseif (str_contains($actionLower, 'update') || str_contains($actionLower, 'edit')) {
                                    $badgeBg = 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-700/50';
                                    $dot = 'bg-amber-500';
                                } elseif (str_contains($actionLower, 'delete') || str_contains($actionLower, 'archive')) {
                                    $badgeBg = 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700/50';
                                    $dot = 'bg-red-500';
                                } elseif (str_contains($actionLower, 'login')) {
                                    $badgeBg = 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700/50';
                                    $dot = 'bg-emerald-500';
                                } elseif (str_contains($actionLower, 'logout')) {
                                    $badgeBg = 'bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-900/40 dark:text-orange-300 dark:border-orange-700/50';
                                    $dot = 'bg-orange-500';
                                } elseif (str_contains($actionLower, 'restore')) {
                                    $badgeBg = 'bg-teal-50 text-teal-700 border border-teal-200 dark:bg-teal-900/40 dark:text-teal-300 dark:border-teal-700/50';
                                    $dot = 'bg-teal-500';
                                } else {
                                    $badgeBg = 'bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600';
                                    $dot = 'bg-gray-400';
                                }
                                $rowNum = ($audit_logs->currentPage() - 1) * $audit_logs->perPage() + $index + 1;

                                // Role colors — matches users.blade.php exactly
                                $roleColor = match ($log->user_role ?? '') {
                                    'Admin' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                    'Priest' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300',
                                    'Treasurer' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'Secretary' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                                    default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                };
                                // Avatar bg/text to match role
                                $avatarColor = match ($log->user_role ?? '') {
                                    'Admin' => 'bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300',
                                    'Priest' => 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300',
                                    'Treasurer' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300',
                                    'Secretary' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
                                    default => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300',
                                };
                            @endphp
                                        <tr class="hover:bg-blue-50/40 dark:hover:bg-gray-700/30 transition-colors">

                                            {{-- # --}}
                                            <td class="px-5 py-3.5 text-center">
                                                <span class="text-sm font-bold text-gray-300 dark:text-gray-600">{{ $rowNum }}</span>
                                            </td>

                                            {{-- Date & Time --}}
                                            <td class="px-5 py-3.5 whitespace-nowrap">
                                                <div class="font-semibold text-gray-700 dark:text-gray-200">
                                                    {{ \Carbon\Carbon::parse($log->created_at)->format('F d, Y') }}
                                                </div>
                                                <div class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">
                                                    {{ \Carbon\Carbon::parse($log->created_at)->format('h:i:s A') }}
                                                </div>
                                            </td>

                                            {{-- User --}}
                                            <td class="px-5 py-3.5 whitespace-nowrap">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-none {{ $avatarColor }}">
                                                        <span class="text-sm font-bold">
                                                            @if($log->user_name)
                                                                {{ strtoupper(substr($log->user_name, 0, 1)) }}
                                                            @elseif($log->user_id)
                                                                <i class="fas fa-user-slash text-xs"></i>
                                                            @else
                                                                <i class="fas fa-cog text-xs"></i>
                                                            @endif
                                                        </span>
                                                    </div>
                                                    <span class="font-bold text-gray-800 dark:text-white">
                                                        @if($log->user_name)
                                                            {{ $log->user_name }}
                                                        @elseif($log->user_id)
                                                            <span class="text-gray-400 italic font-medium">Deleted User</span>
                                                        @else
                                                            System
                                                        @endif
                                                    </span>
                                                </div>
                                            </td>

                                            {{-- Role — colors match RBAC User Accounts --}}
                                            <td class="px-5 py-3.5 whitespace-nowrap">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-bold uppercase tracking-wide {{ $roleColor }}">
                                                    {{ $log->user_role ?? 'System' }}
                                                </span>
                                            </td>

                                            {{-- Action --}}
                                            <td class="px-5 py-3.5 whitespace-nowrap">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-bold uppercase tracking-wide {{ $badgeBg }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $dot }} flex-none"></span>
                                                    {{ $log->action }}
                                                </span>
                                            </td>

                                            {{-- Details --}}
                                            <td class="px-5 py-3.5 text-gray-600 dark:text-gray-300">
                                                {{ $log->details }}
                                            </td>

                                            {{-- IP Address --}}
                                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                                <span class="text-sm text-gray-400 dark:text-gray-500">{{ $log->ip_address ?? 'N/A' }}</span>
                                            </td>
                                        </tr>
                        @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer: Pagination --}}
                    <div class="border-t border-gray-100 dark:border-gray-700 px-5 py-4 shrink-0">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">

                            <p class="text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                Showing
                                <span class="font-bold text-gray-700 dark:text-gray-200">{{ $audit_logs->firstItem() }}</span>
                                to
                                <span class="font-bold text-gray-700 dark:text-gray-200">{{ $audit_logs->lastItem() }}</span>
                                of
                                <span class="font-bold text-gray-700 dark:text-gray-200">{{ $audit_logs->total() }}</span>
                                results
                            </p>

                            <div class="flex items-center gap-1.5 flex-wrap justify-center">
                                {{-- Prev --}}
                                @if($audit_logs->onFirstPage())
                                    <span class="px-3 py-1.5 rounded-lg text-sm text-gray-300 dark:text-gray-600 cursor-not-allowed border border-gray-100 dark:border-gray-700">
                                        <i class="fas fa-chevron-left"></i>
                                    </span>
                                @else
                                    <a href="{{ $audit_logs->previousPageUrl() }}"
                                       class="px-3 py-1.5 rounded-lg text-sm text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                @endif

                                @php
                                    $lastPage = $audit_logs->lastPage();
                                    $currentPage = $audit_logs->currentPage();
                                    $start = max(1, $currentPage - 3);
                                    $end = min($lastPage, $currentPage + 3);
                                @endphp

                                @if($start > 1)
                                    <a href="{{ $audit_logs->url(1) }}"
                                       class="px-3 py-1.5 rounded-lg text-sm text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">1</a>
                                    @if($start > 2)
                                        <span class="px-1 text-sm text-gray-300 dark:text-gray-600">…</span>
                                    @endif
                                @endif

                                @for($p = $start; $p <= $end; $p++)
                                    @if($p == $currentPage)
                                        <span class="px-3 py-1.5 rounded-lg text-sm font-bold bg-blue-600 text-white border border-blue-600 shadow-sm">{{ $p }}</span>
                                    @else
                                        <a href="{{ $audit_logs->url($p) }}"
                                           class="px-3 py-1.5 rounded-lg text-sm text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">{{ $p }}</a>
                                    @endif
                                @endfor

                                @if($end < $lastPage)
                                    @if($end < $lastPage - 1)
                                        <span class="px-1 text-sm text-gray-300 dark:text-gray-600">…</span>
                                    @endif
                                    <a href="{{ $audit_logs->url($lastPage) }}"
                                       class="px-3 py-1.5 rounded-lg text-sm text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700">{{ $lastPage }}</a>
                                @endif

                                {{-- Next --}}
                                @if($audit_logs->hasMorePages())
                                    <a href="{{ $audit_logs->nextPageUrl() }}"
                                       class="px-3 py-1.5 rounded-lg text-sm text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                @else
                                    <span class="px-3 py-1.5 rounded-lg text-sm text-gray-300 dark:text-gray-600 cursor-not-allowed border border-gray-100 dark:border-gray-700">
                                        <i class="fas fa-chevron-right"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
        @endif
        </div>
</div>
@endsection