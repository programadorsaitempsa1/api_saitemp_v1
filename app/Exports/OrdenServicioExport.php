<?php

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\FormularioIngresoExport;
use App\Exports\SeguimientoGuardadoOrdenServicioSheet;
use App\Exports\LaborariosMedicosSheet;
use App\Exports\SeguimientoEstadosOrdenServicioSheet;

class OrdenServicioExport implements WithMultipleSheets
{
    protected $data1, $data2, $data3, $data4;

    public function __construct($data1, $data2, $data3, $data4)
    {
        $this->data1 = $data1; // Ingresos
        $this->data2 = $data2; // Seguimiento Guardado
        $this->data3 = $data3; // Laboratorios
        $this->data4 = $data4; // Seguimiento Estado
    }

    public function sheets(): array
    {
        return [
            new FormularioIngresoExport($this->data1),
            new SeguimientoGuardadoOrdenServicioSheet($this->data2),
            new LaborariosMedicosSheet($this->data3),
            new SeguimientoEstadosOrdenServicioSheet($this->data4),
        ];
    }
}
