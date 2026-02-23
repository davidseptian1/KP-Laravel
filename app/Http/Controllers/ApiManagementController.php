<?php

namespace App\Http\Controllers;

class ApiManagementController extends Controller
{
    public function index()
    {
        $endpoints = [
            ['method' => 'GET', 'endpoint' => '/api/admin/reimburse', 'desc' => 'Monitoring Reimburse'],
            ['method' => 'GET', 'endpoint' => '/api/admin/reimburse/{id}', 'desc' => 'Detail Reimburse'],
            ['method' => 'PUT', 'endpoint' => '/api/admin/reimburse/{id}', 'desc' => 'Update Status Reimburse'],

            ['method' => 'GET', 'endpoint' => '/api/admin/monitoring/data-request', 'desc' => 'Monitoring Pengajuan Data'],
            ['method' => 'GET', 'endpoint' => '/api/admin/monitoring/data-request/{id}', 'desc' => 'Detail Pengajuan Data'],
            ['method' => 'PUT', 'endpoint' => '/api/admin/monitoring/data-request/{id}', 'desc' => 'Update Status Pengajuan Data'],
            ['method' => 'DELETE', 'endpoint' => '/api/admin/monitoring/data-request/{id}', 'desc' => 'Hapus Pengajuan Data'],

            ['method' => 'GET', 'endpoint' => '/api/admin/monitoring/loan-request', 'desc' => 'Monitoring Peminjaman Barang'],
            ['method' => 'GET', 'endpoint' => '/api/admin/monitoring/loan-request/{id}', 'desc' => 'Detail Peminjaman Barang'],
            ['method' => 'PUT', 'endpoint' => '/api/admin/monitoring/loan-request/{id}', 'desc' => 'Update Status Peminjaman Barang'],
            ['method' => 'DELETE', 'endpoint' => '/api/admin/monitoring/loan-request/{id}', 'desc' => 'Hapus Peminjaman Barang'],

            ['method' => 'GET', 'endpoint' => '/api/admin/monitoring/deposit', 'desc' => 'Monitoring Deposit'],
            ['method' => 'GET', 'endpoint' => '/api/admin/monitoring/deposit/{id}', 'desc' => 'Detail Deposit'],
            ['method' => 'PUT', 'endpoint' => '/api/admin/monitoring/deposit/{id}/details', 'desc' => 'Update Detail Deposit'],
            ['method' => 'PUT', 'endpoint' => '/api/admin/monitoring/deposit/{id}/status', 'desc' => 'Update Status Deposit'],
            ['method' => 'DELETE', 'endpoint' => '/api/admin/monitoring/deposit/{id}', 'desc' => 'Hapus Deposit'],
        ];

        return view('admin.api-management.index', [
            'title' => 'API Manajement',
            'menuApiManagement' => 'active',
            'endpoints' => $endpoints,
        ]);
    }
}
