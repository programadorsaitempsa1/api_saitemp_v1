<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FormularioIngresoResponsable;
use Illuminate\Support\Facades\DB;

class EstadoResponsableOrdenServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = FormularioIngresoResponsable::join('usr_app_usuarios as user', 'user.id', 'usr_app_formulario_ingreso_responsable.usuario_id')
            ->join('usr_app_usuarios_internos as useri', 'useri.usuario_id', 'user.id')
            ->join('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso_responsable.estado_ingreso_id')
            ->select(
                'usr_app_formulario_ingreso_responsable.id',
                DB::raw("CONCAT(useri.nombres,' ',useri.apellidos)  AS usuario"),
                'est.nombre as estado',
            )
            ->orderby('useri.nombres')
            ->paginate($cantidad);
        return $result;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

        try {
            DB::beginTransaction();
            $estado_responsable = $request->all();
            foreach ($estado_responsable[0] as  $usuario) {
                foreach ($estado_responsable[1] as  $estado) {
                    $result = new FormularioIngresoResponsable;
                    $result->usuario_id = $usuario['id'];
                    $result->estado_ingreso_id = $estado['id'];
                    $result->save();
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } catch (\Exception $e) {
            DB::rollback();
            // return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
        }
    }

    public function filtro($cadena, $cantidad = null)
    {
        if ($cantidad == null) {
            $cantidad = 10;
        }
        $cadenaJSON = base64_decode($cadena);
        $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
        $arrays = explode('/', $cadenaUTF8);
        $arraysDecodificados = array_map('json_decode', $arrays);

        $campo = $arraysDecodificados[0];

        $operador = $arraysDecodificados[1];
        $valor_comparar = $arraysDecodificados[2];
        $valor_comparar2 = $arraysDecodificados[3];
        $query = FormularioIngresoResponsable::join('usr_app_usuarios as user', 'user.id', 'usr_app_formulario_ingreso_responsable.usuario_id')
            ->join('usr_app_usuarios_internos as useri', 'useri.usuario_id', 'user.id')
            ->join('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso_responsable.estado_ingreso_id')
            ->select(
                'usr_app_formulario_ingreso_responsable.id',
                DB::raw("CONCAT(useri.nombres,' ',useri.apellidos)  AS usuario"),
                'est.nombre as estado',
            )
            ->orderby('useri.nombres');
        $numElementos = count($campo);


        for ($i = 0; $i < $numElementos; $i++) {
            $campoActual = $campo[$i];
            $operadorActual = $operador[$i];
            $valorCompararActual = $valor_comparar[$i];
            $esUsuario = $campoActual === 'usuario';

            $prefijoCampo = '';
            if ($campoActual === 'usuario') {
                $prefijoCampo = 'useri.';
                $campoActual = 'nombres';
                $campoActual2 = 'apellidos';
            } elseif ($campoActual === 'estado') {
                $prefijoCampo = 'est.';
                $campoActual = 'nombre';
            }


            switch ($operadorActual) {
                case 'Igual a':
                    $query->where($prefijoCampo . $campoActual, '=', $valorCompararActual);
                    if ($esUsuario) {
                        $query->orWhere($prefijoCampo . $campoActual2, '=', $valorCompararActual);
                    }
                    break;
                case 'Contiene':
                    $query->where($prefijoCampo . $campoActual, 'like', '%' . $valorCompararActual . '%');
                    if ($esUsuario) {
                        $query->orWhere($prefijoCampo . $campoActual2, 'like', '%' . $valorCompararActual . '%');
                    }
                    break;
                default:
                    $campoActual2 = '';
                    break;
            }
        }
        $resultados = $query->paginate($cantidad); 

        return $resultados;
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = FormularioIngresoResponsable::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
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
            $result = FormularioIngresoResponsable::find($id);
            $result->delete();
            return response()->json(["status" => "success", "message" => "Registro eliminado exitosamente."]);
        } catch (\Exception $e) {
            return response()->json(["status" => "success", "message" => "Error al eliminar el registro."]);
        }
    }
}
