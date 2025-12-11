<?php

namespace App\Exports;
use App\Models\Minusan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MinusanExport implements FromView
{
    public function view(): View
    {
        $data = array(
            'minusan' => Minusan::get(),
        );
        return view('admin/minusan/excel',$data);
    }
}

