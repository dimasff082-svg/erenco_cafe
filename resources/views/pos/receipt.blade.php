@extends('layouts.app')

@push('styles')
<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printable-receipt, #printable-receipt * {
        visibility: visible;
    }
    #printable-receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        max-width: 80mm;
        margin: 0 auto;
        padding: 10px;
        box-shadow: none !important;
        border: none !important;
    }
    .no-print {
        display: none !important;
    }
}
</style>
@endpush

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <!-- Top Actions / Nav -->
    <div class="flex items-center justify-between no-print">
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </span>
            <div>
                <h2 class="text-xl font-black text-slate-800">Transaksi Berhasil!</h2>
                <p class="text-xs text-slate-500">Struk digital telah dibuat dan tersimpan aman di sistem.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" id="btnShareWhatsApp" onclick="openWhatsAppReceiptDialog()"
                class="inline-flex items-center gap-2 bg-[#25D366] hover:bg-[#20ba59] active:scale-95 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition cursor-pointer">
                <i class="fa-brands fa-whatsapp text-base"></i>
                <span>Kirim Struk WA</span>
            </button>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Struk
            </button>
            @if(auth()->user()->isCashier() || auth()->user()->isAdmin())
                <a href="{{ route('takeaway.index') }}"
                    class="inline-flex items-center gap-2 bg-[#c59b27] hover:bg-[#a87f1c] text-stone-950 text-xs font-extrabold px-4 py-2.5 rounded-xl shadow-md transition transform active:scale-95">
                    <svg class="w-4 h-4 text-stone-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <span>Pesanan Bawa Pulang Baru</span>
                </a>
            @else
                <a href="{{ route('pos.index') }}"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-md shadow-emerald-600/20 transition transform active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Transaksi Baru
                </a>
            @endif
        </div>
    </div>

    <!-- Digital Receipt Card -->
    <div id="printable-receipt" class="bg-white rounded-3xl border border-slate-200/90 shadow-xl p-8 font-sans max-w-md mx-auto">
        <!-- Store Header -->
        <div class="text-center pb-5 border-b border-dashed border-slate-300">
            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-16 h-16 mx-auto object-contain mb-2 drop-shadow-sm" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}'">
            <h3 class="font-extrabold text-lg text-slate-900 tracking-tight uppercase leading-tight">
                <span>{{ $storeNameLine1 }}</span>
                @if(!empty($storeNameLine2))
                    <span class="block text-sm font-bold text-slate-700 mt-0.5">{{ $storeNameLine2 }}</span>
                @endif
            </h3>
            <p class="text-xs text-slate-500 mt-1">{{ $storeAddress }}</p>
            <p class="text-xs font-semibold text-slate-700 mt-0.5">Telp / WA: {{ $storePhone }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Struk Resmi Pembelian &bull; Jam Buka: {{ $storeHours }}</p>
        </div>

        <!-- Meta Info -->
        @php
            $isOpenBill = (bool) ($sale->open_bill_id && $sale->openBill);
            $receiptSales = $isOpenBill ? $sale->openBill->sales : collect([$sale]);
            $receiptItems = $isOpenBill ? $sale->openBill->sales->flatMap->items : $sale->items;
            $receiptTotal = $isOpenBill ? ($sale->openBill->total_amount > 0 ? $sale->openBill->total_amount : $receiptSales->sum('total_amount')) : $sale->total_amount;
            $receiptSubtotal = $isOpenBill ? $receiptSales->sum('subtotal') : $sale->subtotal;
            $receiptPayment = $sale->payment ?? $sale->openBill?->sales->pluck('payment')->filter()->first();
            $receiptCashier = $sale->openBill?->closer?->name ?? ($sale->openBill?->opener?->name ?? ($sale->confirmer?->name ?? ($sale->user?->name ?? 'Kasir Resto')));
        @endphp

        @if($isOpenBill)
            <div class="mb-3 px-3 py-2 rounded-xl bg-amber-50 border border-amber-200 text-center font-mono text-[11px] text-amber-900">
                <span class="font-bold uppercase tracking-wider block">Nota Tagihan Open Bill Meja</span>
                <span>Meja: <strong>{{ $sale->openBill->table_number }}</strong> &bull; {{ $receiptSales->count() }} Pesanan Tergabung</span>
            </div>
        @endif

        <div class="py-4 border-b border-dashed border-slate-300 text-xs space-y-1.5 font-mono">
            <div class="flex justify-between">
                <span class="text-slate-400">No. {{ $isOpenBill ? 'Bill / Sesi' : 'Transaksi' }}</span>
                <span class="font-bold text-slate-900">{{ $isOpenBill ? $sale->openBill->bill_number : $sale->transaction_number }}</span>
            </div>
            @if($isOpenBill)
                <div class="flex justify-between">
                    <span class="text-slate-400">Daftar Order</span>
                    <span class="text-slate-700 font-bold">{{ $receiptSales->pluck('transaction_number')->implode(', ') }}</span>
                </div>
            @endif
            <div class="flex justify-between">
                <span class="text-slate-400">Tipe Pesanan / Meja</span>
                <span class="text-slate-700 font-bold">
                    @if($sale->table_number === 'TAKEAWAY')
                        <span class="text-purple-700 font-black">Bawa Pulang (Takeaway)</span>
                    @elseif($sale->table_number === 'ONLINE')
                        <span class="text-blue-700 font-black">Pesanan Online</span>
                    @elseif($sale->table_number === 'KIOSK')
                        <span class="text-indigo-700 font-black">Kiosk Mandiri</span>
                    @elseif($sale->table_number)
                        Meja {{ $sale->table_number }}
                    @else
                        Non-Meja
                    @endif
                </span>
            </div>
            @if($sale->customer_name || $sale->openBill?->customer_name)
                <div class="flex justify-between">
                    <span class="text-slate-400">Pelanggan</span>
                    <span class="text-slate-700 font-semibold">{{ $sale->customer_name ?: $sale->openBill->customer_name }}</span>
                </div>
            @endif
            @if($sale->customer_phone)
                <div class="flex justify-between items-center">
                    <span class="text-slate-400">No. HP Pelanggan</span>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-700 font-semibold">{{ $sale->customer_phone }}</span>
                        <button type="button" onclick="openWhatsAppReceiptDialog('{{ $sale->customer_phone }}')"
                            class="no-print inline-flex items-center gap-1 text-[10.5px] font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-2 py-0.5 rounded-lg border border-emerald-300 transition cursor-pointer"
                            title="Kirim Struk ke No. WhatsApp Ini">
                            <i class="fa-brands fa-whatsapp text-emerald-600"></i> Kirim WA
                        </button>
                    </div>
                </div>
            @endif
            <div class="flex justify-between">
                <span class="text-slate-400">Waktu</span>
                <span class="text-slate-700">{{ ($sale->openBill?->closed_at ?? $sale->transaction_date)->format('d/m/Y H:i:s') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Kasir</span>
                <span class="text-slate-700 font-semibold">{{ $receiptCashier }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Status</span>
                <span class="text-emerald-600 font-bold uppercase tracking-wider">Lunas</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="py-4 border-b border-dashed border-slate-300">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-slate-400 text-left font-mono border-b border-slate-100 pb-2">
                        <th class="pb-1.5">Item</th>
                        <th class="pb-1.5 text-center">Qty</th>
                        <th class="pb-1.5 text-right">Harga</th>
                        <th class="pb-1.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($receiptItems as $item)
                        <tr>
                            <td class="py-2 pr-1">
                                <div class="font-bold text-slate-900 leading-tight">{{ $item->product_name_snapshot }}</div>
                                <div class="text-[10px] text-slate-400">{{ $item->sku_snapshot }}</div>
                            </td>
                            <td class="py-2 text-center text-slate-700">{{ $item->quantity }}</td>
                            <td class="py-2 text-right text-slate-600">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="py-2 text-right font-bold text-slate-900">{{ number_format($item->line_total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Totals -->
        <div class="py-4 border-b border-dashed border-slate-300 text-xs font-mono space-y-2">
            <div class="flex justify-between text-slate-600">
                <span>Subtotal Item</span>
                <span>Rp {{ number_format($receiptSubtotal, 0, ',', '.') }}</span>
            </div>

            <div class="flex justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-100">
                <span>TOTAL TAGIHAN</span>
                <span class="text-emerald-700">Rp {{ number_format($receiptTotal, 0, ',', '.') }}</span>
            </div>

            <div class="pt-2 border-t border-slate-100 space-y-1">
                <div class="flex justify-between text-slate-600">
                    <span>Metode Bayar</span>
                    <span class="font-bold uppercase">{{ $receiptPayment?->methodLabel() ?? 'TUNAI' }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Uang Diterima</span>
                    <span>Rp {{ number_format($receiptPayment->amount_tendered ?? $receiptTotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between font-bold text-slate-900">
                    <span>Uang Kembalian</span>
                    <span>Rp {{ number_format($receiptPayment->change_amount ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Thank You Note -->
        <div class="text-center pt-6 space-y-1 text-xs text-slate-400">
            <p class="font-bold text-slate-700">Terima kasih atas kunjungan Anda!</p>
            <p class="text-[11px] font-semibold text-slate-600">Hubungi Toko: {{ $storePhone }}</p>
            <p class="text-[11px]">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan tanpa bukti struk ini.</p>
        </div>
    </div>
</div>

@php
    $receiptLines = [];
    foreach ($receiptItems as $it) {
        $receiptLines[] = "• " . $it->product_name_snapshot . " (" . $it->quantity . "x) : Rp " . number_format($it->line_total, 0, ',', '.');
    }
    $itemsText = implode("\n", $receiptLines);
    $trxNum = $isOpenBill ? $sale->openBill->bill_number : $sale->transaction_number;
    $trxTime = ($sale->openBill?->closed_at ?? $sale->transaction_date)->format('d/m/Y H:i') . ' WIB';
    $custName = $sale->customer_name ?: ($sale->openBill?->customer_name ?: 'Pelanggan');
    $custPhone = $sale->customer_phone ?: ($sale->openBill?->sales->pluck('customer_phone')->filter()->first() ?: '');
    $payMethod = $receiptPayment?->methodLabel() ?? 'TUNAI';

    $defaultWaMessage = "🧾 *STRUK PEMBELIAN - " . strtoupper($storeName) . "*\n"
        . "----------------------------------------\n"
        . "No. Transaksi : " . $trxNum . "\n"
        . "Waktu         : " . $trxTime . "\n"
        . "Pelanggan     : " . $custName . "\n"
        . "Kasir         : " . $receiptCashier . "\n"
        . "Meja / Tipe   : " . ($sale->table_number ? (str_starts_with($sale->table_number, 'Meja') ? $sale->table_number : 'Meja ' . $sale->table_number) : 'Non-Meja') . "\n"
        . "----------------------------------------\n"
        . "*RINCIAN PESANAN:*\n"
        . $itemsText . "\n"
        . "----------------------------------------\n"
        . "*Subtotal*    : Rp " . number_format($receiptSubtotal, 0, ',', '.') . "\n"
        . "*TOTAL BAYAR* : Rp " . number_format($receiptTotal, 0, ',', '.') . "\n"
        . "Metode Bayar  : " . $payMethod . " (Lunas)\n"
        . "----------------------------------------\n"
        . "📸 _Foto bukti fisik struk resmi terlampir di atas._\n\n"
        . "Terima kasih banyak telah berkunjung ke *" . $storeName . "*! Semoga harimu menyenangkan 🙏✨";
@endphp

<!-- MODAL KIRIM STRUK KE WHATSAPP PELANGGAN -->
<div id="whatsappReceiptModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-stone-950/70 backdrop-blur-xs flex items-center justify-center p-4 no-print">
    <div class="relative w-full max-w-md bg-white rounded-3xl border border-[#e5dfd3] shadow-2xl overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150">
        <!-- Close (X) button -->
        <button type="button" onclick="closeWhatsAppReceiptDialog()"
            class="absolute top-4 right-4 w-8 h-8 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 hover:text-stone-800 flex items-center justify-center transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <div class="p-6 space-y-4">
            <!-- Modal Header -->
            <div class="text-center space-y-1">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-200 text-[#25D366] flex items-center justify-center mx-auto text-2xl shadow-xs">
                    <i class="fa-brands fa-whatsapp text-3xl"></i>
                </div>
                <h3 class="font-serif-classic font-extrabold text-lg text-[#26211a]">
                    Kirim Struk ke WhatsApp Pelanggan
                </h3>
                <p class="text-xs text-stone-500">
                    Sistem otomatis menyiapkan <strong>foto struk</strong> dan <strong>rincian teks pesanan</strong> ke nomor WhatsApp pelanggan.
                </p>
            </div>

            <!-- WhatsApp Number Input -->
            <div class="space-y-1.5 text-left">
                <label class="block text-xs font-bold text-stone-700">Nomor WhatsApp Pelanggan *</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-stone-400 select-none">
                        +62
                    </span>
                    <input type="tel" id="waRecipientPhone"
                        placeholder="Contoh: 81234567890 (atau 0812...)"
                        class="w-full pl-12 pr-3.5 py-2.5 rounded-xl border border-stone-300 text-xs font-bold text-stone-900 bg-stone-50 focus:bg-white focus:ring-2 focus:ring-[#25D366] focus:border-[#25D366] focus:outline-none transition">
                </div>
                <p id="waPhoneHelpText" class="text-[11px] text-stone-400">
                    Otomatis mengambil nomor yang diisi pelanggan saat pemesanan keranjang.
                </p>
            </div>

            <!-- Preview Struk Info Box -->
            <div class="bg-[#faf7f0] rounded-2xl p-3.5 border border-[#e5dfd3] text-left text-xs space-y-1.5">
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-stone-400 font-mono">No. Transaksi:</span>
                    <span class="font-mono font-bold text-[#8a681c]">{{ $trxNum }}</span>
                </div>
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-stone-400">Pelanggan:</span>
                    <span class="font-bold text-stone-800">{{ $custName }}</span>
                </div>
                <div class="flex justify-between items-center text-xs pt-1 border-t border-[#ede7dc]">
                    <span class="text-stone-500 font-bold">Total Pembayaran:</span>
                    <span class="font-serif-classic font-extrabold text-[#1b4332] text-sm">
                        Rp {{ number_format($receiptTotal, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Message Preview -->
            <div class="text-left space-y-1">
                <label class="block text-[11px] font-bold text-stone-600 uppercase tracking-wider">
                    Pratinjau Pesan WhatsApp:
                </label>
                <textarea id="waMessagePreview" rows="4"
                    class="w-full p-2.5 rounded-xl border border-stone-200 bg-stone-50 text-[11px] font-mono text-stone-700 leading-relaxed resize-none focus:outline-none select-all"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-3 pt-2">
                <button type="button" onclick="closeWhatsAppReceiptDialog()"
                    class="w-full py-2.5 px-4 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs transition border border-stone-200 cursor-pointer">
                    Batal
                </button>
                <button type="button" id="btnConfirmSendWhatsApp" onclick="executeSendWhatsAppReceipt()"
                    class="w-full py-2.5 px-4 rounded-xl font-bold text-xs transition shadow-xs flex items-center justify-center gap-1.5 text-white bg-[#25D366] hover:bg-[#20ba59] active:scale-95 cursor-pointer">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>Kirim Foto & Pesan</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SUCCESS INSTRUCTION MODAL -->
<div id="waSuccessToastModal" class="fixed inset-0 z-50 hidden bg-stone-950/70 backdrop-blur-xs flex items-center justify-center p-4 no-print">
    <div class="relative w-full max-w-sm bg-white rounded-3xl border border-[#e5dfd3] shadow-2xl p-6 text-center space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center mx-auto text-2xl shadow-xs">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        </div>
        <div>
            <h4 id="waSuccessToastTitle" class="font-serif-classic font-extrabold text-lg text-[#26211a]">Menghubungkan ke WhatsApp...</h4>
            <p id="waSuccessToastMessage" class="text-xs text-stone-600 mt-1 leading-relaxed"></p>
        </div>
        <div class="bg-amber-50 rounded-2xl p-3 border border-amber-200 text-[11px] text-amber-900 text-left space-y-1.5">
            <span class="font-bold block text-amber-950">💡 Panduan Kirim Foto Struk ke Pelanggan:</span>
            <p>1. Foto struk telah <strong>otomatis disalin ke clipboard</strong> dan diunduh.</p>
            <p>2. Pada jendela chat WhatsApp, klik kolom ketik lalu tekan <strong>Ctrl + V</strong> untuk menempelkan foto struk.</p>
            <p>3. Tekan <strong>Enter / Kirim</strong> untuk mengirim foto & pesan ke nomor pelanggan.</p>
        </div>
        <div class="space-y-2 pt-1">
            <a id="btnDirectWaDesktop" href="#"
                class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-brands fa-whatsapp text-sm text-[#25D366]"></i>
                <span>Buka WhatsApp Desktop</span>
            </a>
            <a id="btnDirectWaWeb" href="#" target="_blank"
                class="w-full py-2.5 rounded-xl bg-[#25D366] hover:bg-[#20ba59] text-white font-bold text-xs transition shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Buka WhatsApp Web</span>
            </a>
            <button type="button" onclick="document.getElementById('waSuccessToastModal').classList.add('hidden')"
                class="w-full py-2 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs transition border border-stone-200 cursor-pointer">
                Tutup
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const WA_RECEIPT_CONFIG = {
        storeName: @json($storeName),
        trxNumber: @json($trxNum),
        defaultPhone: @json($custPhone),
        defaultMessage: @json($defaultWaMessage),
    };

    function openWhatsAppReceiptDialog(overridePhone) {
        const phone = overridePhone || WA_RECEIPT_CONFIG.defaultPhone || '';
        document.getElementById('waRecipientPhone').value = cleanDisplayPhone(phone);
        document.getElementById('waMessagePreview').value = WA_RECEIPT_CONFIG.defaultMessage;

        const modal = document.getElementById('whatsappReceiptModal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        if (!phone) {
            setTimeout(() => document.getElementById('waRecipientPhone').focus(), 150);
        }
    }

    function closeWhatsAppReceiptDialog() {
        const modal = document.getElementById('whatsappReceiptModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    function cleanDisplayPhone(phone) {
        if (!phone) return '';
        let cleaned = phone.toString().trim().replace(/[^0-9]/g, '');
        if (cleaned.startsWith('62')) {
            cleaned = cleaned.substring(2);
        } else if (cleaned.startsWith('0')) {
            cleaned = cleaned.substring(1);
        }
        return cleaned;
    }

    function formatInternationalPhone(phone) {
        if (!phone) return '';
        let cleaned = phone.toString().trim().replace(/[^0-9]/g, '');
        if (cleaned.startsWith('0')) {
            return '62' + cleaned.substring(1);
        }
        if (!cleaned.startsWith('62')) {
            return '62' + cleaned;
        }
        return cleaned;
    }

    async function executeSendWhatsAppReceipt() {
        const rawPhone = document.getElementById('waRecipientPhone').value.trim();
        if (!rawPhone) {
            alert('Silakan masukkan nomor WhatsApp pelanggan terlebih dahulu.');
            document.getElementById('waRecipientPhone').focus();
            return;
        }

        const intlPhone = formatInternationalPhone(rawPhone);
        if (intlPhone.length < 9) {
            alert('Nomor WhatsApp tidak valid (terlalu pendek).');
            return;
        }

        const messageText = document.getElementById('waMessagePreview').value.trim();
        const sendBtn = document.getElementById('btnConfirmSendWhatsApp');
        const originalBtnHtml = sendBtn.innerHTML;

        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-sm"></i> Menyiapkan Foto Struk...';

        try {
            const receiptElement = document.getElementById('printable-receipt');
            if (!receiptElement) {
                throw new Error('Elemen struk tidak ditemukan.');
            }

            const h2c = window.html2canvas || html2canvas;
            if (!h2c) {
                throw new Error('Pustaka pembuat foto struk belum termuat.');
            }

            const canvas = await h2c(receiptElement, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
            });

            const filename = 'Struk-' + WA_RECEIPT_CONFIG.trxNumber + '.png';

            canvas.toBlob(async function(blob) {
                if (!blob) {
                    alert('Gagal menghasilkan foto struk.');
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = originalBtnHtml;
                    return;
                }

                const file = new File([blob], filename, { type: 'image/png' });

                // Deteksi apakah perangkat adalah HP / Tablet atau Laptop / Komputer PC
                const ua = navigator.userAgent || '';
                const isMobileOrTablet = (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|Tablet/i.test(ua)
                    || (/Macintosh/i.test(ua) && navigator.maxTouchPoints > 1))
                    && !/Windows NT/i.test(ua);

                const waDesktopUrl = 'whatsapp://send?phone=' + intlPhone + '&text=' + encodeURIComponent(messageText);
                const waWebUrl = 'https://web.whatsapp.com/send?phone=' + intlPhone + '&text=' + encodeURIComponent(messageText);
                const waApiUrl = 'https://api.whatsapp.com/send?phone=' + intlPhone + '&text=' + encodeURIComponent(messageText);

                // =========================================================================
                // 1. JIKA DIAKSES DARI HP ATAU TABLET:
                // Sistem langsung mencari dan membuka aplikasi WhatsApp yang terunduh di HP/tablet
                // =========================================================================
                if (isMobileOrTablet) {
                    // A. Prioritaskan Web Share API jika didukung (melampirkan foto struk langsung ke aplikasi WA)
                    if (navigator.canShare && navigator.canShare({ files: [file] })) {
                        try {
                            await navigator.share({
                                files: [file],
                                title: 'Struk ' + WA_RECEIPT_CONFIG.trxNumber + ' - ' + WA_RECEIPT_CONFIG.storeName,
                                text: messageText,
                            });
                            closeWhatsAppReceiptDialog();
                            sendBtn.disabled = false;
                            sendBtn.innerHTML = originalBtnHtml;
                            return;
                        } catch (shareErr) {
                            if (shareErr.name === 'AbortError') {
                                sendBtn.disabled = false;
                                sendBtn.innerHTML = originalBtnHtml;
                                return;
                            }
                        }
                    }

                    // B. Luncurkan aplikasi WhatsApp di HP melalui skema whatsapp://
                    closeWhatsAppReceiptDialog();
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = originalBtnHtml;

                    window.location.href = waDesktopUrl;

                    // Fallback jika aplikasi belum diunduh di perangkat
                    const mobileStart = Date.now();
                    setTimeout(function() {
                        if (Date.now() - mobileStart < 2500) {
                            window.location.href = waApiUrl;
                        }
                    }, 1500);

                    return;
                }

                // =========================================================================
                // 2. JIKA DIAKSES DARI LAPTOP / KOMPUTER PC (DESKTOP):
                // A. Salin foto struk ke Clipboard & unduh file sebagai cadangan
                // =========================================================================
                try {
                    await navigator.clipboard.write([
                        new ClipboardItem({ 'image/png': blob })
                    ]);
                } catch (clipErr) {
                    console.log('Clipboard write tidak diizinkan:', clipErr);
                }

                try {
                    const dlLink = document.createElement('a');
                    dlLink.download = filename;
                    dlLink.href = canvas.toDataURL('image/png');
                    document.body.appendChild(dlLink);
                    dlLink.click();
                    document.body.removeChild(dlLink);
                } catch (dlErr) {
                    console.log('Download file:', dlErr);
                }

                // B. Buka WhatsApp Desktop terlebih dahulu!
                // Jika WhatsApp Desktop tidak terpasang di laptop, baru otomatis membuka WhatsApp Web
                let desktopAppDetected = false;

                const markAppDetected = function() {
                    desktopAppDetected = true;
                    window.removeEventListener('blur', markAppDetected);
                    document.removeEventListener('visibilitychange', markAppDetected);
                };

                window.addEventListener('blur', markAppDetected);
                document.addEventListener('visibilitychange', markAppDetected);

                // Coba luncurkan WhatsApp Desktop via hidden iframe & direct assign
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = waDesktopUrl;
                document.body.appendChild(iframe);

                try {
                    window.location.assign(waDesktopUrl);
                } catch (assignErr) {}

                // Evaluasi dalam 1.8 detik: jika window tidak blur / document.hidden false
                // (artinya WhatsApp Desktop tidak ada di laptop), buka WhatsApp Web!
                setTimeout(function() {
                    try {
                        if (document.body.contains(iframe)) {
                            document.body.removeChild(iframe);
                        }
                    } catch (e) {}

                    window.removeEventListener('blur', markAppDetected);
                    document.removeEventListener('visibilitychange', markAppDetected);

                    if (!desktopAppDetected && !document.hidden) {
                        console.log('WhatsApp Desktop tidak terpasang. Membuka WhatsApp Web...');
                        window.open(waWebUrl, '_blank');
                    }
                }, 1800);

                closeWhatsAppReceiptDialog();
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalBtnHtml;

                // Tampilkan modal panduan dengan tombol pintasan desktop & web
                const toastTitle = document.getElementById('waSuccessToastTitle');
                if (toastTitle) toastTitle.innerText = 'Menghubungkan ke WhatsApp...';

                const toastMsg = document.getElementById('waSuccessToastMessage');
                if (toastMsg) {
                    toastMsg.innerHTML = 'Sistem mencoba membuka <strong>WhatsApp Desktop</strong>. Jika WhatsApp Desktop tidak terpasang di laptop, <strong>WhatsApp Web</strong> akan terbuka otomatis.';
                }

                const btnDirectDesktop = document.getElementById('btnDirectWaDesktop');
                if (btnDirectDesktop) {
                    btnDirectDesktop.href = waDesktopUrl;
                }

                const btnDirectWeb = document.getElementById('btnDirectWaWeb');
                if (btnDirectWeb) {
                    btnDirectWeb.href = waWebUrl;
                }

                document.getElementById('waSuccessToastModal').classList.remove('hidden');
            }, 'image/png');

        } catch (err) {
            alert('Kendala memproses foto struk: ' + err.message);
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalBtnHtml;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('auto_wa') === '1') {
            openWhatsAppReceiptDialog();
        }
    });

    document.getElementById('whatsappReceiptModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeWhatsAppReceiptDialog();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeWhatsAppReceiptDialog();
            document.getElementById('waSuccessToastModal')?.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection
