<?php

namespace App\Http\Controllers;

use App\Models\SigContrato;
use Illuminate\Http\Request;

class SigContratoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = SigContrato::join('sig_estados_contrato', 'sig_estados_contrato.id', '=', 'sig_contratos.estado_contrato_id')
            ->select(
                'sig_contratos.id',
                'sig_contratos.numero',
                'sig_contratos.descripcion',
                'sig_estados_contrato.nombre as estado_contrato_id',
                'sig_contratos.created_at',
            )
            ->orderBy('sig_contratos.id', 'desc')
            ->paginate($cantidad);
        return response()->json($result);
    }

    public function lista()
    {
        $result = SigContrato::join('sig_estados_contrato', 'sig_estados_contrato.id', '=', 'sig_contratos.estado_contrato_id')
            ->select(
                'sig_contratos.id',
                'sig_contratos.numero',
                'sig_contratos.descripcion',
                'sig_estados_contrato.nombre as estado',
                'sig_contratos.created_at',
            )
            ->orderBy('sig_contratos.id', 'desc')
            ->get();
        return response()->json($result);
    }

    public function contratosactivos()
    {
        $result = SigContrato::join('sig_estados_contrato', 'sig_estados_contrato.id', '=', 'sig_contratos.estado_contrato_id')
            ->where('sig_estados_contrato.nombre', '=', 'Activo')
            ->select(
                'sig_contratos.numero',
                'sig_contratos.descripcion',
                'sig_contratos.id',
                'sig_contratos.created_at',
                'sig_estados_contrato.nombre as estado_contrato_id',
                'sig_estados_contrato.id as estado_id',
            )

            ->orderBy('sig_contratos.id', 'desc')
            ->get();
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
        if ($operador == 'Contiene') {
            $operador = 'like';
            $valor = '%' . $valor . '%';
        } else if ($operador == 'Igual a') {
            $operador = '=';
        } else if ($operador == 'Igual a fecha') {
            $operador = '=';
            $result = SigContrato::join('sig_estados_contrato', 'sig_estados_contrato.id', '=', 'sig_contratos.estado_contrato_id')
                ->whereDate('sig_contratos.' . $campo, $operador, $valor)
                ->select(
                    'sig_contratos.id',
                    'sig_contratos.numero',
                    'sig_contratos.descripcion',
                    'sig_estados_contrato.nombre as estado_contrato_id',
                    'sig_contratos.created_at',
                )
                ->orderBy('sig_contratos.id', 'desc')
                ->paginate();
            return response()->json($result);
        } else if ($operador == 'Entre') {
            $result = SigContrato::join('sig_estados_contrato', 'sig_estados_contrato.id', '=', 'sig_contratos.estado_contrato_id')
                ->whereBetween('sig_contratos.' . $campo, [$valor, $valor2])
                ->select(
                    'sig_contratos.id',
                    'sig_contratos.numero',
                    'sig_contratos.descripcion',
                    'sig_estados_contrato.nombre as estado_contrato_id',
                    'sig_contratos.created_at',
                )
                ->orderBy('sig_contratos.id', 'desc')
                ->paginate();
            return response()->json($result);
        }
        $result = SigContrato::join('sig_estados_contrato', 'sig_estados_contrato.id', '=', 'sig_contratos.estado_contrato_id')
            ->where('sig_contratos.' . $campo, $operador, $valor)
            ->select(
                'sig_contratos.id',
                'sig_contratos.numero',
                'sig_contratos.descripcion',
                'sig_estados_contrato.nombre as estado_contrato_id',
                'sig_contratos.created_at',
            )
            ->orderBy('sig_contratos.id', 'desc')
            ->paginate();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $contrato = new SigContrato;
            $contrato->numero = $request->numero;
            $contrato->descripcion = $request->descripcion;
            if ($contrato->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Este contrato ya se encuentra registrado']);
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
                $result = SigContrato::find($valor);

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
        // try {
        $contrato = SigContrato::find($id);
        if ($request->input('numero')) {
            $contrato->numero = $request->numero;
            $contrato->descripcion = $request->descripcion;
            $contrato->estado_contrato_id = $request->estado;
        }
        if ($contrato->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
        }
        // } catch (\Exception$e) {
        //     return response()->json(['status' => 'error', 'message' => 'Este contrato ya se encuentra registrado']);
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $contrato = SigContrato::find($id);
        if ($contrato->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = SigContrato::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
