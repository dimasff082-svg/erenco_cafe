<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\RestaurantTableController as AdminRestaurantTableController;
use App\Http\Controllers\Admin\SalaryController as AdminSalaryController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Cashier\DashboardController as CashierDashboardController;
use App\Http\Controllers\Cashier\MenuController as CashierMenuController;
use App\Http\Controllers\Cashier\OpenBillController as CashierOpenBillController;
use App\Http\Controllers\Cashier\TransactionController as CashierTransactionController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\EmployeeSalaryController;
use App\Http\Controllers\KioskOrderController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\OrderCancellationController as OwnerOrderCancellationController;
use App\Http\Controllers\Owner\ProfitLossController as OwnerProfitLossController;
use App\Http\Controllers\Owner\ReportController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\TakeawayOrderController;
use App\Http\Controllers\Warehouse\ExpenseController as WarehouseExpenseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Redirect root to login or appropriate dashboard
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->isOwner()) {
            return redirect()->route('owner.dashboard');
        }
        if (auth()->user()->isKitchen()) {
            return redirect()->route('kitchen.index');
        }
        if (auth()->user()->isWarehouse()) {
            return redirect()->route('warehouse.expenses.index');
        }

        return redirect()->route('cashier.dashboard');
    }

    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Customer Self-Order via Table QR Code (Public / Guest)
Route::prefix('order')->name('order.')->group(function () {
    Route::get('/{table_number}', [CustomerOrderController::class, 'index'])->name('index');
    Route::post('/{table_number}/checkout', [CustomerOrderController::class, 'checkout'])->name('checkout');
    Route::post('/{table_number}/request-open-bill', [CustomerOrderController::class, 'requestOpenBill'])->name('request_open_bill');
    Route::post('/{table_number}/cancel-open-bill-request', [CustomerOrderController::class, 'cancelOpenBillRequest'])->name('cancel_open_bill_request');
    Route::post('/{table_number}/open-bill/snap-token', [CustomerOrderController::class, 'getOpenBillSnapToken'])->name('open_bill_snap_token');
    Route::post('/{table_number}/open-bill/midtrans-success', [CustomerOrderController::class, 'markOpenBillMidtransSuccess'])->name('open_bill_midtrans_success');
    Route::get('/{table_number}/status/{sale}', [CustomerOrderController::class, 'status'])->name('status');
    Route::post('/{table_number}/status/{sale}/midtrans-success', [CustomerOrderController::class, 'markMidtransSuccess'])->name('midtrans_success');
    Route::post('/{table_number}/status/{sale}/snap-token', [CustomerOrderController::class, 'getSnapToken'])->name('snap_token');
});
Route::post('/api/midtrans/notification', [CustomerOrderController::class, 'midtransNotification'])->name('midtrans.notification');
Route::get('/meja/{table_number}', fn ($table_number) => redirect()->route('order.index', $table_number));

// Customer Self-Ordering Kiosk (Layar Pemesanan Mandiri di Toko - Akses langsung via URL tanpa link navigasi)
Route::prefix('kiosk')->name('kiosk.')->group(function () {
    Route::get('/', [KioskOrderController::class, 'index'])->name('index');
    Route::post('/checkout', [KioskOrderController::class, 'checkout'])->name('checkout');
    Route::get('/receipt/{sale}', [KioskOrderController::class, 'receipt'])->name('receipt');
});

// Customer Online Ordering (Pemesanan Online via Link yang dibagikan Kasir - Public & Mobile-First)
Route::prefix('online-order')->name('online_order.')->group(function () {
    Route::get('/', [OnlineOrderController::class, 'index'])->name('index');
    Route::post('/checkout', [OnlineOrderController::class, 'checkout'])->name('checkout');
    Route::get('/status/{transaction_number}', [OnlineOrderController::class, 'status'])->name('status');
    Route::post('/{sale}/midtrans-success', [OnlineOrderController::class, 'markMidtransSuccess'])->name('midtrans_success');
    Route::post('/{sale}/snap-token', [OnlineOrderController::class, 'getSnapToken'])->name('snap_token');
});
Route::get('/pesan-online', fn () => redirect()->route('online_order.index'));

