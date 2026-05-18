<?php

namespace App\Http\Controllers;

use App\Models\DataCutLog;
use App\Models\Transaksi;
use App\Models\Deposit;
use App\Models\Reimburse;
use App\Models\Minusan;
use App\Models\Import;
use App\Models\TagNomorPascaBayar;
use App\Models\TagPlnInternet;
use App\Models\TagLainnya;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DataCuttingController extends Controller
{
    public function guide()
    {
        // Hanya superadmin yang bisa akses
        if (strtolower(auth()->user()->jabatan ?? '') !== 'superadmin') {
            abort(403, 'Unauthorized');
        }

        return view('admin.data-cutting.guide');
    }

    public function index()
    {
        // Hanya superadmin yang bisa akses
        if (strtolower(auth()->user()->jabatan ?? '') !== 'superadmin') {
            abort(403, 'Unauthorized');
        }

        $logs = DataCutLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.data-cutting.index', compact('logs'));
    }

    public function create()
    {
        if (strtolower(auth()->user()->jabatan ?? '') !== 'superadmin') {
            abort(403, 'Unauthorized');
        }

        $twoMonthsAgo = now()->subMonths(2)->startOfDay();
        $dataStats = $this->getDataStats($twoMonthsAgo);

        return view('admin.data-cutting.create', compact('twoMonthsAgo', 'dataStats'));
    }

    public function preview(Request $request)
    {
        if (strtolower(auth()->user()->jabatan ?? '') !== 'superadmin') {
            abort(403, 'Unauthorized');
        }

        $cutDate = Carbon::parse($request->cut_date)->startOfDay();
        
        $stats = [
            'transaksis' => Transaksi::where('tgl_entri', '<', $cutDate)->count(),
            'deposits' => Deposit::where('created_at', '<', $cutDate)->count(),
            'reimburse' => Reimburse::where('created_at', '<', $cutDate)->count(),
            'minusans' => Minusan::where('created_at', '<', $cutDate)->count(),
            'imports' => Import::where('created_at', '<', $cutDate)->count(),
            'tag_nomor_pasca_bayars' => TagNomorPascaBayar::where('created_at', '<', $cutDate)->count(),
            'tag_pln_internets' => TagPlnInternet::where('created_at', '<', $cutDate)->count(),
            'tag_lainnyas' => TagLainnya::where('created_at', '<', $cutDate)->count(),
        ];

        $totalRecords = array_sum($stats);

        return response()->json([
            'success' => true,
            'cut_date' => $cutDate->format('Y-m-d'),
            'stats' => $stats,
            'total_records' => $totalRecords,
        ]);
    }

    public function store(Request $request)
    {
        if (strtolower(auth()->user()->jabatan ?? '') !== 'superadmin') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'cut_date' => 'required|date',
            'backup_database' => 'required|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cutDate = Carbon::parse($request->cut_date)->startOfDay();
        
        try {
            DB::beginTransaction();

            $log = DataCutLog::create([
                'user_id' => auth()->id(),
                'cut_date' => $cutDate,
                'notes' => $request->notes,
                'status' => 'pending',
            ]);

            // Backup database jika diminta
            if ($request->backup_database) {
                $log->update(['status' => 'backing_up']);
                $backupResult = $this->backupDatabase($log);
                
                if (!$backupResult['success']) {
                    throw new \Exception($backupResult['message']);
                }

                $log->update([
                    'backup_file' => $backupResult['filename'],
                    'backup_size' => $backupResult['size'],
                ]);
            }

            // Mulai proses penghapusan data
            $log->update(['status' => 'deleting']);
            
            $deletedCounts = $this->deleteOldData($cutDate);

            $log->update([
                'transaksis_deleted' => $deletedCounts['transaksis'],
                'deposits_deleted' => $deletedCounts['deposits'],
                'reimburse_deleted' => $deletedCounts['reimburse'],
                'minusans_deleted' => $deletedCounts['minusans'],
                'imports_deleted' => $deletedCounts['imports'],
                'tag_nomor_pasca_bayars_deleted' => $deletedCounts['tag_nomor_pasca_bayars'],
                'tag_pln_internets_deleted' => $deletedCounts['tag_pln_internets'],
                'tag_lainnyas_deleted' => $deletedCounts['tag_lainnyas'],
                'status' => 'completed',
            ]);

            DB::commit();

            return redirect()->route('data-cutting.index')
                ->with('success', 'Data berhasil dipotong! Total ' . $log->getTotalDeletedCount() . ' record dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            $log->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
            ]);

            return redirect()->route('data-cutting.index')
                ->with('error', 'Gagal memotong data: ' . $e->getMessage());
        }
    }

    public function download($id)
    {
        if (strtolower(auth()->user()->jabatan ?? '') !== 'superadmin') {
            abort(403, 'Unauthorized');
        }

        $log = DataCutLog::findOrFail($id);

        if (!$log->backup_file || !file_exists(storage_path('app/backups/' . $log->backup_file))) {
            return redirect()->back()->with('error', 'File backup tidak ditemukan.');
        }

        return response()->download(
            storage_path('app/backups/' . $log->backup_file),
            'backup_' . $log->cut_date->format('Y-m-d') . '.sql'
        );
    }

    /**
     * Backup database ke file SQL
     */
    private function backupDatabase(DataCutLog $log)
    {
        try {
            $backupPath = storage_path('app/backups');
            
            if (!is_dir($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $filename = 'backup_' . now()->format('Y-m-d_His') . '.sql';
            $filepath = $backupPath . '/' . $filename;

            $host = config('database.connections.mysql.host');
            $database = config('database.connections.mysql.database');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $port = config('database.connections.mysql.port');

            // Cari path mysqldump
            $mysqldumpPath = $this->getMysqldumpPath();
            
            if (!$mysqldumpPath) {
                return [
                    'success' => false,
                    'message' => 'mysqldump tidak ditemukan. Pastikan MySQL sudah terinstall atau tambahkan path ke system PATH',
                ];
            }

            // Build command
            $command = "\"$mysqldumpPath\" --host=\"$host\" --port=\"$port\" --user=\"$username\"";
            
            if (!empty($password)) {
                $command .= " --password=\"$password\"";
            }
            
            $command .= " \"$database\" > \"$filepath\" 2>&1";

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                return [
                    'success' => false,
                    'message' => 'Gagal membuat backup database: ' . implode(' ', $output),
                ];
            }

            $fileSize = filesize($filepath);
            $fileSizeMB = round($fileSize / (1024 * 1024), 2);

            return [
                'success' => true,
                'filename' => $filename,
                'size' => $fileSizeMB . ' MB',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cari path mysqldump
     */
    private function getMysqldumpPath()
    {
        // Paths untuk Windows
        $possiblePaths = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-5.7.26-win32-x64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-5.7.36-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.1-winx64\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
        ];

        // Cek di PATH environment
        $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' 
            ? 'where mysqldump' 
            : 'which mysqldump';
        
        exec($command, $output, $returnVar);
        
        if ($returnVar === 0 && !empty($output[0])) {
            return trim($output[0]);
        }

        // Cek di paths yang mungkin
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Hapus data yang lebih lama dari tanggal yang ditentukan
     */
    private function deleteOldData($cutDate)
    {
        $deleted = [
            'transaksis' => Transaksi::where('tgl_entri', '<', $cutDate)->delete(),
            'deposits' => Deposit::where('created_at', '<', $cutDate)->delete(),
            'reimburse' => Reimburse::where('created_at', '<', $cutDate)->delete(),
            'minusans' => Minusan::where('created_at', '<', $cutDate)->delete(),
            'imports' => Import::where('created_at', '<', $cutDate)->delete(),
            'tag_nomor_pasca_bayars' => TagNomorPascaBayar::where('created_at', '<', $cutDate)->delete(),
            'tag_pln_internets' => TagPlnInternet::where('created_at', '<', $cutDate)->delete(),
            'tag_lainnyas' => TagLainnya::where('created_at', '<', $cutDate)->delete(),
        ];

        return $deleted;
    }

    /**
     * Dapatkan statistik data
     */
    private function getDataStats($fromDate)
    {
        return [
            'transaksis' => Transaksi::where('tgl_entri', '<', $fromDate)->count(),
            'deposits' => Deposit::where('created_at', '<', $fromDate)->count(),
            'reimburse' => Reimburse::where('created_at', '<', $fromDate)->count(),
            'minusans' => Minusan::where('created_at', '<', $fromDate)->count(),
            'imports' => Import::where('created_at', '<', $fromDate)->count(),
            'tag_nomor_pasca_bayars' => TagNomorPascaBayar::where('created_at', '<', $fromDate)->count(),
            'tag_pln_internets' => TagPlnInternet::where('created_at', '<', $fromDate)->count(),
            'tag_lainnyas' => TagLainnya::where('created_at', '<', $fromDate)->count(),
        ];
    }
}
