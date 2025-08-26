<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ConsultaPilaExport implements FromCollection, WithHeadings
{

    protected $empleados;

    public function __construct(array $empleados)
    {
        $this->empleados = $empleados;
    }
    /**
     * 
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->empleados)->map(function ($empleado) {
            return [
                $empleado['TipoDocumento'],
                $empleado['NumeroDocumento'],
                $empleado['PrimerNombre'],
                $empleado['SegundoNombre'],
                $empleado['PrimerApellido'],
                $empleado['SegundoApellido'],
                $empleado['CodigoEps'],
                $empleado['NombreEps'],
                $empleado['eps_novasoft'],
                $empleado['eps_nombre_novasoft'],
                $empleado['FechaAfiliacionEps'],
                $empleado['eps_fecha_afiliacion_novasoft'],
                $empleado['CertificadoEps'] == true ? "Si" : "No",
                $empleado['CodigoAfp'],
                $empleado['NombreAfp'],
                $empleado['afp_novasoft'],
                $empleado['afp_nombre_novasoft'],
                $empleado['FechaAfiliacionAfp'],
                $empleado['afp_fecha_afiliacion_novasoft'] == $empleado['afp_fecha_afiliacion_novasoft'] ? $empleado['afp_fecha_afiliacion_novasoft'] : null,
                $empleado['CertificadoAfp'] == true ? "Si" : "No",
                $empleado['EsPensionado'] == true ? "Si" : "No",
                $empleado['EnTramitePension'],
                $empleado['TipoPension'],



                implode(" | ", $empleado['advertencias'] ?? []),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tipo Documento',
            'Número Documento',
            'Primer Nombre',
            'Segundo Nombre',
            'Primer Apellido',
            'Segundo Apellido',
            'Código EPS ADRES',
            'Nombre EPS ADRES',
            'EPS Novasoft',
            'Nombre EPS Novasoft',
            'Fecha Afiliación EPS ADRES',
            'Fecha Afiliación EPS Novasoft',
            'Certificado EPS',
            'Código AFP RUES',
            'Nombre AFP RUES',
            'Código AFP Novasoft',
            'Nombre AFP Novasoft',
            'Fecha Afiliación AFP RUES',
            'Fecha Afiliación AFP Novasoft',
            'Certificado AFP',
            'Es Pensionado',
            'En Trámite Pensión',
            'Tipo Pensión',
            'Advertencias',
        ];
    }
}