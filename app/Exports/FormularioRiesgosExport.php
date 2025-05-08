<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class FormularioRiesgosExport implements FromCollection, WithStyles, ShouldAutoSize,WithHeadings
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
            'Tipo de proceso',
            'nombre del proceso',
            'Nombre del riesgo',
            'Oportunidad',
            'Causa',
            'Plan de acción',
            'Concecuencia',
            'Efecto',
            'Amenaza',
            'Oportunidad 2',
            'Amenaza probabilidad-peso',
            'Amenaza probabilidad-descripcion',
            'Amenaza impacto-peso',
            'Amenaza impacto-descripcion',
            'Amenaza probabilidad*impacto-total',
            'Amenaza nivel de riesgo',
            'Amenaza tratamiento',
            'Amenaza método de identificación',
            'Amenaza factor',
            'Amenaza control',
            'Amenaza soporte',
            'Amenaza seguimiento',
            'Amenaza documento registrado-descripcion',
            'Amenaza documento registrado-peso',
            'Amenaza clase de control-descripcion',
            'Amenaza clase de control-peso',
            'Amenaza frecuencia del control-descripcion',
            'Amenaza frecuencia del control-peso',
            'Amenaza tipo de control-descripcion',
            'Amenaza tipo de control-peso',
            'Amenaza existe evidencia-descripcion',
            'Amenaza existe evidencia-peso',
            'Amenaza ejecución eficaz-descripcion',
            'Amenaza ejecución eficaz-peso',
            'Amenaza resultado del control-descripcion',
            'Amenaza resultado del control-peso',
            'Oportunidad probabilidad-peso',
            'Oportunidad probabilidad-descripcion',
            'Oportunidad impacto-peso',
            'Oportunidad impacto-descripcion',
            'Oportunidad probabilidad*impacto-total',
            'Oportunidad nivel de riesgo',
            'Oportunidad tratamiento',
            'Oportunidad método de identificación',
            'Oportunidad factor',
            'Oportunidad control',
            'Oportunidad soporte',
            'Oportunidad seguimiento',
            'Oportunidad documento registrado-descripcion',
            'Oportunidad documento registrado-peso',
            'Oportunidad clase de control-descripcion',
            'Oportunidad clase de control-peso',
            'Oportunidad frecuencia del control-descripcion',
            'Oportunidad frecuencia del control-peso',
            'Oportunidad tipo de control-descripcion',
            'Oportunidad tipo de control-peso',
            'Oportunidad existe evidencia-descripcion',
            'Oportunidad existe evidencia-peso',
            'Oportunidad ejecución eficaz-descripcion',
            'Oportunidad ejecución eficaz-peso',
            'Oportunidad resultado del control-descripcion',
            'Oportunidad resultado del control-peso',
            'Responsable',
            'Última revisión'
            
            
        ];
    }
    

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

}