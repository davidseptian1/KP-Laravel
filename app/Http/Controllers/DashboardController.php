<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Minusan;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            "title"             => 'Dashboard',
            "menuDashboard"     => 'active',
            "minusan"           => Minusan::latest()->take(5)->get(),
            "totalUser"         => User::count(),
            "totalMinusan"      => Minusan::selectRaw('SUM(qty * total_per_orang) as total')->value('total'),
            "totalTransaksi"    => Minusan::count(),
        ];

        return view('dashboard', $data);
    }
}
