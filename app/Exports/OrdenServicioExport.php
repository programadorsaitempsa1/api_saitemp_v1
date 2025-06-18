<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\FormularioIngresoExport;
use App\Exports\SeguimientoEstadosOrdenServicioSheet;
use App\Exports\SeguimientoGuardadoOrdenServicioSheet;
use App\Exports\LaborariosMedicosSheet;

class OrdenServicioExport implements WithMultipleSheets
{
    /** @var \Illuminate\Support\Collection */
    private $data1;
    /** @var \Illuminate\Support\Collection */
    private $data2;
    /** @var \Illuminate\Support\Collection */
    private $data3;
    /** @var \Illuminate\Support\Collection */
    private $data4;

    /**
     * @param \Illuminate\Support\Collection $data1
     * @param \Illuminate\Support\Collection $data2
     */
    public function __construct($data1, $data2, $data3, $data4)
    {
        $this->data1 = $data1;
        $this->data2 = $data2;
        $this->data3 = $data3;
        $this->data4 = $data4;
    }

    /**
     * Retorna un arreglo de instancias de las hojas que quieres exportar.
     *
     * @return array
     */
    public function sheets(): array
    {
        return [
            // Aquí pasas la colección a cada sheet
            new FormularioIngresoExport($this->data1),
            new SeguimientoEstadosOrdenServicioSheet($this->data2),
            new SeguimientoGuardadoOrdenServicioSheet($this->data3),
            new LaborariosMedicosSheet($this->data4),
        ];
    }
}
