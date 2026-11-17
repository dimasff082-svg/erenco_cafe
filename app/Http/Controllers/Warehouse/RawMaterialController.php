<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\RawMaterial;
use App\Models\RawMaterialMutation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RawMaterialController extends Controller
{
    /**
     * Katalog dan dashboard inventori bahan baku.
     */
    public function index(Request $request): View
    {
        $query = RawMaterial::query()->orderBy('name');

        // Filter Pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter Kategori
        if ($category = $request->input('category')) {
            if ($category !== 'all') {
                $query->where('category', $category);
            }
        }

        // Filter Status Stok
        if ($status = $request->input('status')) {
            if ($status === 'out_of_stock') {
                $query->where('current_stock', '<=', 0);
            } elseif ($status === 'low_stock') {
                $query->where('current_stock', '>', 0)
                    ->whereColumn('current_stock', '<=', 'min_stock');
            } elseif ($status === 'safe') {
                $query->whereColumn('current_stock', '>', 'min_stock');
            }
        }

        $rawMaterials = $query->paginate(15)->withQueryString();

        // Statistik KPI Utama
        $allMaterials = RawMaterial::all();
        $summary = [
            'total_items' => $allMaterials->count(),
            'total_asset_value' => $allMaterials->sum(fn (RawMaterial $item) => $item->totalAssetValue()),
            'low_stock_count' => $allMaterials->filter(fn (RawMaterial $item) => $item->isLowStock())->count(),
            'out_of_stock_count' => $allMaterials->filter(fn (RawMaterial $item) => $item->isOutOfStock())->count(),
        ];

        // Daftar kategori untuk filter dropdown
        $categories = RawMaterial::query()
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter();

        return view('warehouse.raw_materials.index', compact('rawMaterials', 'summary', 'categories'));
    }

    /**
     * Tambah master bahan baku baru.
     */
    public function store(Request $request): RedirectResponse
    {
        // Bersihkan tanda titik pada angka rupiah jika ada
        if ($request->has('unit_price')) {
            $cleanPrice = preg_replace('/[^\d]/', '', (string) $request->input('unit_price'));
            $request->merge(['unit_price' => $cleanPrice !== '' ? (float) $cleanPrice : 0]);
        }

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:50', 'unique:raw_materials,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'initial_stock' => ['nullable', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama bahan baku wajib diisi.',
            'category.required' => 'Kategori bahan baku wajib dipilih atau diisi.',
            'unit.required' => 'Satuan bahan baku wajib diisi.',
            'min_stock.required' => 'Batas stok minimum peringatan wajib diisi.',
        ]);

        // Auto-generate kode jika tidak diisi: BHN-001, BHN-002, dst.
        $code = $validated['code'] ?? null;
        if (! $code) {
            $lastId = (int) (RawMaterial::max('id') ?? 0) + 1;
            $code = sprintf('BHN-%03d', $lastId);
            while (RawMaterial::where('code', $code)->exists()) {
                $lastId++;
                $code = sprintf('BHN-%03d', $lastId);
            }
        }

        $initialStock = (float) ($validated['initial_stock'] ?? 0);

        $rawMaterial = RawMaterial::create([
            'code' => $code,
            'name' => $validated['name'],
            'category' => $validated['category'],
            'unit' => $validated['unit'],
            'current_stock' => 0, // di-set lewat mutation jika initialStock > 0
            'min_stock' => $validated['min_stock'],
            'unit_price' => $validated['unit_price'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($initialStock > 0) {
            $rawMaterial->recordMutation(
                type: 'in',
                quantity: $initialStock,
                referenceNumber: 'SALDO-AWAL',
                notes: 'Saldo stok awal saat pendaftaran bahan baku baru',
                userId: $request->user()->id
            );
        }

        return redirect()->route('warehouse.raw_materials.index')
            ->with('success', "Bahan baku {$rawMaterial->name} ({$rawMaterial->code}) berhasil ditambahkan.");
    }

    /**
     * Perbarui data master bahan baku.
     */
    public function update(Request $request, RawMaterial $rawMaterial): RedirectResponse
    {
        if ($request->has('unit_price')) {
            $cleanPrice = preg_replace('/[^\d]/', '', (string) $request->input('unit_price'));
            $request->merge(['unit_price' => $cleanPrice !== '' ? (float) $cleanPrice : 0]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:50'],
            'min_stock' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $rawMaterial->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'unit' => $validated['unit'],
            'min_stock' => $validated['min_stock'],
            'unit_price' => $validated['unit_price'] ?? $rawMaterial->unit_price,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('warehouse.raw_materials.index')
            ->with('success', "Data bahan baku {$rawMaterial->name} berhasil diperbarui.");
    }

    /**
     * Hapus bahan baku.
     */
    public function destroy(RawMaterial $rawMaterial): RedirectResponse
    {
        $name = $rawMaterial->name;
        $rawMaterial->delete();

        return redirect()->route('warehouse.raw_materials.index')
            ->with('success', "Bahan baku {$name} berhasil dihapus.");
    }

    /**
     * Restock Cepat (+): Tambah stok bahan masuk mandiri.
     */
    public function restock(Request $request, RawMaterial $rawMaterial): RedirectResponse
    {
        if ($request->has('unit_price') && $request->filled('unit_price')) {
            $cleanPrice = preg_replace('/[^\d]/', '', (string) $request->input('unit_price'));
            $request->merge(['unit_price' => $cleanPrice !== '' ? (float) $cleanPrice : null]);
        }

        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'quantity.required' => 'Jumlah kuantitas restock wajib diisi.',
            'quantity.min' => 'Jumlah kuantitas restock minimal 0.01.',
        ]);

        $qty = (float) $validated['quantity'];
        $ref = ($validated['reference_number'] ?? null) ?: ('RESTOCK-'.now()->format('YmdHis'));
        $notes = ($validated['notes'] ?? null) ?: 'Restock fisik mandiri di gudang/bar';

        $rawMaterial->recordMutation(
            type: 'in',
            quantity: $qty,
            referenceNumber: $ref,
            notes: $notes,
            userId: $request->user()->id
        );

        if (! empty($validated['unit_price']) && (float) $validated['unit_price'] > 0) {
            $rawMaterial->update(['unit_price' => (float) $validated['unit_price']]);
        }

        return redirect()->route('warehouse.raw_materials.index')
            ->with('success', "Berhasil menambahkan stok {$qty} {$rawMaterial->unit} untuk {$rawMaterial->name}.");
    }

    /**
     * Catat Pemakaian Bar/Dapur atau Bahan Rusak (-).
     */
    public function usage(Request $request, RawMaterial $rawMaterial): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'usage_type' => ['required', 'string', 'in:bar_usage,kitchen_usage,damaged,spoilage,other'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['required', 'string', 'max:500'],
        ], [
            'quantity.required' => 'Jumlah kuantitas pemakaian wajib diisi.',
            'notes.required' => 'Alasan pemakaian atau rincian bahan rusak wajib diisi.',
        ]);

        $qty = (float) $validated['quantity'];
        $currentStock = (float) $rawMaterial->current_stock;

        // Validasi proteksi anti-minus: kuantitas keluar tidak boleh melebihi stok yang ada
        if ($qty > $currentStock) {
            return back()->withInput()->withErrors([
                'quantity' => "Jumlah pemakaian ({$qty} {$rawMaterial->unit}) melebihi sisa stok fisik yang tersedia (".number_format($currentStock, 2, ',', '.')." {$rawMaterial->unit}).",
            ]);
        }

        $typeLabel = match ($validated['usage_type']) {
            'bar_usage' => 'Pemakaian Bar Minuman',
            'kitchen_usage' => 'Pemakaian Dapur Masakan',
            'damaged' => 'Bahan Tumpah / Rusak',
            'spoilage' => 'Bahan Basi / Kedaluwarsa',
            default => 'Pemakaian Lainnya',
        };

        $ref = ($validated['reference_number'] ?? null) ?: ('USE-'.now()->format('YmdHis'));
        $notes = "[{$typeLabel}] ".$validated['notes'];

        $rawMaterial->recordMutation(
            type: 'out',
            quantity: $qty,
            referenceNumber: $ref,
            notes: $notes,
            userId: $request->user()->id
        );

        return redirect()->route('warehouse.raw_materials.index')
            ->with('success', "Pengurangan stok {$qty} {$rawMaterial->unit} untuk {$rawMaterial->name} berhasil dicatat.");
    }

    /**
     * Stock Opname Fisik: Menghitung selisih (+/-) antara fisik di lapangan dengan sistem.
     */
    public function opname(Request $request, RawMaterial $rawMaterial): RedirectResponse
    {
        $validated = $request->validate([
            'actual_stock' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'actual_stock.required' => 'Hasil hitungan fisik riil wajib diisi.',
            'actual_stock.min' => 'Hitungan fisik tidak boleh bernilai negatif.',
        ]);

        $stockBefore = (float) $rawMaterial->current_stock;
        $actualStock = (float) $validated['actual_stock'];
        $diff = $actualStock - $stockBefore;

        $ref = 'OPNAME-'.now()->format('YmdHis');
        $diffText = ($diff >= 0 ? '+' : '').number_format($diff, 2, ',', '.')." {$rawMaterial->unit}";
        $notes = 'Audit Opname Fisik (Hitungan riil: '.number_format($actualStock, 2, ',', '.')." {$rawMaterial->unit}, Selisih: {$diffText}). ".($validated['notes'] ?? '');

        $rawMaterial->recordMutation(
            type: 'adjustment',
            quantity: $diff,
            referenceNumber: $ref,
            notes: $notes,
            userId: $request->user()->id
        );

        return redirect()->route('warehouse.raw_materials.index')
            ->with('success', "Stock Opname {$rawMaterial->name} berhasil disimpan. Saldo stok kini disesuaikan menjadi ".number_format($actualStock, 2, ',', '.')." {$rawMaterial->unit}.");
    }

    /**
     * Riwayat Mutasi Stok Global (Buku Kas Stok Semua Bahan).
     */
    public function mutations(Request $request): View
    {
        $query = RawMaterialMutation::with(['rawMaterial', 'user'])->latest('id');

        if ($type = $request->input('type')) {
            if ($type !== 'all') {
                $query->where('type', $type);
            }
        }

        if ($materialId = $request->input('raw_material_id')) {
            if ($materialId !== 'all') {
                $query->where('raw_material_id', $materialId);
            }
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $mutations = $query->paginate(20)->withQueryString();
        $rawMaterials = RawMaterial::orderBy('name')->get();

        return view('warehouse.raw_materials.mutations', compact('mutations', 'rawMaterials'));
    }

    /**
     * Kartu Stok Rinci Khusus Satu Bahan Baku.
     */
    public function history(RawMaterial $rawMaterial, Request $request): View
    {
        $query = $rawMaterial->mutations()->with('user')->latest('id');

        if ($type = $request->input('type')) {
            if ($type !== 'all') {
                $query->where('type', $type);
            }
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $mutations = $query->paginate(20)->withQueryString();

        // Akumulasi statistik per item
        $allMutations = $rawMaterial->mutations()->get();
        $itemStats = [
            'total_in' => (float) $allMutations->where('type', 'in')->sum('quantity'),
            'total_out' => (float) $allMutations->where('type', 'out')->sum('quantity'),
            'current_stock' => (float) $rawMaterial->current_stock,
            'unit' => $rawMaterial->unit,
            'total_value' => $rawMaterial->totalAssetValue(),
        ];

        return view('warehouse.raw_materials.history', compact('rawMaterial', 'mutations', 'itemStats'));
    }
}
