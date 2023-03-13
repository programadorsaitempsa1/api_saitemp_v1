<?php

namespace App\Http\Controllers;

use App\Models\SigEmpleados;
use App\Models\user;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SigEmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $id = auth()->user();
        if ($id->rol_id == 1 || $id->rol_id == 2) {

            $result = SigEmpleados::join('sig_estado_empleados', 'sig_estado_empleados.id', '=', 'sig_empleados.estado_empleado_id')
                ->join('sig_tipos_documento_identidad', 'sig_tipos_documento_identidad.id', '=', 'sig_empleados.tipo_documento_identidad_id')
                ->join('sig_cargos', 'sig_cargos.id', '=', 'sig_empleados.sig_cargo_id')
                ->select(
                    'sig_empleados.id',
                    'sig_empleados.nombres',
                    'sig_empleados.apellidos',
                    'sig_tipos_documento_identidad.nombre as tipo_documento_identidad_id',
                    'sig_empleados.documento_identidad',
                    'sig_estado_empleados.nombre as estado_empleado_id',
                    'sig_cargos.nombre as sig_cargo_id',
                )
                // ->orderBy('sig_contrato_empleados.id', 'ASC')
                ->paginate($cantidad);
            return response()->json($result);
        } else {
            $result = SigEmpleados::join('sig_estado_empleados', 'sig_estado_empleados.id', '=', 'sig_empleados.estado_empleado_id')
                ->join('sig_tipos_documento_identidad', 'sig_tipos_documento_identidad.id', '=', 'sig_empleados.tipo_documento_identidad_id')
                ->join('sig_cargos', 'sig_cargos.id', '=', 'sig_empleados.sig_cargo_id')
                ->where('sig_empleados.documento_identidad', '=', $id->documento_identidad)
                ->select(
                    'sig_empleados.id',
                    'sig_empleados.nombres',
                    'sig_empleados.apellidos',
                    'sig_tipos_documento_identidad.nombre as tipo_documento_identidad_id',
                    'sig_empleados.documento_identidad',
                    'sig_estado_empleados.nombre as estado_empleado_id',
                    'sig_cargos.nombre as sig_cargo_id',
                )
                // ->orderBy('sig_contrato_empleados.id', 'ASC')
                ->paginate($cantidad);
            return response()->json($result);

        }
    }

    public function getEmpleadosSST()
    {
        $result = SigEmpleados::join('sig_cargos', 'sig_cargos.id', '=', 'sig_empleados.sig_cargo_id')
            ->where('sig_cargos.id', '=', 5)
            ->select(
                'sig_empleados.id',
                'sig_empleados.nombres',
                'sig_empleados.apellidos',
                'sig_empleados.documento_identidad',
            )
            ->orderBy('sig_empleados.id', 'DESC')
            ->get();
        return response()->json($result);
    }

    public function sigempleadoslista()
    {
        $result = SigEmpleados::join('sig_estado_empleados', 'sig_estado_empleados.id', '=', 'sig_empleados.estado_empleado_id')
            ->join('sig_tipos_documento_identidad', 'sig_tipos_documento_identidad.id', '=', 'sig_empleados.tipo_documento_identidad_id')
            ->join('sig_cargos', 'sig_cargos.id', '=', 'sig_empleados.sig_cargo_id')
            ->select(
                'sig_empleados.id',
                'sig_empleados.nombres',
                'sig_empleados.apellidos',
                'sig_empleados.documento_identidad',
            )
            ->orderBy('sig_empleados.id', 'DESC')
            ->get();
        return response()->json($result);
    }

    public function getEmpleadoEncargado()
    {
        {
            $id = auth()->user();
            $contrato = $this->contrato();
            if ($id->rol_id == 1 || $id->rol_id == 2) {

                $result = SigEmpleados::join('sig_estado_empleados', 'sig_estado_empleados.id', '=', 'sig_empleados.estado_empleado_id')
                    ->join('sig_tipos_documento_identidad', 'sig_tipos_documento_identidad.id', '=', 'sig_empleados.tipo_documento_identidad_id')
                    ->join('sig_cargos', 'sig_cargos.id', '=', 'sig_empleados.sig_cargo_id')
                    ->where('sig_cargos.id', '=', 4)
                    ->select(
                        'sig_empleados.id',
                        'sig_empleados.nombres',
                        'sig_empleados.apellidos',
                        'sig_empleados.documento_identidad',
                    )
                    ->orderBy('sig_empleados.id', 'DESC')
                    ->get();
                return response()->json($result);
            } else {
                $result = SigEmpleados::join('sig_estado_empleados', 'sig_estado_empleados.id', '=', 'sig_empleados.estado_empleado_id')
                    ->join('sig_tipos_documento_identidad', 'sig_tipos_documento_identidad.id', '=', 'sig_empleados.tipo_documento_identidad_id')
                    ->join('sig_cargos', 'sig_cargos.id', '=', 'sig_empleados.sig_cargo_id')
                    ->join('sig_contrato_empleados', 'sig_contrato_empleados.empleado_id', '=', 'sig_empleados.id')
                    ->where('sig_empleados.estado_empleado_id', '=', 1)
                    ->where('sig_cargos.id', '=', 4)
                    ->select(
                        'sig_empleados.id',
                        'sig_empleados.nombres',
                        'sig_empleados.apellidos',
                        'sig_empleados.documento_identidad',
                    )
                    ->orderBy('sig_empleados.id', 'DESC')
                    ->get();
                return response()->json($result);

            }
        }
    }

    public function contrato()
    {
        $id = auth()->id();
        $contrato = user::join("sig_usuarios_contratos", "sig_usuarios_contratos.usuario_id", "=", "usuarios.id")
            ->join("sig_contratos", "sig_contratos.id", "=", "sig_usuarios_contratos.contrato_id")
            ->where('usuarios.id', '=', $id)
            ->select(
                "sig_contratos.id as contrato_id",
            )
            ->get();
        return $contrato;
    }

    public function empleadoById($id)
    {
        $result = SigEmpleados::join('sig_cargos', 'sig_cargos.id', '=', 'sig_empleados.sig_cargo_id')
            ->where('documento_identidad', '=', $id)
            ->select(
                'sig_empleados.nombres',
                'sig_empleados.apellidos',
                'sig_empleados.firma',
                'sig_cargos.nombre as cargo',
            )
            ->get();
        return response()->json($result);
    }

    public function filtro(Request $request)
    {
        $operador = $request->operador;
        $valor = $request->valor;
        if ($request->operador == 'Contiene') {
            $operador = 'like';
            $valor = '%' . $request->valor . '%';
        } else if ($request->operador == 'Igual a') {
            $operador = '=';
        }
        $result = SigEmpleados::join('sig_estado_empleados', 'sig_estado_empleados.id', '=', 'sig_empleados.estado_empleado_id')
            ->join('sig_tipos_documento_identidad', 'sig_tipos_documento_identidad.id', '=', 'sig_empleados.tipo_documento_identidad_id')
            ->join('sig_cargos', 'sig_cargos.id', '=', 'sig_empleados.sig_cargo_id')
            ->where('sig_empleados.' . $request->campo, $operador, $valor)
            ->select(
                'sig_empleados.id',
                'sig_empleados.nombres',
                'sig_empleados.apellidos',
                'sig_tipos_documento_identidad.nombre as tipo_documento_identidad_id',
                'sig_empleados.documento_identidad',
                'sig_estado_empleados.nombre as estado_empleado_id',
                'sig_cargos.nombre as sig_cargo_id',
            )
            ->orderBy('sig_empleados.id', 'DESC')
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
            $empleado = new SigEmpleados;

            if ($request->hasFile('firma')) {

                $nombreArchivoOriginal = $request->file('firma')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('firma')->move($carpetaDestino, $nuevoNombre);
                $empleado->firma = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }

            $empleado->nombres = $request->nombres;
            $empleado->apellidos = $request->apellidos;
            $empleado->documento_identidad = $request->documento_identidad;
            // $empleado->estado_empleado_id = $request->estado_empleado_id;
            $empleado->tipo_documento_identidad_id = $request->tipo_documento_identidad_id;
            $empleado->sig_cargo_id = $request->sig_cargo_id;
            if ($empleado->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro, por favor intente nuevamente']);
            }
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Ya se encuentra registrado un empleado con este número de documento']);
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
                $result = SigEmpleados::find($valor);

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
        $empleado = SigEmpleados::find($id);

        try {
            if ($request->hasFile('firma')) {

                if ($empleado->firma != null) {
                    $rutaArchivo = base_path('public') . $empleado->firma;
                    if (file_exists($rutaArchivo)) {
                        unlink($rutaArchivo);
                    }
                }

                $nombreArchivoOriginal = $request->file('firma')->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                $request->file('firma')->move($carpetaDestino, $nuevoNombre);
                $empleado->firma = ltrim($carpetaDestino, '.') . $nuevoNombre;
            }

            $empleado->nombres = $request->nombres;
            $empleado->apellidos = $request->apellidos;
            $empleado->documento_identidad = $request->documento_identidad;
            $empleado->estado_empleado_id = $request->estado_empleado_id;
            $empleado->tipo_documento_identidad_id = $request->tipo_documento_identidad_id;
            $empleado->sig_cargo_id = $request->sig_cargo_id;
            if ($empleado->save()) {
                return response()->json(['status' => 'success', 'message' => 'Registro actualizado exitosamente']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al actualizar el registro, por favor intente nuevamente']);
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
        $empleado = SigEmpleados::find($id);
        if ($empleado->firma != null) {
            $rutaArchivo = base_path('public') . $empleado->firma;
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
        }
        if ($empleado->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }

    public function borradomasivo(Request $request)
    {
        try {
            for ($i = 0; $i < count($request->id); $i++) {
                $result = SigEmpleados::find($request->id[$i]);
                $result->delete();
            }
            return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
        } catch (\Exception$e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar el registro, por favor intente nuevamente']);
        }
    }
}
