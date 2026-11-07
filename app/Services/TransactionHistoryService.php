<?php

namespace App\Services;

use App\Models\OpenBill;
use App\Models\Sale;
use App\Support\UnifiedTransactionRecord;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TransactionHistoryService
{
    /**
     * Dapatkan daftar riwayat transaksi terpadu (menggabungkan pesanan Open Bill dalam 1 riwayat).
     *
     * @param  int|null  $onlyCashierId  Jika diisi, batasi hanya transaksi yang dilayani/dikonfirmasi oleh kasir tersebut.
     */
    public function getTransactions(Request $request, ?int $onlyCashierId = null, int $perPage = 15): LengthAwarePaginator
    {
        [$salesQuery, $openBillsQuery] = $this->buildBaseQueries($request, $onlyCashierId);

        // Gabungkan kedua subquery dengan UNION ALL
        $unionQuery = $salesQuery->unionAll($openBillsQuery);

        $paginator = DB::query()
            ->fromSub($unionQuery, 'combined_transactions')
            ->orderByDesc('sort_date')
            ->paginate($perPage)
            ->withQueryString();

        // Kumpulkan ID untuk eager loading yang efisien
        $saleIds = [];
        $openBillIds = [];

        foreach ($paginator->items() as $item) {
            if ($item->record_type === 'sale') {
                $saleIds[] = $item->record_id;
            } else {
                $openBillIds[] = $item->record_id;
            }
        }

        $sales = ! empty($saleIds)
            ? Sale::with(['user', 'confirmer', 'canceller', 'payment', 'items.product'])->whereIn('id', $saleIds)->get()->keyBy('id')
            : collect();

        $openBills = ! empty($openBillIds)
            ? OpenBill::with(['opener', 'closer', 'sales.user', 'sales.confirmer', 'sales.canceller', 'sales.payment', 'sales.items.product'])
                ->whereIn('id', $openBillIds)
                ->get()
                ->keyBy('id')
            : collect();

        $unifiedRecords = collect();

        foreach ($paginator->items() as $item) {
            if ($item->record_type === 'sale' && isset($sales[$item->record_id])) {
                $unifiedRecords->push(UnifiedTransactionRecord::fromSale($sales[$item->record_id]));
            } elseif ($item->record_type === 'open_bill' && isset($openBills[$item->record_id])) {
                $unifiedRecords->push(UnifiedTransactionRecord::fromOpenBill($openBills[$item->record_id]));
            }
        }

        $paginator->setCollection($unifiedRecords);

        return $paginator;
    }

    /**
     * Hitung ringkasan akumulasi transaksi: total transaksi & total uang masuk.
     *
     * @return array{total_transactions: int, total_revenue: float}
     */
    public function getTransactionSummary(Request $request, ?int $onlyCashierId = null): array
    {
        [$salesQuery, $openBillsQuery] = $this->buildBaseQueries($request, $onlyCashierId);
        $unionQuery = $salesQuery->unionAll($openBillsQuery);

        $summary = DB::query()
            ->fromSub($unionQuery, 'combined_transactions')
            ->selectRaw('COUNT(*) as total_count, COALESCE(SUM(paid_amount), 0) as total_revenue')
            ->first();

        return [
            'total_transactions' => (int) ($summary->total_count ?? 0),
            'total_revenue' => (float) ($summary->total_revenue ?? 0),
        ];
    }

    /**
     * Membangun subquery dasar untuk transaksi Sale mandiri dan Open Bill.
     */
    protected function buildBaseQueries(Request $request, ?int $onlyCashierId = null): array
    {
        // 1. Query Pesanan Mandiri / Reguler (tanpa Open Bill)
        $salesQuery = DB::table('sales')
            ->selectRaw("'sale' as record_type, id as record_id, transaction_date as sort_date, total_amount, (CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END) as paid_amount")
            ->whereNull('open_bill_id');

        // 2. Query Open Bill yang memiliki pesanan
        $openBillsQuery = DB::table('open_bills')
            ->selectRaw("
                'open_bill' as record_type,
                id as record_id,
                COALESCE(closed_at, opened_at, created_at) as sort_date,
                COALESCE(NULLIF(open_bills.total_amount, 0), (SELECT COALESCE(SUM(sales.total_amount), 0) FROM sales WHERE sales.open_bill_id = open_bills.id), 0) as total_amount,
                (CASE WHEN open_bills.status = 'paid' THEN COALESCE(NULLIF(open_bills.total_amount, 0), (SELECT COALESCE(SUM(sales.total_amount), 0) FROM sales WHERE sales.open_bill_id = open_bills.id), 0) ELSE 0 END) as paid_amount
            ")
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('sales')
                    ->whereColumn('sales.open_bill_id', 'open_bills.id');
            });

        // Scoping Kasir (hanya transaksi yang dilayaninya / dikonfirmasinya sendiri)
        if ($onlyCashierId !== null) {
            $salesQuery->where('payment_status', 'paid')
                ->where(function ($q) use ($onlyCashierId) {
                    $q->where('user_id', $onlyCashierId)
                        ->orWhere('confirmed_by', $onlyCashierId);
                });

            $openBillsQuery->where('status', 'paid')
                ->where(function ($q) use ($onlyCashierId) {
                    $q->where('opened_by', $onlyCashierId)
                        ->orWhere('closed_by', $onlyCashierId)
                        ->orWhereExists(function ($sub) use ($onlyCashierId) {
                            $sub->select(DB::raw(1))
                                ->from('sales')
                                ->whereColumn('sales.open_bill_id', 'open_bills.id')
                                ->where(function ($s) use ($onlyCashierId) {
                                    $s->where('sales.user_id', $onlyCashierId)
                                        ->orWhere('sales.confirmed_by', $onlyCashierId);
                                });
                        });
                });
        }

        // Filter: Hari Ini (Today)
        if ($request->input('period') === 'today') {
            $today = now()->toDateString();
            $salesQuery->whereDate('transaction_date', $today);
            $openBillsQuery->where(function ($q) use ($today) {
                $q->whereDate('closed_at', $today)
                    ->orWhere(function ($q2) use ($today) {
                        $q2->whereNull('closed_at')->whereDate('opened_at', $today);
                    });
            });
        } else {
            // Filter Rentang Tanggal
            if ($request->filled('date_from')) {
                $dateFrom = $request->input('date_from');
                $salesQuery->whereDate('transaction_date', '>=', $dateFrom);
                $openBillsQuery->where(function ($q) use ($dateFrom) {
                    $q->whereDate('closed_at', '>=', $dateFrom)
                        ->orWhere(function ($q2) use ($dateFrom) {
                            $q2->whereNull('closed_at')->whereDate('opened_at', '>=', $dateFrom);
                        });
                });
            }

            if ($request->filled('date_to')) {
                $dateTo = $request->input('date_to');
                $salesQuery->whereDate('transaction_date', '<=', $dateTo);
                $openBillsQuery->where(function ($q) use ($dateTo) {
                    $q->whereDate('closed_at', '<=', $dateTo)
                        ->orWhere(function ($q2) use ($dateTo) {
                            $q2->whereNull('closed_at')->whereDate('opened_at', '<=', $dateTo);
                        });
                });
            }
        }

        // Filter Kasir / User (Admin & Owner)
        if ($onlyCashierId === null && $request->filled('user_id')) {
            $userId = (int) $request->input('user_id');
            $salesQuery->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhere('confirmed_by', $userId);
            });

            $openBillsQuery->where(function ($q) use ($userId) {
                $q->where('opened_by', $userId)
                    ->orWhere('closed_by', $userId)
                    ->orWhereExists(function ($sub) use ($userId) {
                        $sub->select(DB::raw(1))
                            ->from('sales')
                            ->whereColumn('sales.open_bill_id', 'open_bills.id')
                            ->where(function ($s) use ($userId) {
                                $s->where('sales.user_id', $userId)
                                    ->orWhere('sales.confirmed_by', $userId);
                            });
                    });
            });
        }

        // Filter Metode Pembayaran
        if ($request->filled('payment_method')) {
            $method = $request->input('payment_method');
            $salesQuery->whereExists(function ($q) use ($method) {
                $q->select(DB::raw(1))
                    ->from('payments')
                    ->whereColumn('payments.sale_id', 'sales.id')
                    ->where('payments.method', $method);
            });

            $openBillsQuery->whereExists(function ($q) use ($method) {
                $q->select(DB::raw(1))
                    ->from('sales')
                    ->join('payments', 'payments.sale_id', '=', 'sales.id')
                    ->whereColumn('sales.open_bill_id', 'open_bills.id')
                    ->where('payments.method', $method);
            });
        }

        // Filter Status Transaksi (Lunas/Selesai, Dibatalkan, Belum Bayar)
        if ($request->filled('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'cancelled') {
                $salesQuery->where(function ($q) {
                    $q->where('status', 'cancelled')
                        ->orWhere('payment_status', 'cancelled');
                });

                $openBillsQuery->where(function ($q) {
                    $q->where('status', 'cancelled')
                        ->orWhereExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('sales')
                                ->whereColumn('sales.open_bill_id', 'open_bills.id')
                                ->where(function ($s) {
                                    $s->where('sales.status', 'cancelled')
                                        ->orWhere('sales.payment_status', 'cancelled');
                                });
                        });
                });
            } elseif ($status === 'paid' || $status === 'completed') {
                $salesQuery->where('status', '!=', 'cancelled')
                    ->where('payment_status', 'paid');

                $openBillsQuery->where('status', 'paid');
            } elseif ($status === 'unpaid' || $status === 'pending') {
                $salesQuery->where('status', '!=', 'cancelled')
                    ->where('payment_status', '!=', 'paid');

                $openBillsQuery->where('status', '!=', 'paid');
            }
        }

        // Filter Pencarian (No. Transaksi / Bill, Meja, Nama Pelanggan)
        if ($search = $request->input('search')) {
            $salesQuery->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('table_number', 'like', "%{$search}%");
            });

            $openBillsQuery->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('table_number', 'like', "%{$search}%")
                    ->orWhereExists(function ($sub) use ($search) {
                        $sub->select(DB::raw(1))
                            ->from('sales')
                            ->whereColumn('sales.open_bill_id', 'open_bills.id')
                            ->where('sales.transaction_number', 'like', "%{$search}%");
                    });
            });
        }

        return [$salesQuery, $openBillsQuery];
    }
}
