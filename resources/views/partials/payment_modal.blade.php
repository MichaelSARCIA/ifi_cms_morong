<div class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-show="modalOpen" x-data="{ 
            modalOpen: false,
            id: '', 
            applicant: '', 
            service: '', 
            amount: 0, 
            date: '', 
            time: '',
            paymentMethod: 'Cash', 
            amountTendered: '', 
            referenceNumber: '',
            isCash() { return this.paymentMethod === 'Cash'; },
            isElectronic() { return this.paymentMethod !== 'Cash'; },
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
                    hours = hours % 12;
                    hours = hours ? hours : 12; 
                    return `${hours}:${minutes} ${ampm}`;
                }
                return this.time;
            }
        }"
        @open-payment-modal.window="
            id = $event.detail.request.id;
            applicant = $event.detail.request.applicant_name || [$event.detail.request.first_name, $event.detail.request.middle_name, $event.detail.request.last_name].filter(Boolean).join(' ');
            service = $event.detail.request.service_type;
            amount = $event.detail.fee;
            date = $event.detail.request.scheduled_date;
            time = $event.detail.request.scheduled_time;
            paymentMethod = 'Cash';
            amountTendered = '';
            referenceNumber = '';
            modalOpen = true;
        "
        @close-payment-modal.window="modalOpen = false"
    >
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="modalOpen = false"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div x-show="modalOpen" class="bg-white dark:bg-gray-800 rounded-2xl w-full max-w-md shadow-2xl p-6 border border-gray-100 dark:border-gray-700 relative animate-fade-in-up">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-xl text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-cash-register text-green-600"></i> Process Payment
                    </h3>
                    <button type="button" @click="modalOpen = false"
                        class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="paymentForm" method="POST" onsubmit="return confirmPaymentSubmit(event, this)" :action="'{{ url('service-fees') }}/' + id + '/process-payment'">
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
                                <span class="absolute left-4 top-3.5 text-gray-400">₱</span>
                                <input type="number" step="0.01" name="amount" x-model="amount"
                                    class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-8 pr-4 py-3 text-sm font-bold text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                    required readonly>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Payment Method</label>
                            <div class="relative">
                                <select name="payment_method" x-model="paymentMethod" class="dropdown-btn w-full">
                                    @php
                                        // Standard payment methods as fallback
                                        $methods = ['Cash', 'GCash', 'PayMaya', 'Bank Transfer'];
                                        if(isset($payment_methods) && count($payment_methods) > 0) {
                                            $methods = $payment_methods->pluck('name')->toArray();
                                        }
                                    @endphp
                                    @foreach($methods as $method)
                                        <option value="{{ $method }}">{{ $method }}</option>
                                    @endforeach
                                </select>
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
                                <input type="number" step="0.01" name="amount_tendered" x-model="amountTendered"
                                    class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-bold text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                    placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Change</label>
                                <div class="w-full bg-gray-100 dark:bg-gray-800/50 border border-transparent rounded-xl px-4 py-3 text-sm font-bold text-green-600 dark:text-green-400"
                                    x-text="'₱' + changeDue.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                </div>
                            </div>
                        </div>

                        <div x-show="isElectronic()" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            <label class="block text-sm font-bold text-gray-500 dark:text-gray-400 uppercase mb-2 tracking-wider">Reference No.</label>
                            <input type="text" name="reference_number" x-model="referenceNumber"
                                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                placeholder="e.g. Ref No. 123456" :required="isElectronic()">
                        </div>

                        <button type="submit"
                            :disabled="(isCash() && (!amountTendered || parseFloat(amountTendered) <= 0)) || (isElectronic() && !referenceNumber.trim())"
                            :class="(isCash() && (!amountTendered || parseFloat(amountTendered) <= 0)) || (isElectronic() && !referenceNumber.trim())
                                ? 'w-full bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-bold py-3.5 rounded-xl mt-2 cursor-not-allowed'
                                : 'w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-3.5 rounded-xl mt-2 shadow-lg shadow-green-500/30 transition-all transform hover:-translate-y-0.5'">
                            <i class="fas fa-check-circle mr-2"></i> Confirm Payment &amp; Print Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        function openPaymentModal(request, fee) {
            window.dispatchEvent(new CustomEvent('open-payment-modal', { 
                detail: { request: request, fee: fee } 
            }));
        }

        function confirmPaymentSubmit(event, form) {
            event.preventDefault();
            showConfirm(
                'Confirm Payment?',
                'This will mark the request as Paid. You can print the receipt afterwards.',
                'bg-green-600 hover:bg-green-700',
                async () => {
                    try {
                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            },
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            window.dispatchEvent(new CustomEvent('close-payment-modal'));
                            
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
                            window.showConfirmModal({
                                title: 'Error',
                                message: result.message || 'Payment processing failed.',
                                btnClass: 'bg-red-600 hover:bg-red-700',
                                confirmText: 'Okay',
                                isAlert: true
                            });
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
                    }
                },
                'Process Payment'
            );
            return false;
        }
    </script>
