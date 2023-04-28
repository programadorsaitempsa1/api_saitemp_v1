<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class EmpleadosExport implements FromCollection, WithStyles, ShouldAutoSize,WithHeadings
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

    public function headings(): array
    {
        return [
            'Fecha Inicio',
            'Fecha Retiro',
            'Nombre Retiro',
            'Nombre Cargo',
            'Nombre Convenio',
            'Salario Básico',
            'Nombre Tipo Contrato',
            'Notas Contrato',
            'Nombre Analista'
        ];
    }
    

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

}