<!-- Components/Confirmation Modal -->
<style>
    @keyframes popIn {
        0% {
            opacity: 0;
            transform: scale(0.9);
        }

        100% {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes iconPulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(251, 146, 60, 0.4);
        }

        70% {
            transform: scale(1.1);
            box-shadow: 0 0 0 10px rgba(251, 146, 60, 0);
        }

        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(251, 146, 60, 0);
        }
    }

    .modal-enter {
        animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    .icon-animate {
        animation: iconPulse 2s infinite;
    }
</style>
<div id="confirmModal"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm transition-opacity duration-300">
    <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 relative transform transition-all modal-enter border border-gray-100">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-orange-50 mb-6 border-4 border-orange-100 icon-animate transition-colors duration-300"
                id="modalIconContainer">
                <i class="fas fa-exclamation text-orange-400 text-4xl" id="modalIcon"></i>
            </div>
            <h3 class="text-2xl leading-6 font-bold text-gray-800" id="modalTitle">Confirm Action</h3>
            <div class="mt-3">
                <p class="text-gray-500 text-base" id="modalMessage">Are you sure you want to proceed?</p>
            </div>
        </div>
        <div class="mt-8 grid grid-cols-2 gap-4">
            <button type="button" id="confirmBtn"
                class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg px-6 py-3 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none transition-all transform hover:-translate-y-0.5 shadow-red-200">
                Confirm
            </button>
            <button type="button" onclick="closeConfirmModal()"
                class="w-full inline-flex justify-center rounded-xl border border-gray-400 bg-gray-500 shadow-sm px-6 py-3 text-base font-bold text-white hover:bg-gray-600 focus:outline-none transition-all transform hover:-translate-y-0.5">
                Cancel
            </button>
        </div>
    </div>
</div>

<script>
    let confirmCallback = null;

    function showConfirm(title, message, btnClass, callback) {
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalMessage').innerText = message;

        const btn = document.getElementById('confirmBtn');
        // Reset and set new classes, preserving base button styles
        btn.className = "w-full inline-flex justify-center rounded-xl border border-transparent shadow-lg px-6 py-3 text-base font-bold text-white focus:outline-none transition-all transform hover:-translate-y-0.5 " + btnClass;

        // Dynamic Icon Color
        const iconContainer = document.getElementById('modalIconContainer');
        const icon = document.getElementById('modalIcon');

        // Reset base classes
        iconContainer.className = "mx-auto flex items-center justify-center h-24 w-24 rounded-full mb-6 border-4 icon-animate transition-colors duration-300";

        if (btnClass.includes('emerald') || btnClass.includes('green')) {
            // Success/Restore Style
            iconContainer.classList.add('bg-emerald-50', 'border-emerald-100');
            icon.className = "fas fa-check text-emerald-500 text-5xl";
            // Update animation color via custom property or just let it use default orange shadow (or we can adding specific class)
            // For simplicity we keep the pulse animation generic or inline style
        } else if (btnClass.includes('blue')) {
            // Info Style
            iconContainer.classList.add('bg-blue-50', 'border-blue-100');
            icon.className = "fas fa-info text-blue-500 text-5xl";
        } else {
            // Drop/Warning/Archive Style (Default Orange/Red mix)
            iconContainer.classList.add('bg-orange-50', 'border-orange-100');
            icon.className = "fas fa-exclamation text-orange-400 text-5xl";
        }

        confirmCallback = callback;
        document.getElementById('confirmModal').classList.remove('hidden');
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
        confirmCallback = null;
    }

    document.getElementById('confirmBtn').onclick = function () {
        if (confirmCallback) confirmCallback();
        closeConfirmModal();
    };

    // Standard Logout Confirmation Wrapper
    function confirmLogout() {
        showConfirm(
            'Logout System?',
            'Are you sure you want to end your current session?',
            'bg-red-600 hover:bg-red-700',
            () => { window.location.href = "../../auth/logout.php"; }
        );
    }
</script>