<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Tampilan formulir pengaturan toko dan sistem untuk Admin.
     */
    public function index(): View
    {
        $settings = Setting::allMapped();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Menyimpan perubahan pengaturan toko.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:150'],
            'store_phone' => ['required', 'string', 'max:30'],
            'store_email' => ['nullable', 'email', 'max:150'],
            'store_address' => ['required', 'string', 'max:500'],
            'store_hours' => ['required', 'string', 'max:100'],
            'online_welcome_title' => ['required', 'string', 'max:200'],
            'online_welcome_description' => ['required', 'string', 'max:1000'],
            'attendance_max_distance' => ['required', 'numeric', 'min:1', 'max:5000'],
            'order_notification_whatsapp' => ['nullable', 'string', 'max:30'],
            'app_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ], [
            'store_name.required' => 'Nama toko wajib diisi.',
            'store_phone.required' => 'Nomor kontak telepon / WhatsApp toko wajib diisi.',
            'store_address.required' => 'Alamat lengkap toko wajib diisi.',
            'store_hours.required' => 'Jam operasional toko wajib diisi.',
            'online_welcome_title.required' => 'Judul ucapan selamat datang online order wajib diisi.',
            'online_welcome_description.required' => 'Deskripsi singkat toko wajib diisi.',
            'attendance_max_distance.required' => 'Maksimal jarak radius absensi wajib diisi.',
            'attendance_max_distance.numeric' => 'Maksimal jarak radius absensi harus berupa angka.',
            'attendance_max_distance.min' => 'Maksimal jarak radius absensi minimal 1 meter.',
            'app_logo.image' => 'File logo harus berupa gambar yang valid (PNG, JPG, WEBP, SVG).',
            'app_logo.max' => 'Ukuran file logo maksimal 2 MB.',
        ]);

        // Simpan setting berbasis teks
        Setting::set('store_name', $validated['store_name'], 'text', 'store');
        Setting::set('store_phone', $validated['store_phone'], 'text', 'store');
        Setting::set('store_email', $validated['store_email'] ?? '', 'text', 'store');
        Setting::set('store_address', $validated['store_address'], 'textarea', 'store');
        Setting::set('store_hours', $validated['store_hours'], 'text', 'store');
        Setting::set('online_welcome_title', $validated['online_welcome_title'], 'text', 'online_order');
        Setting::set('online_welcome_description', $validated['online_welcome_description'], 'textarea', 'online_order');
        Setting::set('attendance_max_distance', (string) $validated['attendance_max_distance'], 'number', 'attendance');
        Setting::set('order_notification_whatsapp', $validated['order_notification_whatsapp'] ?? '', 'text', 'online_order');

        // Kelola file logo toko: hapus logo lama jika diminta atau jika mengunggah file baru
        if ($request->boolean('remove_logo')) {
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::set('app_logo', null, 'image', 'appearance');
        } elseif ($request->hasFile('app_logo')) {
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('app_logo')->store('settings', 'public');
            Setting::set('app_logo', $logoPath, 'image', 'appearance');
        }

        return redirect()->route('admin.settings.index')->with('success', 'Pengaturan toko dan pesan online berhasil disimpan.');
    }
}
