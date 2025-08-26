<?php

namespace App\Traits;

use App\Models\FormularioIngresoSeguimientoEstado;
use App\Models\FormularioIngresoResponsable;
use App\Models\formularioGestionIngreso;

trait ActualizaEstadoOrdenServicio
{

    public function actualizaestadoingreso($item_id, $estado_id, $responsable_id = null,  $responsable_actual = null, $estado_inicial = null)
    {

        $user = $this->getUserRelaciones();
        $user = $user->getData(true);
        $usuarios = FormularioIngresoResponsable::where('usr_app_formulario_ingreso_responsable.estado_ingreso_id', '=', $estado_id)
            ->join('usr_app_usuarios as usr', 'usr.id', '=', 'usr_app_formulario_ingreso_responsable.usuario_id')
            ->select(
                'usuario_id',
                'usr.nombres',
                'usr.apellidos'
            )
            ->get();

        // Obtener el número total de responsables
        $numeroResponsables = $usuarios->count();

        // Obtener el registro de ingreso
        $registro_ingreso = formularioGestionIngreso::where('usr_app_formulario_ingreso.id', '=', $item_id)
            ->first();

        $permisos = $this->permisos();

        if ($registro_ingreso->responsable_id != null && $registro_ingreso->responsable_id != $user['id'] && !in_array('31', $permisos)) {
            return response()->json(['status' => 'error', 'message' => 'Solo el responsable puede realizar esta acción.']);
        }

        // Asignar a cada registro de ingreso un responsable
        $indiceResponsable = $registro_ingreso->id % $numeroResponsables; // Calcula el índice del responsable basado en el ID del registro
        $responsable = $usuarios[$indiceResponsable];

        $this->eventoSocket($responsable->usuario_id);

        $seguimiento_estado = new FormularioIngresoSeguimientoEstado;
        $seguimiento_estado->responsable_inicial =  $registro_ingreso->responsable != null ?  str_replace("null", "", $registro_ingreso->responsable) : str_replace("null", "", $responsable_actual);
        $seguimiento_estado->responsable_final = $responsable->nombres . ' ' . str_replace("null", "", $responsable->apellidos);;
        $seguimiento_estado->estado_ingreso_inicial = $estado_inicial != null ? $estado_inicial : $registro_ingreso->estado_ingreso_id;
        $seguimiento_estado->estado_ingreso_final =   $estado_id;
        $seguimiento_estado->formulario_ingreso_id =  $item_id;
        $seguimiento_estado->actualiza_registro =   $user['nombres'] . ' ' .  $user['apellidos'];
        $seguimiento_estado->save();

        // Actualizar el registro de ingreso con el estado y el responsable
        $registro_ingreso->estado_ingreso_id = $estado_id;
        $registro_ingreso->asignacion_manual = 0;
        $registro_ingreso->responsable_id = $responsable->usuario_id;
        $registro_ingreso->responsable = $responsable->nombres . ' ' . str_replace("null", "", $responsable->apellidos);
        if ($registro_ingreso->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa.']);
        }

        return response()->json(['status' => 'error', 'message' => 'Error al actualizar registro.']);
    }
}
