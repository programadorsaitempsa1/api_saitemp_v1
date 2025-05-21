<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientesAnalistaModel;
use Illuminate\Support\Facades\DB;

class ClientesAnalistaController extends Controller
{
    public function index($cantidad)
    {
        $result = ClientesAnalistaModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_clientes_analista.analista_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->join('select * from usr_app_clientes as cli', 'cli.id', 'usr_app_clientes_analista.cliente_id')
            ->select(
                'usr_app_clientes_analista.id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
                'cli.razon_social as razon_social'
            )->orderby('usr_app_clientes_analista.id', 'DESC')->paginate($cantidad);
        return response()->json($result);
    }

    public function byid($id)
    {
        $result = ClientesAnalistaModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_clientes_analista.profesional_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->join('usr_app_sector_clientes as cli', 'cli.id', 'usr_app_clientes_analista.cliente_id')
            ->where('usr_app_clientes_analista.profesional_id', '=', $id)
            ->select(
                'usr_app_clientes_analista.id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
                'cli.razon_social'
            )->orderby('usr_app_clientes_analista.id', 'DESC')->paginate();
        return response()->json($result);
    }

    public function create(Request $request)
    {
        $asunto = 'Asignación sector económico';
        $cuerpo_mensaje = 'Se le ha asignado un nuevo sector económico';
        $profesionales = $request->profesionales;
        $cliente = $request->cliente;
        foreach ($profesionales as $profesional) {
            $profesional_sector = new ClientesAnalistaModel;
            $profesional_sector->sector_economico_id = $cliente['id'];
            $profesional_sector->profesional_id = $profesional['usuario_id'];
            $profesional_sector->save();
            $cuerpo_mensaje .= ' Sector: ' . $cliente['nombre'];
            $resultado =  $this->notificaProfesional($asunto, $cuerpo_mensaje, $profesional['correo'], 62, $profesional_sector->id);
        }
        // return response()->json(['status' => 'success', 'message' => 'Reguistro guardado de manera exitosa.']);
        return $resultado;
    }

    public function borradomasivo(Request $request)
    {
        $result = $request->all();
        for ($i = 0; $i < count($request->id); $i++) {
            $result = ClientesAnalistaModel::find($request->id[$i]);
            $result->delete();
        }
        return response()->json(['status' => 'success', 'message' => 'Registros eliminados exitosamente']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $result = ClientesAnalistaModel::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar registro.']);
        }
    }
}