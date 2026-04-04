<?php

namespace App\Http\Middleware;

use App\Models\AdminActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $user = $request->user();
            if (!$user) {
                return $response;
            }

            $role = (string) ($user->jabatan ?? '');
            if (!in_array(strtolower($role), ['admin', 'superadmin'], true)) {
                return $response;
            }

            $input = $request->except([
                '_token',
                'password',
                'password_confirmation',
                'current_password',
            ]);

            $sanitized = [];
            foreach ($input as $key => $value) {
                $sanitized[$key] = $this->normalizeValue($value);
            }

            $payload = [
                'actor_id' => $user->id,
                'actor_name' => $user->nama,
                'actor_role' => $role,
                'action_type' => 'request',
                'method' => $request->method(),
                'route_name' => optional($request->route())->getName(),
                'path' => $request->path(),
                'target_model' => null,
                'target_id' => null,
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                'status_code' => (int) $response->getStatusCode(),
                'change_summary' => 'Akses halaman/endpoint',
                'request_data' => empty($sanitized) ? null : $sanitized,
                'before_data' => null,
                'after_data' => null,
            ];

            try {
                AdminActivityLog::create($payload);
            } catch (\Throwable $exception) {
                AdminActivityLog::create([
                    'actor_id' => $user->id,
                    'actor_name' => $user->nama,
                    'actor_role' => $role,
                    'method' => $request->method(),
                    'route_name' => optional($request->route())->getName(),
                    'path' => $request->path(),
                    'ip_address' => $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                    'status_code' => (int) $response->getStatusCode(),
                    'request_data' => empty($sanitized) ? null : $sanitized,
                ]);
            }
        } catch (\Throwable $exception) {
        }

        return $response;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_null($value) || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return mb_strlen($value) > 500 ? mb_substr($value, 0, 500) . '...' : $value;
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeValue($item);
            }

            return $normalized;
        }

        if ($value instanceof \Illuminate\Http\UploadedFile) {
            return [
                'uploaded_file' => $value->getClientOriginalName(),
                'size' => $value->getSize(),
            ];
        }

        return is_object($value) ? get_class($value) : (string) $value;
    }
}
