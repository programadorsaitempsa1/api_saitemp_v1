<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;

// class ItemController extends Controller
// {
//     //
// }

namespace App\Http\Controllers;

use App\Exports\ItemsExport;
use App\Models\SigItem;

class ItemController extends Controller
{
    public function export($cadena)
    {
        try{
        $consulta = base64_decode($cadena);
        $valores = explode("/", $consulta);
        $campo = $valores[0];
        $operador = $valores[1];
        $valor = $valores[2];

        if ($operador == 'Contiene') {
            $operador = 'like';
            $valor = '%' . $valor . '%';
        } else if ($operador == 'Igual a') {
            $operador = '=';
        } else if ($operador == 'Menor que') {
            $operador = '<';
        } else if ($operador == 'Menor o igual que') {
            $operador = '<=';
        } else if ($operador == 'Mayor que') {
            $operador = '>';
        } else if ($operador == 'Mayor o igual que') {
            $operador = '>=';
        } else if ($operador == 'Igual a número') {
            $operador = '=';
        }
        $items = SigItem::where('sig_items.' . $campo, $operador, $valor)
            ->select(

                "orden_trabajo",
                "item",
                "categoria",
                "subregion",
                "contrato",
                "unidad_medida",
                "valor_unitario",
                "cantidad",
                "valor_total_item",
                "descripcion",
                "encargado",
                "created_at"
            )
            ->get();
        return (new ItemsExport($items))->download('items.xlsx', \Maatwebsite\Excel\Excel::XLSX);
            }catch(\Exception $e){
                // return response()->json(['status'=>'error','message'=>'Por favor']);
            }
    }

    public function index($cantidad)
    {
        $result = SigItem::select("orden_trabajo", "item", "categoria", "subregion",  "contrato", "unidad_medida", "valor_unitario", "cantidad", "valor_total_item", "descripcion", "encargado", "created_at")
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function filtro($cadena)
    {
        $consulta = base64_decode($cadena);
        $valores = explode("/", $consulta);
        $campo = $valores[0];
        $operador = $valores[1];
        $valor = $valores[2];

        if ($operador == 'Contiene') {
            $operador = 'like';
            $valor = '%' . $valor . '%';
        } else if ($operador == 'Igual a') {
            $operador = '=';
        } else if ($operador == 'Menor que') {
            $operador = '<';
        } else if ($operador == 'Menor o igual que') {
            $operador = '<=';
        } else if ($operador == 'Mayor que') {
            $operador = '>';
        } else if ($operador == 'Mayor o igual que') {
            $operador = '>=';
        } else if ($operador == 'Igual a número') {
            $operador = '=';
        }
        $items = SigItem::where('sig_items.' . $campo, $operador, $valor)
            ->select(

                "orden_trabajo",
                "item",
                "categoria",
                "subregion",
                "contrato",
                "unidad_medida",
                "valor_unitario",
                "cantidad",
                "valor_total_item",
                "descripcion",
                "encargado",
                "created_at"
            )
            ->paginate();
        return response()->json($items);
    }
}
