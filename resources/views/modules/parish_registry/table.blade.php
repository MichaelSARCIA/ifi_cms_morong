@forelse($members as $m)
    <tr class="hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors group cursor-pointer"
        @click="viewMember = {{ json_encode($m) }}; viewModalOpen = true">

        <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-sm font-bold">{{ $m->id }}
        </td>
        <td class="px-6 py-4">
            <span class="font-bold text-gray-900 dark:text-white text-base leading-tight">{{ $m->full_name }}</span>
        </td>
        <td class="px-4 py-4 text-center">
            <span
                class="inline-flex items-center justify-center px-2 py-1 rounded-md text-xs font-bold w-full max-w-[80px] {{ $m->sex == 'Male' ? 'text-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'text-pink-600 bg-pink-50 dark:bg-pink-900/20' }} border border-transparent whitespace-nowrap">
                <i class="fas {{ $m->sex == 'Male' ? 'fa-mars' : 'fa-venus' }} mr-1.5"></i>
                {{ $m->sex }}
            </span>
        </td>
        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 uppercase tracking-wide truncate max-w-[200px]"
            title="{{ $m->address }}">
            {{ Str::limit($m->address, 30) }}
        </td>
        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 font-medium whitespace-nowrap">
            {{ $m->contact_info }}
        </td>
        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
            {{ date('F d, Y', strtotime($m->birthdate)) }}
        </td>
        <td class="px-6 py-4 text-center">
            <span
                class="inline-flex items-center px-2.5 py-1 text-xs font-bold rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 uppercase tracking-wide border border-gray-200 dark:border-gray-600">
                {{ $m->marital_status }}
            </span>
        </td>
        <td class="px-8 py-4 text-right" @click.stop>
            <div class="flex justify-end gap-2">
                <button @click="
                                                editMode = true; 
                                                member = {
                                                    id: '{{ $m->id }}',
                                                    full_name: '{{ $m->full_name }}',
                                                    // Simple parsing logic
                                                    surname: '{{ explode(', ', $m->full_name)[0] ?? '' }}',
                                                    first_name: '{{ explode(' ', explode(', ', $m->full_name)[1] ?? '')[0] ?? '' }}',
                                                    birthdate: '{{ $m->birthdate }}',
                                                    sex: '{{ $m->sex }}',
                                                    contact_info: '{{ $m->contact_info }}',
                                                    address: '{{ $m->address }}',
                                                    marital_status: '{{ $m->marital_status }}'
                                                }; 
                                                modalOpen = true"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-blue-600 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 transition-all border border-blue-100 dark:border-blue-800"
                    title="Edit">
                    <i class="fas fa-edit"></i>
                </button>


            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
            <div class="flex flex-col items-center justify-center">
                <i class="fas fa-users text-4xl mb-4 text-gray-300 dark:text-gray-600"></i>
                <p class="text-lg font-bold text-gray-900 dark:text-white">No parishioners found</p>
                <p class="text-sm text-gray-500">Get started by adding a new member.</p>
            </div>
        </td>
    </tr>
@endforelse

<!-- Pagination Links inside the partial? -->
<!-- Actually, implementing infinite scroll or just standard pagination links update is tricky via simple partial replacement. -->
<!-- For now, we will just replace the table rows. The user asked for SEARCH. -->
<!-- If search results fit in one page (limit 10), it's fine. -->