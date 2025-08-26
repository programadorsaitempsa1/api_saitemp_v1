<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;


class SeguimientoEstadosOrdenServicioSheet implements FromCollection, WithStyles, ShouldAutoSize, WithHeadings, WithTitle
{

    use Exportable;

    protected $exportData;

    public function __construct($exportData = null)
    {
        $this->exportData = $exportData;
    }



    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->exportData;
    }

    public function title(): string
    {
        return 'Seguimiento estados';
    }

    public function headings(): array
    {
        return [
            'ID Formulario',
            'Responsable Inicial',
            'Responsable Final',
            'Estado Inicial',
            'Estado Final',
            'Actualiza Registro',
            'Fecha Seguimiento',
        ];
    }



    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
