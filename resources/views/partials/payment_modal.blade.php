    <div class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-show="modalOpen" x-data="{ 
            modalOpen: false,
            id: '', 
            applicant: '', 
            service: '', 
            amount: 0, 
            date: '', 
            time: '',
            paymentMethod: '', 
            @php
                $pms = $payment_methods ?? $data['payment_methods'] ?? collect();
            @endphp
            allMethods: {{ json_encode($pms->map(fn($m) => ['id' => (string)$m->id, 'name' => $m->name])->toArray()) }},
            allowedMethodIds: [],
            hasCustomConfig: false,
            get filteredMethods() {
                if (!this.hasCustomConfig) {
                    return this.allMethods;
                }
                return this.allMethods.filter(m => this.allowedMethodIds.includes(String(m.id)));
            },
            amountTendered: '', 
            referenceNumber: '',
            isCash() { return this.paymentMethod === 'Cash'; },
            isElectronic() { return this.paymentMethod !== 'Cash' && this.paymentMethod !== ''; },
            isProcessing: false,
            referenceError: '',
            get changeDue() { 
                const tendered = parseFloat(this.amountTendered) || 0;
                return Math.max(0, tendered - this.amount);
            },
            get formattedDate() {
                if(!this.date) return 'N/A';
                try {
                    let formattedStr = '';
                    const match = String(this.date).match(/^(\d{4}-\d{2}-\d{2})/);
                    if (match) {
                        const d = new Date(match[1] + 'T00:00:00');
                        if (!isNaN(d.getTime())) {
                            formattedStr = d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                        }
                    }
                    if (!formattedStr) {
                        const fallback = new Date(this.date);
                        if (!isNaN(fallback.getTime())) {
                            formattedStr = fallback.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                        } else {
                            formattedStr = String(this.date);
                        }
                    }
                    return formattedStr;
                } catch(e) { return String(this.date); }
            },
            get formattedTime() {
                if (!this.time) return 'N/A';
                const timeParts = String(this.time).split(':');
                if (timeParts.length >= 2) {
                    let hours = parseInt(timeParts[0], 10);
                    const minutes = timeParts[1];
                    const ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours >= 12 ? (hours > 12 ? hours - 12 : hours) : (hours === 0 ? 12 : hours);
                    return `${hours}:${minutes} ${ampm}`;
                }
                return this.time;
            }
        }"
        @open-payment-modal.window="
            id = $event.detail.request.id;
            applicant = $event.detail.request.applicant_name || [$event.detail.request.first_name, $event.detail.request.middle_name, $event.detail.request.last_name, $event.detail.request.suffix].filter(Boolean).join(' ');
            service = $event.detail.request.service_type || 'N/A';
            amount = $event.detail.fee || 0;
            date = $event.detail.request.scheduled_date;
            time = $event.detail.request.scheduled_time;
            
            // Extract allowed payment methods safely
            const rawAllowed = $event.detail.request.allowed_payment_methods;
            const configId = $event.detail.request.service_type_config_id;
            
            if (configId !== null && configId !== undefined) {
                // We have a custom configuration record for this service type
                hasCustomConfig = true;
                try {
                    let parsed = [];
                    if (Array.isArray(rawAllowed)) {
                        parsed = rawAllowed;
                    } else if (typeof rawAllowed === 'string' && rawAllowed.trim() !== '') {
                        parsed = JSON.parse(rawAllowed);
                    }
                    allowedMethodIds = Array.isArray(parsed) ? parsed.map(String) : [];
                } catch(e) {
                    console.error('Error parsing allowed payment methods:', e);
                    allowedMethodIds = [];
                }
            } else {
                // No configuration record found (legacy) - fallback to all active methods
                hasCustomConfig = false;
                allowedMethodIds = [];
            }
            
            // Require explicit payment method selection
            paymentMethod = '';

            amountTendered = '';
            referenceNumber = '';
            referenceError = '';
            isProcessing = false;
            modalOpen = true;
        "
        @close-payment-modal.window="modalOpen = false"
    >
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="!isProcessing && (modalOpen = false)"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div x-show="modalOpen" class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md shadow-2xl p-6 border border-gray-100 dark:border-gray-700 relative animate-fade-in-up">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-xl text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-cash-register text-green-600"></i> Process Payment
                    </h3>
                    <button type="button" @click="modalOpen = false" :disabled="isProcessing"
                        class="text-gray-400 hover:text-gray-600 disabled:opacity-30">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="paymentForm" method="POST" @submit.prevent="confirmPaymentSubmit($el)" :action="'{{ url('service-fees') }}/' + id + '/process-payment'">
                    @csrf
                    <div class="space-y-4">
                        <!-- Service Info (Read Only) -->
                        <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-xl space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Transaction No:</span>
                                <span class="font-bold text-gray-800 dark:text-white" x-text="'TXN-' + String(id).padStart(6, '0')"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Applicant:</span>
                                <span class="font-bold text-gray-800 dark:text-white" x-text="applicant"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Service:</span>
                                <span class="font-bold text-gray-800 dark:text-white" x-text="service"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Scheduled Date:</span>
                                <span class="font-bold text-gray-800 dark:text-white" x-text="formattedDate"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Scheduled Time:</span>
                                <span class="font-bold text-gray-800 dark:text-white" x-text="formattedTime"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Amount (₱)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">₱</span>
                                <input type="number" step="0.01" name="amount" x-model="amount"
                                    class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-10 pr-4 py-3 text-sm font-bold text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                    required readonly>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Payment Method</label>
                            <div class="relative">
                                <template x-if="filteredMethods.length > 0">
                                    <select name="payment_method" x-model="paymentMethod" class="dropdown-btn w-full" :disabled="isProcessing" required>
                                        <option value="" disabled selected>Select Payment Method...</option>
                                        <template x-for="method in filteredMethods" :key="method.id">
                                            <option :value="method.name" x-text="method.name"></option>
                                        </template>
                                    </select>
                                </template>
                                <template x-if="filteredMethods.length === 0">
                                    <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                                        <p class="text-xs text-red-600 dark:text-red-400 font-bold">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> No payment methods configured for this service.
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Cash Specific Fields -->
                        <div x-show="isCash()" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Amount Tendered</label>
                                <input type="number" step="0.01" name="amount_tendered" x-model="amountTendered" :disabled="isProcessing"
                                    @keydown="['e', 'E', '+', '-'].includes($event.key) && $event.preventDefault()"
                                    class="w-full bg-gray-50 dark:bg-gray-900 border rounded-xl px-4 py-3 text-sm font-bold text-gray-800 dark:text-white focus:outline-none focus:ring-2 transition-all"
                                    :class="isCash() && amountTendered && parseFloat(amountTendered) < amount 
                                        ? 'border-red-500 focus:ring-red-500/20 ring-2 ring-red-500/10' 
                                        : 'border-gray-200 dark:border-gray-700 focus:ring-green-500/20 focus:border-green-500'"
                                    placeholder="0.00">
                                <p x-show="isCash() && amountTendered && parseFloat(amountTendered) < amount" 
                                   class="text-[10px] text-red-500 mt-1 font-bold animate-pulse">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Insufficient amount!
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Change</label>
                                <div class="w-full bg-gray-100 dark:bg-gray-800/50 border border-transparent rounded-xl px-4 py-3 text-sm font-bold text-green-600 dark:text-green-400"
                                    x-text="'₱ ' + changeDue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                </div>
                            </div>
                        </div>

                        <div x-show="isElectronic()" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            <label class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Reference No.</label>
                            <input type="text" name="reference_number" x-model="referenceNumber" :disabled="isProcessing"
                                @input="referenceError = ''"
                                :class="referenceError ? 'border-red-500 focus:ring-red-500/20' : 'border-gray-200 dark:border-gray-700 focus:ring-green-500/20 focus:border-green-500'"
                                class="w-full bg-gray-50 dark:bg-gray-900 border rounded-xl px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 transition-all"
                                placeholder="e.g. Ref No. 123456" :required="isElectronic()">
                            <p x-show="referenceError" x-text="referenceError" 
                                class="text-[11px] text-red-500 mt-1 font-bold animate-pulse"
                                x-transition>
                            </p>
                        </div>

                        <button type="submit"
                            :disabled="!paymentMethod || isProcessing || (isCash() && (!amountTendered || parseFloat(amountTendered) < amount)) || (isElectronic() && !referenceNumber.trim())"
                            :class="(!paymentMethod || isProcessing || (isCash() && (!amountTendered || parseFloat(amountTendered) < amount)) || (isElectronic() && !referenceNumber.trim()))
                                ? 'w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-bold py-3.5 rounded-xl mt-2 cursor-not-allowed'
                                : 'w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-3.5 rounded-xl mt-2 shadow-lg shadow-green-500/30 transition-all transform hover:-translate-y-0.5'">
                            <template x-if="!isProcessing">
                                <span><i class="fas fa-check-circle mr-2"></i> Confirm Payment &amp; Print Receipt</span>
                            </template>
                            <template x-if="isProcessing">
                                <span><i class="fas fa-spinner fa-spin mr-2"></i> Processing...</span>
                            </template>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        window.openPaymentModal = async function(request, fee) {
            // If we have a config ID, fetch the LATEST data to ensure absolute accuracy without reload
            if (request.service_type_config_id) {
                try {
                    // Quick fetch for the latest settings
                    const response = await fetch(`/api/services/${request.service_type_config_id}`);
                    if (response.ok) {
                        const fresh = await response.json();
                        // Update the request object with fresh configuration
                        request.allowed_payment_methods = fresh.payment_methods;
                        // Use fresh fee if available
                        if (fresh.fee !== undefined) {
                            fee = fresh.fee;
                        }
                    }
                } catch (e) {
                    console.error("Failed to fetch fresh service settings:", e);
                    // Fallback to existing data if fetch fails
                }
            }

            window.dispatchEvent(new CustomEvent('open-payment-modal', { 
                detail: { request: request, fee: fee } 
            }));
        };

        async function confirmPaymentSubmit(form) {
            const alpineData = Alpine.$data(form.closest('[x-data]'));
            if (alpineData.isProcessing) return;
            
            alpineData.isProcessing = true;
            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alpineData.modalOpen = false;
                    
                    // Emit global event to update notifications bell
                    window.dispatchEvent(new CustomEvent('notification-updated'));

                    // Success Alert
                    window.showConfirmModal({
                        title: 'Success',
                        message: result.message,
                        btnClass: 'bg-green-600 hover:bg-green-700',
                        confirmText: 'Okay',
                        isAlert: true
                    });

                    // Update UI Row - Handle both specific row IDs (donations) and generic row handling
                    const url = form.action;
                    const parts = url.split('/');
                    const id = parts[parts.length - 2];

                    const row = document.getElementById('row-' + id);
                    if (row) {
                        // Update row in donations page
                        let actionCell = row.cells[row.cells.length - 1];
                        let paymentCell = row.cells.length >= 6 ? row.cells[4] : null;
                        if (paymentCell) {
                            paymentCell.innerHTML = `
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                    <i class="fas fa-check-circle"></i> Paid
                                </span>
                            `;
                        }

                        actionCell.innerHTML = `
                            <div class="flex items-center justify-center gap-2">
                                <button disabled
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-400 cursor-not-allowed"
                                    title="Already Paid">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                                <a href="${result.receipt_url}" target="_blank"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-500/30 transition-all"
                                    title="Print Receipt">
                                    <i class="fas fa-print text-xs"></i>
                                </a>
                            </div>
                        `;
                    } else {
                        // If not row-id found, might be dashboard, reload or update differently
                        // For now, simple reload is safest if we can't find the exact row easily
                        setTimeout(() => window.location.reload(), 1000);
                    }

                } else {
                    // Extract validation errors if possible
                    let errorMessage = result.message || 'Payment processing failed.';
                    let isReferenceError = false;
                    
                    if (result.errors && result.errors.reference_number) {
                        errorMessage = result.errors.reference_number[0];
                        alpineData.referenceError = errorMessage;
                        isReferenceError = true;
                    }
                    
                    if (!isReferenceError) {
                        window.showConfirmModal({
                            title: 'Payment Error',
                            message: errorMessage,
                            btnClass: 'bg-red-600 hover:bg-red-700',
                            confirmText: 'Okay',
                            isAlert: true
                        });
                    }
                }
            } catch (e) {
                console.error(e);
                window.showConfirmModal({
                    title: 'Error',
                    message: 'An unexpected error occurred.',
                    btnClass: 'bg-red-600 hover:bg-red-700',
                    confirmText: 'Okay',
                    isAlert: true
                });
            } finally {
                alpineData.isProcessing = false;
            }
        }
    </script>
