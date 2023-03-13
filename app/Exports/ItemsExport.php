<?php

namespace App\Exports;

use App\Models\SigItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\Exportable;


// , WithColumnWidths
class ItemsExport implements FromCollection, WithHeadings, WithColumnFormatting, WithStyles, ShouldAutoSize
{


    use Exportable;

    protected $items;

    public function __construct($items = null)
    {
        $this->items = $items;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // return $this->items ?: SigItem::all();
        return $this->items; // ?: SigItem::select("orden_trabajo", "item_id", "codigo", "subregion", "unidad_medida", "valor_unitario", "cantidad", "valor_total_item", "descripcion", "created_at")->get();
        // return $result;
    }

    public function headings(): array
    {
        return ["NÚMERO DE ORDEN DE TRABAJO", "ITEM", "CATEGORÍA", "SUBREGIÓN", "CONTRATO", "UNIDAD DE MEDIDA", "VALOR UNITARIO", "CANTIDAD", "TOTAL ITEM", "DESCRIPCIÓN", "ENCARGADO", "FECHA EJECUCIÓN"];
    }

    public function columnFormats(): array
    {
        return [
            'g' => NumberFormat::FORMAT_CURRENCY_USD,
            'i' => NumberFormat::FORMAT_CURRENCY_USD,
            'K' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    // public function columnWidths(): array
    // {
    //     return [

    //     ];
    // }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true, 'size' => 14]],

           

            // Styling a specific cell by coordinate.
            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }

}
