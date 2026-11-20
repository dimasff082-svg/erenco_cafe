<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Display a listing of menus in read-only mode for cashier customer consultation,
     * with quick availability toggle for marking sold-out / restocked menu items.
     */
    public function index(Request $request): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = trim((string) $request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->filled('status')) {
            if ($request->get('status') === 'available') {
                $query->where('is_active', true)->where('is_available', true);
            } elseif ($request->get('status') === 'out_of_stock') {
                $query->where('is_available', false);
            } elseif ($request->get('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $products = $query->orderByDesc('is_available')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('cashier.menu.index', compact('products', 'categories'));
    }

    /**
     * Kasir menandai menu habis (out of stock) atau tersedia kembali.
     */
    public function toggleAvailability(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $product->update([
            'is_available' => ! (bool) $product->is_available,
        ]);

        $statusText = $product->is_available ? 'tersedia kembali' : 'ditandai habis (stok kosong)';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_available' => $product->is_available,
                'message' => "Menu '{$product->name}' berhasil {$statusText}.",
            ]);
        }

        return back()->with('success', "Status menu '{$product->name}' berhasil diubah menjadi {$statusText}.");
    }
}
