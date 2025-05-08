<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EstadosExport implements FromView
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function view(): View
    {
        return view('exports.estados', [
            'estados' => $this->query->get()
        ]);
    }
}