<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class FormularioIngresoExport implements FromCollection, WithStyles, ShouldAutoSize,WithHeadings
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
            'Número de radicado',
            'Fecha de radicado',
            'Estado ingreso',
            'Responsable',
            'Empresa usuaria',
            'Dirección de empresa',
            'Tipo de servicio',
            'Tipo de indentificación',
            'Número de identificación',
            'Nombre candidato',
            'Número de contacto',
            'Correo candidato',
            'cargo',
            'Salario',
            'Profesional',
            'Informe de selección',
            'Subsidio de transporte',
            'Departamento',
            'Ciudad',
            'EPS',
            'AFP',
            'Stradata verificado',
            'Novedades stradata',
            'Dirección laboratorio',
            'Exámenes',
            'Fecha de exámen',
            'Recomendaciones exámen',
            'Novedades exámenes',
            'Observacion al servicio',
            'Novedad en servicio',
            'Afectaciones al servicio',
            'Corregir por',
            'Correo notificación empresa',
            'Fecha de ingreso',
            'Estado de la vacante',
            'Otro laboratorio',
            'Departamento ubicación laboratorio médico',
            'Ciudad ubicación laboratorio médico',
            'Laboratorio médico',
            'Seguimiento estados',
            ' ',
            'Seguimiento guardado',
            ' ',
            
        ];
    }
    

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

}