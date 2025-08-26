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


class FormularioIngresoExport implements FromCollection, WithStyles, ShouldAutoSize, WithHeadings, WithTitle
{

    use Exportable;

    protected $exportData;

    public function __construct($exportData = null)
    {
        $this->exportData = $exportData;
    }

    public function title(): string
    {
        return 'Orden de servicio';
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
            'ID',
            'Número Radicado',
            'Fecha Radicado',
            'Estado Ingreso',
            'Responsable',
            'Razón Social',
            'Dirección Empresa',
            'Tipo Servicio',
            'Tipo Identificación',
            'Número Identificación',
            'Nombre Completo',
            'Número Contacto',
            'Correo Notificación',
            'Cargo',
            'Salario',
            'Profesional',
            'Informe Selección',
            'Subsidio Transporte',
            'Departamento',
            'Ciudad',
            'EPS',
            'AFP',
            'Estradata',
            'Novedad Stradata',
            'Dirección Laboratorio',
            'Exámenes',
            'Fecha Examen',
            'Recomendaciones Examen',
            'Novedades Exámenes',
            'Novedades',
            'Observación Estado',
            'Afectación Servicio',
            'Responsable Corregir',
            'Correo Notificación Empresa',
            'Fecha Ingreso',
            'Estado Vacante',
            'Laboratorio',
            'Observación Estado',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
