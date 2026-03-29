@extends('layouts.app')

@section('title', 'Notifications')
@section('page_title', 'Notifications')
@section('page_subtitle', 'Manage and view all your system alerts')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fas fa-bell text-primary"></i>
            Recent Notifications
        </h2>
        <div class="flex gap-3 items-center">
            <form action="{{ route('notifications.index') }}" method="GET" class="flex items-center gap-2 search-form shadow-none">
                <label for="per_page_notif" class="text-xs font-bold text-gray-400 uppercase tracking-wider">Show</label>
                <select name="per_page" id="per_page_notif" @change="submitSearch"
                    class="dropdown-btn w-20 px-3 py-1.5 h-10">
                    @foreach([5, 10, 15, 20, 50] as $n)
                        <option value="{{ $n }}" {{ request('per_page', 15) == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </form>
            <form action="{{ route('notifications.read') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                    <i class="fas fa-check-double mr-2"></i> Mark all as read
                </button>
            </form>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden search-results-container relative" x-data="tableSearch()" @click="handlePagination">
        @if($notifications->isEmpty())
            <div class="p-16 text-center">
                <div class="w-24 h-24 bg-gray-50 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="far fa-bell-slash text-4xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">No notifications found</h3>
                <p class="text-gray-500 dark:text-gray-400">We'll let you know when something important happens.</p>
            </div>
        @else
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($notifications as $notif)
                    <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors flex gap-5 {{ !$notif->is_read ? 'bg-blue-50/30 dark:bg-blue-900/10 border-l-4 border-l-primary' : '' }}">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 text-white shadow-md {{ $notif->color ?? 'bg-blue-500' }}">
                            <i class="fas {{ $notif->icon ?? 'fa-bell' }} text-xl"></i>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-gray-900 dark:text-white leading-tight text-sm">
                                    {{ $notif->action }}
                                </h4>
                                <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                    {{ $notif->time_ago }}
                                </span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 text-[13px] leading-relaxed mb-3">
                                {{ $notif->details }}
                            </p>
                            <div class="flex items-center gap-4">
                                @if($notif->link && $notif->link !== '#')
                                    <a href="{{ $notif->link }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
                                        View Details <i class="fas fa-arrow-right text-[10px]"></i>
                                    </a>
                                @endif
                                
                                <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400 dark:text-gray-500">
                                    {{ $notif->created_at->format('F d, Y • h:i A') }}
                                </span>
                            </div>
                        </div>

                        @if(!$notif->is_read)
                            <div class="w-3 h-3 rounded-full bg-primary mt-1 shadow-sm shrink-0"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="p-6 bg-gray-50 dark:bg-gray-950 border-t border-gray-100 dark:border-gray-800">
                    {{ $notifications->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<style>
    /* Pagination Styling Overrides */
    .pagination {
        display: flex;
        gap: 0.5rem;
    }
    .page-item .page-link {
        padding: 0.5rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid transparent;
        font-weight: 600;
        font-size: 0.875rem;
        color: #6b7280;
        background: white;
        transition: all 0.2s;
    }
    .dark .page-item .page-link {
        background: #1f2937;
        color: #9ca3af;
    }
    .page-item.active .page-link {
        background: #3b82f6;
        color: white;
    }
    .page-item:hover .page-link:not(.active) {
        background: #f3f4f6;
        border-color: #e5e7eb;
    }
    .dark .page-item:hover .page-link:not(.active) {
        background: #374151;
        border-color: #4b5563;
    }
</style>
<script>
    function tableSearch() {
        return {
            submitSearch() {
                this.$el.closest('form').submit();
            },
            handlePagination(e) {
                // Ensure pagination links work correctly with Alpine
                if (e.target.tagName === 'A' && e.target.closest('.pagination')) {
                    // Normal link behavior is fine here, but we can add loading states if needed
                }
            }
        }
    }
</script>
@endpush
