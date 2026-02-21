<?php

namespace App\Http\Controllers;

class DataMatrixController extends Controller
{
    public function tagNomorPascaBayar()
    {
        return view('admin.data-matrix.index', [
            'title' => 'Tag Nomor Pasca Bayar',
            'menuDataMatrixTagPascaBayar' => 'active',
            'pageTitle' => 'Tag Nomor Pasca Bayar',
            'pageDescription' => 'Halaman Data Matrix untuk kebutuhan tag nomor pasca bayar.',
        ]);
    }

    public function tagPlnInternet()
    {
        return view('admin.data-matrix.index', [
            'title' => 'Tag PLN & Internet',
            'menuDataMatrixTagPlnInternet' => 'active',
            'pageTitle' => 'Tag PLN & Internet',
            'pageDescription' => 'Halaman Data Matrix untuk kebutuhan tag PLN dan Internet.',
        ]);
    }

    public function tagLainnya()
    {
        return view('admin.data-matrix.index', [
            'title' => 'Tag Lainnya',
            'menuDataMatrixTagLainnya' => 'active',
            'pageTitle' => 'Tag Lainnya',
            'pageDescription' => 'Halaman Data Matrix untuk kebutuhan tag lainnya.',
        ]);
    }
}
