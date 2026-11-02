<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\User;
use App\Services\TransactionHistoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request, TransactionHistoryService $historyService): View
    {
        $sales = $historyService->getTransactions($request);
        $summary = $historyService->getTransactionSummary($request);
        $cashiers = User::whereIn('role', ['cashier', 'admin'])->orderBy('name')->get();

        return view('admin.transactions.index', compact('sales', 'cashiers', 'summary'));
    }

    public function show(Sale $sale): View
    {
        $sale->load(['user', 'confirmer', 'payment', 'items.product', 'openBill.sales.items.product']);

        return view('admin.transactions.show', compact('sale'));
    }
}
