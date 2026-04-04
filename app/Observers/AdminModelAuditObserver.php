<?php

namespace App\Observers;

use App\Models\AdminActivityLog;
use Illuminate\Database\Eloquent\Model;

class AdminModelAuditObserver
{
    private static array $beforeUpdate = [];

    public function updating(Model $model): void
    {
        if (!$this->shouldLog($model)) {
            return;
        }

        self::$beforeUpdate[spl_object_id($model)] = $model->getOriginal();
    }

    public function created(Model $model): void
    {
        if (!$this->shouldLog($model)) {
            return;
        }

        $after = $this->sanitizeData($model->getAttributes());

        $this->writeLog([
            'actor_id' => auth()->id(),
            'actor_name' => auth()->user()?->nama,
            'actor_role' => auth()->user()?->jabatan,
            'action_type' => 'created',
            'method' => request()?->method() ?? 'CLI',
            'route_name' => optional(request()?->route())->getName(),
            'path' => request()?->path() ?? '-',
            'target_model' => $model::class,
            'target_id' => $model->getKey(),
            'ip_address' => request()?->ip(),
            'user_agent' => mb_substr((string) request()?->userAgent(), 0, 500),
            'status_code' => 200,
            'change_summary' => 'Membuat data baru',
            'before_data' => null,
            'after_data' => $after,
        ]);
    }

    public function updated(Model $model): void
    {
        if (!$this->shouldLog($model)) {
            return;
        }

        $key = spl_object_id($model);
        $before = $this->sanitizeData(self::$beforeUpdate[$key] ?? []);
        unset(self::$beforeUpdate[$key]);

        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $after = [];
        $beforeChanged = [];
        $summaryParts = [];

        foreach ($changes as $field => $newValue) {
            $oldValue = $before[$field] ?? null;
            $beforeChanged[$field] = $oldValue;
            $after[$field] = $this->sanitizeValue($newValue);
            $summaryParts[] = $field . ': ' . $this->toShortText($oldValue) . ' → ' . $this->toShortText($newValue);
        }

        $this->writeLog([
            'actor_id' => auth()->id(),
            'actor_name' => auth()->user()?->nama,
            'actor_role' => auth()->user()?->jabatan,
            'action_type' => 'updated',
            'method' => request()?->method() ?? 'CLI',
            'route_name' => optional(request()?->route())->getName(),
            'path' => request()?->path() ?? '-',
            'target_model' => $model::class,
            'target_id' => $model->getKey(),
            'ip_address' => request()?->ip(),
            'user_agent' => mb_substr((string) request()?->userAgent(), 0, 500),
            'status_code' => 200,
            'change_summary' => 'Mengubah ' . implode(' | ', $summaryParts),
            'before_data' => $beforeChanged,
            'after_data' => $after,
        ]);
    }

    public function deleted(Model $model): void
    {
        if (!$this->shouldLog($model)) {
            return;
        }

        $before = $this->sanitizeData($model->getOriginal());

        $this->writeLog([
            'actor_id' => auth()->id(),
            'actor_name' => auth()->user()?->nama,
            'actor_role' => auth()->user()?->jabatan,
            'action_type' => 'deleted',
            'method' => request()?->method() ?? 'CLI',
            'route_name' => optional(request()?->route())->getName(),
            'path' => request()?->path() ?? '-',
            'target_model' => $model::class,
            'target_id' => $model->getKey(),
            'ip_address' => request()?->ip(),
            'user_agent' => mb_substr((string) request()?->userAgent(), 0, 500),
            'status_code' => 200,
            'change_summary' => 'Menghapus data',
            'before_data' => $before,
            'after_data' => null,
        ]);
    }

    private function shouldLog(Model $model): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $role = strtolower((string) auth()->user()?->jabatan);
        if (!in_array($role, ['admin', 'superadmin'], true)) {
            return false;
        }

        return !($model instanceof AdminActivityLog);
    }

    private function sanitizeData(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = $this->sanitizeValue($value);
        }

        return $result;
    }

    private function sanitizeValue(mixed $value): mixed
    {
        if (is_null($value) || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return mb_strlen($value) > 1000 ? mb_substr($value, 0, 1000) . '...' : $value;
        }

        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = $this->sanitizeValue($item);
            }

            return $normalized;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return is_object($value) ? get_class($value) : (string) $value;
    }

    private function toShortText(mixed $value): string
    {
        $text = is_scalar($value) || is_null($value)
            ? (string) ($value ?? 'null')
            : json_encode($value, JSON_UNESCAPED_UNICODE);

        return mb_strlen($text) > 80 ? mb_substr($text, 0, 80) . '...' : $text;
    }

    private function writeLog(array $payload): void
    {
        try {
            AdminActivityLog::create($payload);
        } catch (\Throwable $exception) {
        }
    }
}