// Cashier & Terminal Routes (Customer Order Management & Payment Confirmation)
Route::middleware(['auth', 'role:cashier,admin,owner'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', [CashierDashboardController::class, 'index'])->name('dashboard');
    Route::post('/orders/{sale}/confirm-payment', [CashierDashboardController::class, 'confirmPayment'])->name('orders.confirm_payment');

    // Open Bill Management (Khusus Pengelolaan Tagihan Meja)
    Route::get('/open-bills', [CashierOpenBillController::class, 'index'])->name('open_bills.index');
    Route::post('/open-bills', [CashierOpenBillController::class, 'store'])->name('open_bills.store');
    Route::post('/open-bills/{openBill}/approve', [CashierOpenBillController::class, 'approve'])->name('open_bills.approve');
    Route::post('/open-bills/{openBill}/reject', [CashierOpenBillController::class, 'reject'])->name('open_bills.reject');
    Route::get('/open-bills/{openBill}', [CashierOpenBillController::class, 'show'])->name('open_bills.show');
    Route::post('/open-bills/{openBill}/confirm-payment', [CashierOpenBillController::class, 'confirmPayment'])->name('open_bills.confirm_payment');

    Route::get('/transactions', [CashierTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{sale}', [CashierTransactionController::class, 'show'])->name('transactions.show');

    // Data Menu / Katalog Menu (Read-Only untuk Kasir Menjelaskan Menu ke Pelanggan)
    Route::get('/menu', [CashierMenuController::class, 'index'])->name('menu.index');
});

// Kitchen Display System Routes (Dapur - Kelola Status Pesanan: Menunggu -> Diproses -> Selesai)
Route::middleware(['auth', 'role:kitchen,admin'])->prefix('kitchen')->name('kitchen.')->group(function () {
    Route::get('/', [KitchenController::class, 'index'])->name('index');
    Route::patch('/orders/{sale}/status', [KitchenController::class, 'updateStatus'])->name('orders.status');
});

// Takeaway Routes (Pesanan Bawa Pulang Langsung di Kasir & Admin)
Route::middleware(['auth', 'role:cashier,admin'])->prefix('takeaway')->name('takeaway.')->group(function () {
    Route::get('/', [TakeawayOrderController::class, 'index'])->name('index');
    Route::post('/checkout', [TakeawayOrderController::class, 'checkout'])->name('checkout');
});

// POS Routes (Kasir diarahkan ke Dashboard Kasir)
Route::middleware(['auth'])->group(function () {
    Route::get('/pos', function (Request $request) {
        if ($request->user()->isCashier()) {
            return redirect()->route('cashier.dashboard')
                ->with('info', 'Tugas Kasir telah disederhanakan: pelanggan memesan mandiri via QR Code meja, Kasir bertugas mengonfirmasi pembayaran.');
        }

        return app(PosController::class)->index($request);
    })->name('pos.index');

    Route::get('/pos/search', [PosController::class, 'search'])->name('pos.search');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/pos/receipt/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');
});

// Admin Routes (Strict Admin Only)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::patch('/categories/{category}/toggle', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle');
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

    // Products
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
    Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/toggle', [AdminProductController::class, 'toggleStatus'])->name('products.toggle');
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    // Transactions History & Inspection
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{sale}', [AdminTransactionController::class, 'show'])->name('transactions.show');

    // Sales Reports & Analytics
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-excel', [AdminReportController::class, 'exportExcel'])->name('reports.export_excel');
    Route::get('/reports/export-csv', [AdminReportController::class, 'exportCsv'])->name('reports.export_csv');

    // User Management
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/toggle', [AdminUserController::class, 'toggleStatus'])->name('users.toggle');

    // Restaurant Tables Management & QR Code Print
    Route::get('/tables', [AdminRestaurantTableController::class, 'index'])->name('tables.index');
    Route::post('/tables', [AdminRestaurantTableController::class, 'store'])->name('tables.store');
    Route::put('/tables/{table}', [AdminRestaurantTableController::class, 'update'])->name('tables.update');
    Route::delete('/tables/{table}', [AdminRestaurantTableController::class, 'destroy'])->name('tables.destroy');
    Route::get('/tables/{table}/print-qr', [AdminRestaurantTableController::class, 'printQr'])->name('tables.print_qr');
});

// Admin & Owner Shared Management Routes (Monitoring Presensi & Penggajian Karyawan)
Route::middleware(['auth', 'role:admin,owner'])->prefix('admin')->name('admin.')->group(function () {
    // Monitoring Rekap Presensi Karyawan
    Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');

    // Penggajian Karyawan
    Route::get('/salaries', [AdminSalaryController::class, 'index'])->name('salaries.index');
    Route::post('/salaries', [AdminSalaryController::class, 'store'])->name('salaries.store');
    Route::put('/salaries/{salary}', [AdminSalaryController::class, 'update'])->name('salaries.update');
    Route::delete('/salaries/{salary}', [AdminSalaryController::class, 'destroy'])->name('salaries.destroy');
    Route::get('/salaries/{salary}/print', [AdminSalaryController::class, 'print'])->name('salaries.print');
});

// Owner Routes (Role Pemilik)
Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{sale}', [AdminTransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{sale}/cancel', [OwnerOrderCancellationController::class, 'cancel'])->name('transactions.cancel');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export_excel');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export_csv');
    Route::get('/reports/profit-loss', [OwnerProfitLossController::class, 'index'])->name('profit_loss.index');
    Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/salaries', [AdminSalaryController::class, 'index'])->name('salaries.index');
    Route::post('/salaries', [AdminSalaryController::class, 'store'])->name('salaries.store');
    Route::put('/salaries/{salary}', [AdminSalaryController::class, 'update'])->name('salaries.update');
    Route::delete('/salaries/{salary}', [AdminSalaryController::class, 'destroy'])->name('salaries.destroy');
    Route::get('/salaries/{salary}/print', [AdminSalaryController::class, 'print'])->name('salaries.print');
});

// Warehouse Staff Routes (Staf Gudang & Admin/Owner)
Route::middleware(['auth', 'role:warehouse,admin,owner'])->prefix('warehouse')->name('warehouse.')->group(function () {
    // Satu Halaman Transaksi Pengeluaran (Bahan Baku & Operasional)
    Route::get('/expenses', [WarehouseExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [WarehouseExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}', [WarehouseExpenseController::class, 'destroy'])->name('expenses.destroy');
});

// Presensi Karyawan Mandiri (Kasir & Staf Warehouse)
Route::middleware(['auth', 'role:cashier,warehouse,admin,owner'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::post('/', [AttendanceController::class, 'store'])->name('store');
});

// Slip Gaji Karyawan Mandiri (Kasir & Staf Warehouse)
Route::middleware(['auth', 'role:cashier,warehouse,admin,owner'])->prefix('my-salaries')->name('my_salaries.')->group(function () {
    Route::get('/', [EmployeeSalaryController::class, 'index'])->name('index');
    Route::get('/{salary}/print', [EmployeeSalaryController::class, 'print'])->name('print');
});
