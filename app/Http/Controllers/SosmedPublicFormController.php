<?php

namespace App\Http\Controllers;

use App\Models\SosmedSetting;
use App\Models\SosmedSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SosmedPublicFormController extends Controller
{
    public function index()
    {
        $isFormActive = SosmedSetting::getByKey('form_is_active', '1');
        
        $divisiList = [
            'Digital Marketing',
            'Creative & Design',
            'Customer Service',
            'Finance & Accounting',
            'IT & Development',
            'Operations & Warehouse',
            'HR & General Affair',
            'Sales & Retail'
        ];

        $sosmedPlatforms = [
            'Instagram',
            'TikTok',
            'Facebook'
        ];

        $rawTaskLinks = SosmedSetting::getByKey('task_links', '[]');
        $taskLinks = json_decode($rawTaskLinks, true) ?? [];
        if (!is_array($taskLinks)) {
            $taskLinks = [];
        }

        return view('sosmed.public_form', compact('isFormActive', 'divisiList', 'sosmedPlatforms', 'taskLinks'));
    }

    public function store(Request $request)
    {
        $isFormActive = SosmedSetting::getByKey('form_is_active', '1');
        if ($isFormActive === '0' || $isFormActive === 0 || $isFormActive === false) {
            return back()->with('error', 'Mohon maaf, form penginputan sosmed saat ini sedang dinonaktifkan oleh Admin.')->withInput();
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'divisi' => 'required|string|max:255',
            'sosmed_platform' => 'required|string|in:Instagram,TikTok,Facebook',
            'photos' => 'required|array|min:1',
            'photos.*' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'divisi.required' => 'Divisi wajib dipilih.',
            'sosmed_platform.required' => 'Platform Sosmed wajib dipilih.',
            'photos.required' => 'Foto bukti posting minimal 1 file.',
            'photos.*.image' => 'File yang diupload harus berupa gambar.',
            'photos.*.max' => 'Ukuran foto maksimal 10MB per file.',
        ]);

        $uploadedPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('sosmed_photos', 'public');
                    $uploadedPaths[] = 'storage/' . $path;
                }
            }
        }

        if (empty($uploadedPaths)) {
            return back()->with('error', 'Gagal memproses file foto yang diupload. Silakan coba lagi.')->withInput();
        }

        $namaFirstWord = SosmedSubmission::extractFirstWord($request->nama);
        $currentFeeSetting = (float) SosmedSetting::getByKey('fee_per_submission', 5000);

        SosmedSubmission::create([
            'nama' => trim($request->nama),
            'nama_first_word' => $namaFirstWord,
            'divisi' => $request->divisi,
            'sosmed_platform' => $request->sosmed_platform,
            'photos' => $uploadedPaths,
            'status' => 'pending',
            'fee_amount' => $currentFeeSetting,
        ]);

        return back()->with('success', 'Form bukti posting sosmed berhasil dikirim! Data Anda akan ditinjau oleh Admin Sosmed.');
    }
}
