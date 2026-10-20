{{-- Modal Riwayat Log Invoice --}}
<div id="invoiceLogModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl overflow-hidden animate-fade-in">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div>
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <span>📑 Riwayat Log Invoice</span>
                    <span id="invoiceLogDocTitle" class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-blue-100 text-blue-800"></span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Catatan lengkap status perubahaan invoice (Done / Cancel)</p>
            </div>
            <button type="button" onclick="closeInvoiceLogModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="p-6">
            {{-- Loading State --}}
            <div id="invoiceLogLoading" class="py-10 text-center">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
                <p class="text-xs text-gray-500 mt-2">Memuat riwayat log...</p>
            </div>

            {{-- Empty State --}}
            <div id="invoiceLogEmpty" class="hidden py-10 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-sm font-medium text-gray-600 mt-2">Belum ada riwayat status invoice</p>
                <p class="text-xs text-gray-400">Setiap perubahan status invoice akan dicatat di sini.</p>
            </div>

            {{-- Table Content --}}
            <div id="invoiceLogContent" class="hidden max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
                <table class="w-full text-left text-xs text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-200 uppercase font-semibold text-gray-500 tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-center w-12">#</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Dilakukan Oleh</th>
                            <th class="px-4 py-3">Tanggal & Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="invoiceLogTableBody" class="divide-y divide-gray-100">
                        {{-- Log items injected via JavaScript --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button type="button" onclick="closeInvoiceLogModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-lg transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    window.showInvoiceLogs = function(type, id, noRaf) {
        const modal = document.getElementById('invoiceLogModal');
        const title = document.getElementById('invoiceLogDocTitle');
        const tbody = document.getElementById('invoiceLogTableBody');
        const loading = document.getElementById('invoiceLogLoading');
        const empty = document.getElementById('invoiceLogEmpty');
        const content = document.getElementById('invoiceLogContent');

        if (!modal) return;

        title.textContent = (type ? type.toUpperCase() : '') + (noRaf ? ' - ' + noRaf : '');
        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        empty.classList.add('hidden');

        fetch(`{{  url('/invoice-logs') }}/${type}/${id}`)
            .then(res => {
                if (!res.ok) throw new Error('Gagal mengambil data');
                return res.json();
            })
            .then(data => {
                loading.classList.add('hidden');
                tbody.innerHTML = '';
                const logs = Array.isArray(data) ? data : (data.logs || []);
                if (!logs || logs.length === 0) {
                    empty.classList.remove('hidden');
                    return;
                }
                content.classList.remove('hidden');
                logs.forEach((log, index) => {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b border-gray-100 hover:bg-gray-50';
                    const isDone = log.status === 'done';
                    const badgeClass = isDone ? 'bg-green-100 text-green-700 border-green-200' : 'bg-red-100 text-red-700 border-red-200';
                    const statusLabel = isDone ? 'DONE' : 'CANCEL';
                    tr.innerHTML = `
                        <td class="px-4 py-2.5 text-center text-xs text-gray-500">${index + 1}</td>
                        <td class="px-4 py-2.5 text-xs">
                            <span class="inline-block px-2 py-0.5 font-bold rounded border ${badgeClass}">
                                ${statusLabel}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-xs font-semibold text-gray-800">${log.user_name || '-'}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-600">${log.datetime || log.action_at || '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            })
            .catch(err => {
                loading.classList.add('hidden');
                empty.classList.remove('hidden');
                empty.querySelector('p').textContent = 'Gagal memuat log invoice: ' + err.message;
            });
    };

    window.closeInvoiceLogModal = function() {
        const modal = document.getElementById('invoiceLogModal');
        if (modal) modal.classList.add('hidden');
    };
</script>
