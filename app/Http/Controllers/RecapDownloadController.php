<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecapDownloadController extends Controller
{
    public function download(string $file)
    {
        $path = 'recaps/' . $file;
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'File tidak ditemukan');
        }

        return Storage::disk('local')->download($path);
    }
}
