<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiManagementController extends Controller
{
    public function index()
    {
        $endpoints = [
            ['method' => 'GET', 'endpoint' => '/api/admin/reimburse', 'desc' => 'Monitoring Reimburse'],
            ['method' => 'GET', 'endpoint' => '/api/admin/reimburse/{id}', 'desc' => 'Detail Reimburse'],
            ['method' => 'PUT', 'endpoint' => '/api/admin/reimburse/{id}', 'desc' => 'Update Status Reimburse'],

            ['method' => 'GET', 'endpoint' => '/api/v1/monitoring/data-request', 'desc' => 'Monitoring Pengajuan Data (API Key)'],
            ['method' => 'GET', 'endpoint' => '/api/v1/monitoring/data-request/{id}', 'desc' => 'Detail Pengajuan Data (API Key)'],
            ['method' => 'PUT', 'endpoint' => '/api/v1/monitoring/data-request/{id}', 'desc' => 'Update Status Pengajuan Data (API Key)'],
            ['method' => 'DELETE', 'endpoint' => '/api/v1/monitoring/data-request/{id}', 'desc' => 'Hapus Pengajuan Data (API Key)'],

            ['method' => 'GET', 'endpoint' => '/api/v1/monitoring/loan-request', 'desc' => 'Monitoring Peminjaman Barang (API Key)'],
            ['method' => 'GET', 'endpoint' => '/api/v1/monitoring/loan-request/{id}', 'desc' => 'Detail Peminjaman Barang (API Key)'],
            ['method' => 'PUT', 'endpoint' => '/api/v1/monitoring/loan-request/{id}', 'desc' => 'Update Status Peminjaman Barang (API Key)'],
            ['method' => 'DELETE', 'endpoint' => '/api/v1/monitoring/loan-request/{id}', 'desc' => 'Hapus Peminjaman Barang (API Key)'],

            ['method' => 'GET', 'endpoint' => '/api/v1/monitoring/deposit', 'desc' => 'Monitoring Deposit (API Key)'],
            ['method' => 'GET', 'endpoint' => '/api/v1/monitoring/deposit/{id}', 'desc' => 'Detail Deposit (API Key)'],
            ['method' => 'PUT', 'endpoint' => '/api/v1/monitoring/deposit/{id}/details', 'desc' => 'Update Detail Deposit (API Key)'],
            ['method' => 'PUT', 'endpoint' => '/api/v1/monitoring/deposit/{id}/status', 'desc' => 'Update Status Deposit (API Key)'],
            ['method' => 'DELETE', 'endpoint' => '/api/v1/monitoring/deposit/{id}', 'desc' => 'Hapus Deposit (API Key)'],
        ];

        $apiKeys = ApiKey::orderByDesc('id')->get();

        return view('admin.api-management.index', [
            'title' => 'API Manajement',
            'menuApiManagement' => 'active',
            'endpoints' => $endpoints,
            'apiKeys' => $apiKeys,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'never_expires' => 'nullable|boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $neverExpires = (bool) ($validated['never_expires'] ?? false);
        $plainKey = 'kp_' . Str::random(48);

        ApiKey::create([
            'name' => $validated['name'],
            'key_hash' => hash('sha256', $plainKey),
            'is_active' => true,
            'expires_at' => $neverExpires ? null : ($validated['expires_at'] ?? null),
            'last_used_at' => null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.api-management.index')->with([
            'success' => 'API key berhasil dibuat',
            'generated_api_key' => $plainKey,
        ]);
    }

    public function destroy(int $id)
    {
        $apiKey = ApiKey::findOrFail($id);
        $apiKey->delete();

        return redirect()->route('admin.api-management.index')->with('success', 'API key berhasil dihapus');
    }
}
