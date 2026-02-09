<?php

namespace App\Jobs;

use App\Imports\TransaksiImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ProcessTransaksiImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $path;

    /**
     * Create a new job instance.
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Execute the job with batch processing.
     */
    public function handle()
    {
        try {
            // Import from stored path (storage/app/...)
            $fullPath = storage_path('app/' . $this->path);

            // Open file and process in chunks
            $file = fopen($fullPath, 'r');
            
            // Read first line to detect delimiter - try semicolon first, then comma
            $firstLine = fgets($file);
            rewind($file);
            $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
            
            $header = fgetcsv($file, 0, $delimiter); // Use detected delimiter
            $batchSize = 1000; // Process 1,000 records per batch
            $batch = [];

            while (($row = fgetcsv($file, 0, $delimiter)) !== false) { // Use detected delimiter
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                \Log::info('Processing row', ['row' => $row]);

                // Validate header and row length
                if (count($header) !== count($row)) {
                    \Log::error('Row length does not match header length', ['header' => $header, 'row' => $row]);
                    continue;
                }

                $batch[] = array_combine($header, $row);
                \Log::info('Row successfully combined', ['data' => end($batch)]);

                if (count($batch) === $batchSize) {
                    // Dispatch batch for processing
                    $this->processBatch($batch);
                    $batch = [];
                }
            }

            // Process remaining records
            if (!empty($batch)) {
                $this->processBatch($batch);
            }

            fclose($file);

            // On success, move the processed file to imports/processed
            try {
                $processedDir = 'imports/processed';
                Storage::makeDirectory($processedDir);
                if (Storage::exists($this->path)) {
                    $dest = $processedDir . '/' . basename($this->path);
                    Storage::move($this->path, $dest);
                }
            } catch (\Throwable $e) {
                Log::warning('Could not move imported file: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error('Import failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process a batch of records.
     */
    private function processBatch(array $batch)
    {
        foreach ($batch as $record) {
            try {
                \Log::info('Inserting record into database', ['record' => $record]);
                \DB::table('transaksis')->insertOrIgnore($record);
                \Log::info('Record inserted successfully', ['record' => $record]);
            } catch (\Throwable $e) {
                \Log::error('Error inserting record', ['record' => $record, 'error' => $e->getMessage()]);
            }
        }
    }
}
