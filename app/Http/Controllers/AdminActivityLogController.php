<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use Illuminate\Http\Request;

class AdminActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminActivityLog::query()
            ->select([
                'id',
                'actor_id',
                'actor_name',
                'actor_role',
                'action_type',
                'method',
                'route_name',
                'path',
                'target_model',
                'target_id',
                'ip_address',
                'status_code',
                'change_summary',
                'request_data',
                'before_data',
                'after_data',
                'created_at',
            ]);

        if ($request->filled('q')) {
            $keyword = trim((string) $request->q);
            $query->where(function ($builder) use ($keyword) {
                $builder->where('actor_name', 'like', "%{$keyword}%")
                    ->orWhere('path', 'like', "%{$keyword}%")
                    ->orWhere('route_name', 'like', "%{$keyword}%")
                    ->orWhere('target_model', 'like', "%{$keyword}%")
                    ->orWhere('change_summary', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        } else {
            $query->whereIn('action_type', ['created', 'updated', 'deleted']);
        }

        if ($request->filled('role')) {
            $query->where('actor_role', $request->role);
        }

        if ($request->filled('method')) {
            $query->where('method', strtoupper((string) $request->method));
        }

        if ($request->filled('status_code')) {
            $query->where('status_code', (int) $request->status_code);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query
            ->orderByDesc('id')
            ->simplePaginate(50)
            ->withQueryString();

        return view('admin.logs.index', [
            'title' => 'Lihat Logs',
            'menuSuperadminLogs' => 'active',
            'logs' => $logs,
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'actions' => ['request', 'created', 'updated', 'deleted'],
        ]);
    }
}
