<?php

namespace App\Support;

use App\Models\OpenBill;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UnifiedTransactionRecord
{
    /**
     * ID numerik dari Sale (atau Sale utama pada Open Bill) untuk kompatibilitas route model binding.
     */
    public int $id;

    /**
     * ID unik string untuk elemen DOM / JavaScript Accordion (contoh: 'sale_1' atau 'ob_2').
     */
    public string $accordion_id;

    public int $raw_id;

    public string $type; // 'sale' atau 'open_bill'

    public bool $is_open_bill;

    public bool $is_takeaway = false;

    public string $badge_label;

    public string $transaction_number;

    public Carbon $transaction_date;

    public ?string $table_number = null;

    public ?string $customer_name = null;

    public ?string $cashier_name = null;

    public ?User $user = null;

    public ?User $confirmer = null;

    public ?Payment $payment = null;

    public Collection $items;

    public Collection $orders;

    public int $orders_count = 1;

    public float $subtotal = 0;

    public float $discount_amount = 0;

    public float $total_amount = 0;

    public string $status = 'completed';

    public string $payment_status = 'paid';

    public ?string $notes = null;

    public string $receipt_url = '#';

    public ?Sale $sale = null;

    public ?OpenBill $openBill = null;

    public static function fromSale(Sale $sale): self
    {
        $record = new self;
        $record->id = $sale->id;
        $record->accordion_id = 'sale_'.$sale->id;
        $record->raw_id = $sale->id;
        $record->type = 'sale';
        $record->is_open_bill = false;
        $record->is_takeaway = ($sale->table_number === 'TAKEAWAY');
        $record->badge_label = $record->is_takeaway ? 'Takeaway' : ($sale->table_number === 'KIOSK' ? 'Kiosk Mandiri' : 'Reguler');
        $record->transaction_number = $sale->transaction_number;
        $record->transaction_date = $sale->transaction_date ?? ($sale->created_at ?? now());
        $record->table_number = $record->is_takeaway ? 'Bawa Pulang (Takeaway)' : ($sale->table_number === 'KIOSK' ? 'Layar Mandiri (Kiosk)' : $sale->table_number);
        $record->customer_name = $sale->customer_name;
        $record->cashier_name = $sale->confirmer?->name ?? ($sale->user?->name ?? 'Kasir');
        $record->user = $sale->user;
        $record->confirmer = $sale->confirmer;
        $record->payment = $sale->payment;
        $record->items = $sale->items ?? collect();
        $record->orders = collect([$sale]);
        $record->orders_count = 1;
        $record->subtotal = (float) $sale->subtotal;
        $record->discount_amount = (float) $sale->discount_amount;
        $record->total_amount = (float) $sale->total_amount;
        $record->status = $sale->status;
        $record->payment_status = $sale->payment_status;
        $record->notes = $sale->notes;
        $record->receipt_url = route('pos.receipt', $sale->id);
        $record->sale = $sale;
        $record->openBill = null;

        return $record;
    }

    public static function fromOpenBill(OpenBill $openBill): self
    {
        $record = new self;
        $primarySale = $openBill->sales->first();

        $record->id = $primarySale?->id ?? $openBill->id;
        $record->accordion_id = 'ob_'.$openBill->id;
        $record->raw_id = $openBill->id;
        $record->type = 'open_bill';
        $record->is_open_bill = true;
        $record->badge_label = 'Open Bill';
        $record->transaction_number = $openBill->bill_number;
        $record->transaction_date = $openBill->closed_at ?? ($openBill->opened_at ?? ($openBill->created_at ?? now()));
        $record->table_number = $openBill->table_number;

        $customerName = $openBill->customer_name;
        if (! $customerName && $openBill->sales->isNotEmpty()) {
            $customerName = $openBill->sales->pluck('customer_name')->filter()->first();
        }
        $record->customer_name = $customerName;

        // Nama Kasir yang melayani / menutup sesi Open Bill
        $cashierName = $openBill->closer?->name ?? ($openBill->opener?->name ?? null);
        if (! $cashierName && $openBill->sales->isNotEmpty()) {
            $cashierName = $openBill->sales->pluck('confirmer.name')->filter()->first()
                ?? $openBill->sales->pluck('user.name')->filter()->first();
        }
        $record->cashier_name = $cashierName ?: 'Kasir';
        $record->user = $openBill->closer ?? ($openBill->opener ?? $primarySale?->user);
        $record->confirmer = $openBill->closer ?? $primarySale?->confirmer;

        // Gabungkan seluruh item dari semua pesanan dalam Open Bill ini
        $allItems = collect();
        $allOrders = $openBill->sales;
        foreach ($allOrders as $order) {
            foreach ($order->items as $item) {
                $allItems->push($item);
            }
        }
        $record->items = $allItems;
        $record->orders = $allOrders;
        $record->orders_count = $allOrders->count();

        // Akumulasi total dan subtotal
        $record->subtotal = (float) ($openBill->sales->sum('subtotal') ?: $openBill->total_amount);
        $record->discount_amount = (float) $openBill->sales->sum('discount_amount');
        $record->total_amount = (float) ($openBill->total_amount > 0 ? $openBill->total_amount : $openBill->sales->sum('total_amount'));

        // Ambil data pembayaran
        $payment = $openBill->sales->pluck('payment')->filter()->first();
        $record->payment = $payment;

        $record->status = $openBill->status;
        $record->payment_status = $openBill->status === 'paid' ? 'paid' : ($openBill->sales->every(fn ($s) => $s->payment_status === 'paid') ? 'paid' : 'unpaid');
        $record->notes = $openBill->notes;

        $record->receipt_url = $primarySale ? route('pos.receipt', $primarySale->id) : '#';
        $record->sale = $primarySale;
        $record->openBill = $openBill;

        return $record;
    }

    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->$name);
    }
}
