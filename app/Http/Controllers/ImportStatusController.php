<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Import;

class ImportStatusController extends Controller
{
    // GET /imports/status?file=filename or ?id=123
    public function status(Request $request)
    {
        $id = $request->query('id');
        $file = $request->query('file');

        if ($id) {
            $import = Import::find($id);
        } elseif ($file) {
            $import = Import::where('file_name', $file)->orWhere('file_path', $file)->latest()->first();
        } else {
            return response()->json(['error' => 'missing id or file'], 400);
        }

        if (! $import) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'id' => $import->id,
            'file' => $import->file_name,
            'status' => $import->status,
            'rows' => $import->rows_inserted,
            'message' => $import->message,
            'updated_at' => $import->updated_at,
        ]);
    }
}
