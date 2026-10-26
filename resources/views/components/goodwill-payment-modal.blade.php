{{-- Modal Detail Pembayaran Invoice Goodwill --}}
<div id="goodwillPaymentModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden animate-fade-in flex flex-col max-h-[90vh]">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div>
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2 flex-wrap">
                    <span>💳 Detail Pembayaran Invoice Goodwill</span>
                    <span id="goodwillModalInvNo" class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-indigo-100 text-indigo-800 border border-indigo-200"></span>
                    <span id="goodwillModalSoNo" class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-gray-100 text-gray-700 border border-gray-300"></span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Informasi invoice dan riwayat alokasi / pembayaran dari sistem Goodwill</p>
            </div>
            <button type="button" onclick="closeGoodwillPaymentModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="p-6 overflow-y-auto space-y-5">
            {{-- Loading State --}}
            <div id="goodwillPaymentLoading" class="py-12 text-center">
                <div class="inline-block animate-spin rounded-full h-9 w-9 border-4 border-indigo-600 border-t-transparent"></div>
                <p class="text-xs text-gray-500 mt-2 font-medium">Memuat data pembayaran Goodwill...</p>
            </div>

            {{-- Main Content --}}
            <div id="goodwillPaymentContent" class="hidden space-y-5">
                {{-- Tab Pemilih Invoice jika 1 RAF memiliki banyak Invoice --}}
                <div id="goodwillSiblingInvoicesWrap" class="hidden p-3 bg-indigo-50/70 border border-indigo-200 rounded-lg">
                    <div class="text-xs font-bold text-indigo-900 mb-2 flex items-center justify-between flex-wrap gap-1">
                        <span class="flex items-center gap-1.5">
                            <span>📑</span> No. RAF ini memiliki <span id="goodwillSiblingCount" class="underline font-extrabold"></span> Invoice:
                        </span>
                        <span class="text-[11px] font-normal text-indigo-700">Pilih invoice untuk melihat rincian alokasinya</span>
                    </div>
                    <div id="goodwillSiblingTabs" class="flex flex-wrap gap-2"></div>
                </div>

                {{-- Info Invoice Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Client</div>
                        <div id="goodwillInfoClient" class="text-sm font-bold text-gray-800 mt-0.5">-</div>
                        <div id="goodwillInfoBillDate" class="text-[11px] text-gray-500 mt-1">Bill Date: -</div>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Tanggal Invoice</div>
                        <div id="goodwillInfoInvDate" class="text-sm font-bold text-gray-800 mt-0.5">-</div>
                        <div id="goodwillInfoStatus" class="text-[11px] text-gray-500 mt-1">Status: -</div>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Grand Total Invoice</div>
                        <div id="goodwillInfoGrandTotal" class="text-sm font-bold text-indigo-700 mt-0.5">-</div>
                        <div id="goodwillInfoPaidStatus" class="text-[11px] font-semibold mt-1 text-gray-600">Paid: -</div>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Total Teralokasi</div>
                        <div id="goodwillInfoTotalAlloc" class="text-sm font-bold text-emerald-700 mt-0.5">Rp 0,00</div>
                        <div id="goodwillInfoAllocCount" class="text-[11px] text-gray-500 mt-1">0 Alokasi</div>
                    </div>
                </div>

                {{-- Tabel Daftar Payment / Alokasi --}}
                <div>
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>Daftar Alokasi & Pembayaran</span>
                    </h4>

                    {{-- Empty State --}}
                    <div id="goodwillPaymentEmpty" class="hidden py-8 text-center border border-dashed border-gray-200 rounded-lg bg-gray-50">
                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-xs font-semibold text-gray-600 mt-2">Belum ada riwayat alokasi payment untuk invoice ini</p>
                        <p class="text-[11px] text-gray-400">Data alokasi payment akan muncul setelah diimport melalui command goodwill.</p>
                    </div>

                    {{-- Table --}}
                    <div id="goodwillPaymentTableWrap" class="border border-gray-200 rounded-lg overflow-x-auto">
                        <table class="w-full text-left text-xs text-gray-600">
                            <thead class="bg-gray-50 border-b border-gray-200 uppercase font-bold text-gray-500 tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-center w-10">#</th>
                                    <th class="px-4 py-3">No. Alokasi</th>
                                    <th class="px-4 py-3">Tgl Alokasi</th>
                                    <th class="px-4 py-3 text-right">Nominal Alokasi</th>
                                    <th class="px-4 py-3">No. Payment</th>
                                    <th class="px-4 py-3">Tgl Payment</th>
                                    <th class="px-4 py-3 text-center">Paid</th>
                                </tr>
                            </thead>
                            <tbody id="goodwillPaymentTableBody" class="divide-y divide-gray-100">
                                {{-- Dynamically populated via JavaScript --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button type="button" onclick="closeGoodwillPaymentModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-lg transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    window.showGoodwillPayments = function(invNo, noRaf) {
        const modal = document.getElementById('goodwillPaymentModal');
        const invNoBadge = document.getElementById('goodwillModalInvNo');
        const soNoBadge = document.getElementById('goodwillModalSoNo');
        const loading = document.getElementById('goodwillPaymentLoading');
        const content = document.getElementById('goodwillPaymentContent');
        const empty = document.getElementById('goodwillPaymentEmpty');
        const tableWrap = document.getElementById('goodwillPaymentTableWrap');
        const tbody = document.getElementById('goodwillPaymentTableBody');

        if (!modal) return;

        invNoBadge.textContent = invNo ? 'Inv: ' + invNo : 'Invoice';
        soNoBadge.textContent = noRaf ? 'RAF: ' + noRaf : '';
        soNoBadge.style.display = noRaf ? 'inline-block' : 'none';

        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');

        // Fetch detail
        const query = invNo ? `inv_no=${encodeURIComponent(invNo)}` : `so_no=${encodeURIComponent(noRaf)}`;
        fetch(`{{ url('/loc/goodwill-payments') }}?${query}`)
            .then(res => {
                if (!res.ok) throw new Error('Gagal mengambil data goodwill');
                return res.json();
            })
            .then(data => {
                loading.classList.add('hidden');
                content.classList.remove('hidden');

                const inv = data.invoice;
                const payments = data.payments || [];
                const siblings = data.sibling_invoices || [];

                // Render Tab Sibling Invoices jika 1 No. RAF punya lebih dari 1 invoice
                const siblingWrap = document.getElementById('goodwillSiblingInvoicesWrap');
                const siblingCount = document.getElementById('goodwillSiblingCount');
                const siblingTabs = document.getElementById('goodwillSiblingTabs');
                if (siblingWrap && siblingTabs) {
                    if (siblings.length > 1) {
                        siblingWrap.classList.remove('hidden');
                        siblingCount.textContent = siblings.length;
                        siblingTabs.innerHTML = '';
                        siblings.forEach(sib => {
                            const isCurrent = (inv && sib.inv_no === inv.inv_no);
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = isCurrent
                                ? 'px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 text-white shadow-xs flex items-center gap-1.5 cursor-default'
                                : 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-white text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition flex items-center gap-1.5 cursor-pointer';

                            const isPaid = (sib.paid || '').toUpperCase() === 'Y';
                            const paidBadge = isPaid
                                ? '<span class="px-1.5 py-0.5 rounded text-[9px] bg-green-100 text-green-800 font-bold">PAID</span>'
                                : '<span class="px-1.5 py-0.5 rounded text-[9px] bg-amber-100 text-amber-800 font-bold">UNPAID</span>';

                            btn.innerHTML = `<span>${sib.inv_no}</span> <span class="opacity-80 text-[11px]">(${sib.grand_total_fmt})</span> ${paidBadge}`;
                            if (!isCurrent) {
                                btn.onclick = () => window.showGoodwillPayments(sib.inv_no, noRaf);
                            }
                            siblingTabs.appendChild(btn);
                        });
                    } else {
                        siblingWrap.classList.add('hidden');
                    }
                }

                // Render Info Invoice
                if (inv) {
                    document.getElementById('goodwillInfoClient').textContent = inv.client || '-';
                    document.getElementById('goodwillInfoBillDate').textContent = 'Bill Date: ' + (inv.bill_date || '-');
                    document.getElementById('goodwillInfoInvDate').textContent = inv.inv_date || '-';
                    document.getElementById('goodwillInfoStatus').textContent = 'Status: ' + (inv.status || '-');
                    document.getElementById('goodwillInfoGrandTotal').textContent = inv.grand_total_fmt || 'Rp 0,00';

                    const isPaid = (inv.paid || '').toUpperCase() === 'Y';
                    const paidElem = document.getElementById('goodwillInfoPaidStatus');
                    paidElem.textContent = isPaid ? 'PAID / LUNAS (Y)' : 'BELUM LUNAS (' + (inv.paid || '-') + ')';
                    paidElem.className = isPaid ? 'text-[11px] font-bold text-emerald-600 mt-1' : 'text-[11px] font-bold text-amber-600 mt-1';
                } else {
                    document.getElementById('goodwillInfoClient').textContent = '-';
                    document.getElementById('goodwillInfoBillDate').textContent = '-';
                    document.getElementById('goodwillInfoInvDate').textContent = '-';
                    document.getElementById('goodwillInfoStatus').textContent = '-';
                    document.getElementById('goodwillInfoGrandTotal').textContent = '-';
                    document.getElementById('goodwillInfoPaidStatus').textContent = '-';
                }

                // Hitung total alokasi
                let totalAlloc = 0;
                payments.forEach(p => {
                    totalAlloc += parseFloat(p.allocation_amt) || 0;
                });
                document.getElementById('goodwillInfoTotalAlloc').textContent = 'Rp ' + totalAlloc.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('goodwillInfoAllocCount').textContent = payments.length + ' Alokasi Pembayaran';

                // Render Tabel Payments
                tbody.innerHTML = '';
                if (payments.length === 0) {
                    empty.classList.remove('hidden');
                    tableWrap.classList.add('hidden');
                } else {
                    empty.classList.add('hidden');
                    tableWrap.classList.remove('hidden');

                    payments.forEach((p, idx) => {
                        const tr = document.createElement('tr');
                        tr.className = 'border-b border-gray-100 hover:bg-gray-50';

                        const isPaidRow = (p.paid || '').toUpperCase() === 'Y';
                        const paidBadge = isPaidRow
                            ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700">Y</span>'
                            : '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">' + (p.paid || '-') + '</span>';

                        tr.innerHTML = `
                            <td class="px-4 py-3 text-center text-gray-400 font-medium">${idx + 1}</td>
                            <td class="px-4 py-3 font-semibold text-gray-800">${p.allocation_no || '-'}</td>
                            <td class="px-4 py-3 text-gray-600">${p.allocation_date || '-'}</td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-600">${p.allocation_amt_fmt || 'Rp 0,00'}</td>
                            <td class="px-4 py-3 font-medium text-gray-700">${p.payment_no || '-'}</td>
                            <td class="px-4 py-3 text-gray-600">${p.payment_date || '-'}</td>
                            <td class="px-4 py-3 text-center">${paidBadge}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                }
            })
            .catch(err => {
                loading.classList.add('hidden');
                content.classList.remove('hidden');
                empty.classList.remove('hidden');
                tableWrap.classList.add('hidden');
                console.error(err);
            });
    };

    window.closeGoodwillPaymentModal = function() {
        const modal = document.getElementById('goodwillPaymentModal');
        if (modal) modal.classList.add('hidden');
    };

    // Close on escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeGoodwillPaymentModal();
        }
    });

    // Close on click outside
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('goodwillPaymentModal');
        if (modal && e.target === modal) {
            closeGoodwillPaymentModal();
        }
    });
</script>

