<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EstadosFirma;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\ResponsablesEstadosModel;
use App\Models\ClientesSeguimientoEstado;
use Exception;

class EstadosFirmaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        /* $hoy = Carbon::now();
        $diaSemana = $hoy->dayName;
        formatoFechaCarbon= 2024-10-28T20:06:44.820417Z; */


        $result = EstadosFirma::select(
            'id',
            DB::raw("CONCAT(posicion, '. ', nombre) AS nombre"),
            'color',
            'tiempo_respuesta',
            'posicion'
        )->orderBy('posicion')
            /*  ->orderByRaw("
        TRY_CAST(LEFT(nombre, CHARINDEX('.', nombre + '.') - 1) AS INT), nombre") */
            ->get();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $estadosFirma = new EstadosFirma;
            $estadosFirma->nombre = $request->nombre;
            $estadosFirma->color = $request->color;
            $estadosFirma->tiempo_respuesta = $request->tiempo_respuesta;
            $estadosFirma->save();
            if ($request->responsables) {
                foreach ($request->responsables as $item) {
                    $responsableEstado = new ResponsablesEstadosModel;
                    $responsableEstado->usuario_id = $item["usuario_id"];
                    $responsableEstado->estado_firma_id = $estadosFirma->id;
                    $responsableEstado->save();
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa', 'id' => $estadosFirma->id]);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }
    public function indexResponsableEstado($estado)
    {
        $usuarios = ResponsablesEstadosModel::join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_clientes_responsable_estado.usuario_id')
            ->where('usr_app_clientes_responsable_estado.estado_firma_id', '=', $estado)
            ->select(
                'usuario_id',
                DB::raw("CONCAT(nombres,' ',apellidos)  AS nombre"),
                'usr.usuario as email'
            )
            ->get();
        return response()->json($usuarios);
    }
    public function indexResponsableEstado2()
    {
        try {
            $usuarios = ResponsablesEstadosModel::join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_clientes_responsable_estado.usuario_id')
                ->select(
                    'usuario_id',
                    DB::raw("CONCAT(usr.nombres, ' ', usr.apellidos) AS nombre"),
                    'usr.usuario as email'
                )
                ->distinct()
                ->get();

            return response()->json($usuarios);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Buscar el estado y actualizar sus datos básicos
            $result = EstadosFirma::find($id);
            $result->nombre = $request->nombre;
            $result->color = $request->color;
            $result->tiempo_respuesta = $request->tiempo_respuesta;
            $result->save();
            $responsablesExistentes = ResponsablesEstadosModel::where('estado_firma_id', $result->id)
                ->pluck('usuario_id')
                ->toArray();

            $responsablesNuevos = collect($request->responsables)->pluck('usuario_id')->toArray();
            $responsablesParaCrear = array_diff($responsablesNuevos, $responsablesExistentes);
            $responsablesParaEliminar = array_diff($responsablesExistentes, $responsablesNuevos);

            foreach ($responsablesParaCrear as $usuario_id) {
                ResponsablesEstadosModel::create([
                    'usuario_id' => $usuario_id,
                    'estado_firma_id' => $result->id
                ]);
            }

            ResponsablesEstadosModel::where('estado_firma_id', $result->id)
                ->whereIn('usuario_id', $responsablesParaEliminar)
                ->delete();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa', 'id' => $result->id]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente', 'error' => $e->getMessage()]);
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
        $result = EstadosFirma::find($id);
        if (!$result) {
            return response()->json(['message' => 'El radicado no existe.'], 404);
        }
        try {
            $result->delete();
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el estado, por favor intenta nuevamente o verifica que no existan registros creados con este estado', 'error']);
        }
    }
    public function allClientesSeguimientoEstado()
    {
        $result = ClientesSeguimientoEstado::all();
        return response()->json($result);
    }

    public function byId($id)
    {
        return EstadosFirma::select(
            'id',
            'nombre',
            'color',
            'tiempo_respuesta'
        )->where('usr_app_estados_firma.id', $id)
            ->first();
    }

    public function byOrder($order)
    {
        return EstadosFirma::select(
            'id',
            'nombre',
            'color',
            'tiempo_respuesta'
        )->where('usr_app_estados_firma.posicion', $order)
            ->first();
    }

    public function cambiarOrden(Request $request)
    {

        $estadosArray = $request['estados'];
        foreach ($estadosArray as $index => $estado) {
            try {
                $result = EstadosFirma::find($estado['id']);
                $result->posicion = $index + 1;
                $result->save();
            } catch (Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Hubo un problema al cambiar el orden de los estados']);
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Registros actualizados de manera exitosa']);
    }
}