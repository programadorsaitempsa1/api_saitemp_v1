<?php

namespace App\Http\Controllers;

use App\Models\Costo;
use Illuminate\Http\Request;

class CostoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = Costo::join('unidades_medida', 'unidades_medida.id', '=', 'costos.id_unidad_medida')
            ->join('codigos_item', 'codigos_item.id', '=', 'costos.id_codigo')
            ->join('sig_zonas', 'sig_zonas.id', '=', 'costos.id_subregion')
            ->select(
                'costos.id',
                'codigos_item.nombre as id_codigo',
                'costos.item',
                'costos.descripcion',
                'unidades_medida.nombre as id_unidad_medida',
                'costos.valor_unitario',
                'sig_zonas.nombre as id_subregion',
            )
            ->orderBy('id', 'DESC')
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function costoitemcategoria($categoria)
    {
        $result = Costo::join('unidades_medida', 'unidades_medida.id', '=', 'costos.id_unidad_medida')
            ->join('codigos_item', 'codigos_item.id', '=', 'costos.id_codigo')
            ->join('sig_zonas', 'sig_zonas.id', '=', 'costos.id_subregion')
            ->where('costos.id_codigo', '=', $categoria)
            ->select(
                'costos.id',
                'codigos_item.nombre as id_codigo',
                'costos.item',
                'costos.descripcion',
                'unidades_medida.nombre as id_unidad_medida',
                'costos.valor_unitario',
                'sig_zonas.nombre as id_subregion',
            )
            ->orderBy('id', 'DESC')
            ->get($categoria);
        return response()->json($result);
    }

    public function filtro($cadena)
    {
        $consulta = base64_decode($cadena);
        $valores = explode("/", $consulta);
        $campo = $valores[0];
        $operador = $valores[1];
        $valor = $valores[2];
        $valor2 = $valores[3];

        // return $campo.$operador.$valor.$valor2;

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
        } else if ($operador == 'Entre') {
            $result = Costo::join('unidades_medida', 'unidades_medida.id', '=', 'costos.id_unidad_medida')
                ->join('codigos_item', 'codigos_item.id', '=', 'costos.id_codigo')
                ->join('sig_zonas', 'sig_zonas.id', '=', 'costos.id_subregion')
                ->whereBetween('costos.' . $campo, [$valor, $valor2])
                ->select(
                    'costos.id',
                    'codigos_item.nombre as id_codigo',
                    'costos.item',
                    'costos.descripcion',
                    'unidades_medida.nombre as id_unidad_medida',
                    'costos.valor_unitario',
                    'sig_zonas.nombre as id_subregion',
                )
                ->orderBy('costos.id', 'desc')
                ->paginate();
            return response()->json($result);
        }
        $result = Costo::join('unidades_medida', 'unidades_medida.id', '=', 'costos.id_unidad_medida')
            ->join('codigos_item', 'codigos_item.id', '=', 'costos.id_codigo')
            ->join('sig_zonas', 'sig_zonas.id', '=', 'costos.id_subregion')
            ->where('costos.' . $campo, $operador, $valor)
            ->select(
                'costos.id',
                'codigos_item.nombre as id_codigo',
                'costos.item',
                'costos.descripcion',
                'unidades_medida.nombre as id_unidad_medida',
                'costos.valor_unitario',
                'sig_zonas.nombre as id_subregion',
            )
        // ->orderBy('id', 'DESC')
            ->paginate();
        return response()->json($result);
    }

    public function cargaMasiva(Request $request)
    {
        try {
            $documento = $request->file('cargamasiva')->getContent();
            $lineas = explode("\n", $documento);
            $numero_registros = count($lineas) - 1;
            $posicion = 0;
            $valores = explode(";", $lineas[0]);
            $errorItem = "Los items";
            $valida_errores = false;
            for ($i = 1; $i < $numero_registros; $i++) {
                try {
                    $valores = explode(";", $lineas[$i]);
                    $objeto = new Costo;
                    $objeto->id_codigo = $valores[$posicion];
                    $objeto->item = $valores[$posicion + 1];
                    $objeto->descripcion = $valores[$posicion + 2] == '' ? null : $valores[$posicion + 2];
                    $objeto->id_unidad_medida = $valores[$posicion + 3];
                    $objeto->id_subregion = $valores[$posicion + 4];
                    $objeto->valor_unitario = $valores[$posicion + 5] == '' ? null : $valores[$posicion + 5];
                    $objeto->save();
                } catch (\Exception$e) {
                    $valida_errores = true;
                    $errorItem .= ', ' . $objeto->item;
                }
            }
            if ($valida_errores) {
                return response()->json(['status' => 'duplicate', 'message' => $errorItem . ' no puedieron ser guardados ya que presentan problemas' .
                    ' de incompatibilidad, el texto no debe tener ";" ni saltos de linea, por favor cargue solo esos item nuevamente.']);
            } else {
                return response()->json(['status' => 'success', 'message' => 'Registros guardados exitosamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al guardar los registros, '.
            'por favor verifique el archivo cargado e intente nuevamente, los registros no deben tener saltos de linea ni ";"']);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $costo = new Costo;
            $costo->id_codigo = $request->codigo;
            $costo->item = $request->item;
            $costo->descripcion = $request->descripcion;
            $costo->id_unidad_medida = $request->unidad_medida;
            $costo->valor_unitario = $request->valor_unitario;
            $costo->id_subregion = $request->id_subregion;
            if ($costo->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => $e]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function actualizacionmasiva(Request $request)
    {
        try {
            foreach ($request->id as $valor) {
                $result = Costo::find($valor);

                foreach ($request->campos as $clave => $valor) {
                    if ($valor != "") {
                        $result->$clave = $valor;
                    }
                }
                $result->save();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros actualizados exitosamente']);

        } catch (\Exception$e) {
            return response()->json($e);
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $costo = Costo::find($id);
        try {
            $costo->id_codigo = $request->codigo;
            $costo->item = $request->item;
            $costo->descripcion = $request->descripcion;
            $costo->id_unidad_medida = $request->unidad_medida;
            $costo->valor_unitario = $request->valor_unitario;
            $costo->id_subregion = $request->id_subregion;
            if ($costo->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => $e]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $costo = Costo::find($id);
            if ($costo->delete()) {
                return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => $e]);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = Costo::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
