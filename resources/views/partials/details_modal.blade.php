<div id="detailsModal" x-cloak class="hidden fixed inset-0 z-[9999] overflow-y-auto">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" onclick="closeDetailsModal()"></div>

    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-2xl shadow-2xl p-6 border border-gray-100 dark:border-gray-700 relative animate-fade-in-up" 
             style="max-width: 672px !important; width: 100% !important;">

            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-xl text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-info-circle text-blue-500"></i> Service Details
                </h3>
                <button type="button" onclick="closeDetailsModal()"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">
                        <span class="block text-sm text-black dark:text-white uppercase mb-1" style="font-weight: 700 !important;">Service Type</span>
                        <span class="text-gray-800 dark:text-white" id="detailServiceType" style="font-weight: 400 !important;"></span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">
                        <span class="block text-sm text-black dark:text-white uppercase mb-1" style="font-weight: 700 !important;">Service Status</span>
                        <span class="text-gray-800 dark:text-white" id="detailStatus" style="font-weight: 400 !important;"></span>
                        <span class="block text-xs text-gray-400 mt-1" id="detailStatusDate"></span>
                    </div>
                </div>

                @if(Auth::user()->role !== 'Priest')
                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl flex items-center justify-between">
                    <span class="text-sm text-black dark:text-white uppercase" style="font-weight: 700 !important;">Payment Status</span>
                    <span id="detailPaymentStatus"></span>
                </div>
                @endif

                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">
                    <span class="block text-sm text-black dark:text-white uppercase mb-1" style="font-weight: 700 !important;">Applicant Name</span>
                    <span class="text-gray-800 dark:text-white" id="detailName" style="font-weight: 400 !important;"></span>
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl">
                    <span class="block text-sm text-black dark:text-white uppercase mb-1" style="font-weight: 700 !important;">Scheduled Date</span>
                    <span class="text-gray-800 dark:text-white" id="detailDate" style="font-weight: 400 !important;"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl overflow-hidden">
                        <span class="block text-sm text-black dark:text-white uppercase mb-1" style="font-weight: 700 !important;">Contact No.</span>
                        <span class="text-gray-800 dark:text-white" id="detailContact" style="font-weight: 400 !important;"></span>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl overflow-hidden">
                        <span class="block text-sm text-black dark:text-white uppercase mb-1" style="font-weight: 700 !important;">Email</span>
                        <span class="text-gray-800 dark:text-white break-words" id="detailEmail" style="font-weight: 400 !important;"></span>
                    </div>
                </div>

                <div>
                    <span class="block text-sm text-black dark:text-white uppercase mb-2" style="font-weight: 700 !important;">Requirements Submitted</span>
                    <div class="flex flex-wrap gap-2" id="detailRequirements"></div>
                </div>

                <div>
                    <span class="block text-sm text-black dark:text-white uppercase mb-1" style="font-weight: 700 !important;">Other Details</span>
                    <p class="text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700 font-normal"
                        id="detailDetails"></p>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700 mt-6">
                <button type="button" onclick="closeDetailsModal()"
                    class="px-6 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium transition-all text-sm">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.openDetailsModal = function(request) {
        try {
            // Helper functions for badges
            const serviceColorsMap = {!! json_encode(\App\Helpers\ServiceHelper::getServiceColorMap()) !!};
            const getServiceBadgeClass = (serviceType) => {
                const type = (serviceType || '').toLowerCase();
                if (serviceColorsMap[type]) return serviceColorsMap[type];
                
                for (const [key, cls] of Object.entries(serviceColorsMap)) {
                    if (type.includes(key)) return cls;
                }

                return 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
            };

            const getStatusBadgeClass = (status) => {
                const st = (status || '').toLowerCase();
                if (st.includes('pending')) return 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800/40';
                if (st === 'for priest review') return 'bg-indigo-100 text-indigo-700 border-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-400 dark:border-indigo-800/40';
                if (st === 'for payment') return 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800/40';
                if (st === 'approved') return 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800/40';
                if (st === 'completed') return 'bg-green-100 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800/40';
                if (st === 'cancelled' || st === 'declined' || st === 'rejected') return 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/40';
                return 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-700';
            };

            // Populate details with pill badges
            const sType = request.service_type || 'N/A';
            const sStatus = request.status || 'N/A';
            document.getElementById('detailServiceType').innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-base mt-1 font-bold border ${getServiceBadgeClass(sType)}">${sType}</span>`;
            document.getElementById('detailStatus').innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-base mt-1 font-bold border ${getStatusBadgeClass(sStatus)}">${sStatus}</span>`;

            // Show date/time when status is Completed
            const statusDateEl = document.getElementById('detailStatusDate');
            if (statusDateEl && request.updated_at && (request.status === 'Completed' || request.status === 'Approved')) {
                const d = new Date(request.updated_at);
                const formatted = d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })
                    + ' at ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                statusDateEl.textContent = (request.status === 'Completed' ? 'Completed on: ' : 'Approved on: ') + formatted;
            } else if (statusDateEl) {
                statusDateEl.textContent = '';
            }

            // Handle both unified applicant_name (from Dashboard view) and raw columns
            let name = request.applicant_name || '';
            if (!name && (request.first_name || request.last_name)) {
                name = [request.first_name, request.middle_name, request.last_name, request.suffix].filter(Boolean).join(' ');
            }
            document.getElementById('detailName').textContent = name || 'N/A';

            // Format Date safely
            let formattedDate = 'N/A';
            if (request.scheduled_date) {
                // Extract just the YYYY-MM-DD part if it includes time
                const datePart = request.scheduled_date.includes('T') ? request.scheduled_date.split('T')[0] : request.scheduled_date;
                const date = new Date(datePart + 'T00:00:00');
                if (!isNaN(date.getTime())) {
                    formattedDate = date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                }
            }
            
            let timeStr = '';
            if(request.scheduled_time) {
                const tConvert = (time) => {
                    if (!time) return '';
                    let [h, m] = time.substring(0,5).split(':');
                    let part = h >= 12 ? 'PM' : 'AM';
                    h = h % 12 || 12;
                    return `${h}:${m} ${part}`;
                };
                timeStr = ' @ ' + tConvert(request.scheduled_time);
            }

            document.getElementById('detailDate').textContent = formattedDate + timeStr;

            document.getElementById('detailContact').textContent = request.contact_number || (request.custom_data?.contact_number || (request.custom_data?.contact_no || 'N/A'));

            // Fallback for email (checks both root and custom_data fields)
            let email = request.email;
            if (!email && request.custom_data) {
                email = request.custom_data.email || request.custom_data.email_address;
            }
            document.getElementById('detailEmail').textContent = email || 'N/A';

            document.getElementById('detailDetails').textContent = request.details || 'None';

            // Requirements
            const reqList = document.getElementById('detailRequirements');
            if (reqList) {
                reqList.innerHTML = '';
                let reqs = request.requirements;
                if (typeof reqs === 'string') {
                    try { reqs = JSON.parse(reqs); } catch(e) { reqs = []; }
                }
                if (Array.isArray(reqs) && reqs.length > 0) {
                    reqs.forEach(req => {
                        const span = document.createElement('span');
                        span.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-sm font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800';
                        span.innerHTML = '<i class="fas fa-check-circle text-green-600 dark:text-green-500"></i> ' + req;
                        reqList.appendChild(span);
                    });
                } else {
                    reqList.innerHTML = '<span class="text-gray-500 text-sm italic">No requirements submitted.</span>';
                }
            }

            // Payment Status Badge
            const payEl = document.getElementById('detailPaymentStatus');
            if (payEl) {
                const isPaid = request.payment_status === 'Paid';
                const isWaived = request.payment_status === 'Waived';
                
                if (isPaid) {
                    payEl.innerHTML = '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-700 border border-green-200"><i class="fas fa-check-circle"></i> Paid</span>';
                } else if (isWaived) {
                    payEl.innerHTML = '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-700 border border-blue-200"><i class="fas fa-hand-holding-heart"></i> Waived</span>';
                } else {
                    payEl.innerHTML = '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200"><i class="fas fa-clock"></i> Unpaid — Awaiting Payment</span>';
                }
            }

            // Show modal
            document.getElementById('detailsModal').classList.remove('hidden');
        } catch (e) {
            console.error("Error opening details modal:", e);
        }
    };

    window.closeDetailsModal = function() {
        document.getElementById('detailsModal').classList.add('hidden');
    };
</script>
