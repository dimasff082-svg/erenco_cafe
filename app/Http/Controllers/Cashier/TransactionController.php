<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\TransactionHistoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request, TransactionHistoryService $historyService): View
    {
        $cashierId = $request->user()->isCashier() ? $request->user()->id : null;
        $sales = $historyService->getTransactions($request, $cashierId);
        $summary = $historyService->getTransactionSummary($request, $cashierId);

        return view('cashier.transactions.index', compact('sales', 'summary'));
    }

    public function show(Request $request, Sale $sale): View
    {
        // Kasir hanya boleh membuka transaksi yang dilayani atau dikonfirmasinya sendiri
        if ($request->user()->isCashier()) {
            $isServed = ($sale->user_id === $request->user()->id || $sale->confirmed_by === $request->user()->id);
            if (! $isServed && $sale->open_bill_id) {
                $sale->loadMissing('openBill.sales');
                if ($sale->openBill) {
                    $isServed = ($sale->openBill->opened_by === $request->user()->id
                        || $sale->openBill->closed_by === $request->user()->id
                        || $sale->openBill->sales->contains(fn ($s) => $s->user_id === $request->user()->id || $s->confirmed_by === $request->user()->id));
                }
            }

            if (! $isServed) {
                abort(403, 'Anda tidak memiliki hak untuk melihat transaksi ini karena dilayani oleh kasir lain.');
            }
        } elseif (! $request->user()->isAdmin() && ! $request->user()->isOwner()) {
            abort(403, 'Anda tidak memiliki hak untuk melihat transaksi ini.');
        }

        $sale->load(['user', 'confirmer', 'payment', 'items.product', 'openBill.sales.items.product']);

        return view('cashier.transactions.show', compact('sale'));
    }
}
