<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientesProfesionalModel;
use Illuminate\Support\Facades\DB;
use App\Traits\AutenticacionGuard;

class ClientesProfesionalController extends Controller
{
    use AutenticacionGuard;
    public function index($cantidad)
    {
        $result = ClientesProfesionalModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_clientes_profesional.profesional_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->join('usr_app_clientes as cli', 'cli.id', 'usr_app_clientes_profesional.cliente_id')
            ->select(
                'usr_app_clientes_profesional.id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
                DB::raw('COALESCE(cli.nit, cli.numero_identificacion) as nit'),
                'cli.razon_social'
            )->orderby('usr_app_clientes_profesional.id', 'DESC')->paginate($cantidad);
        return response()->json($result);
    }

    public function byid($id)
    {
        $result = ClientesProfesionalModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_clientes_profesional.profesional_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->join('usr_app_clientes as cli', 'cli.id', 'usr_app_clientes_profesional.cliente_id')
            ->where('usr_app_clientes_profesional.profesional_id', '=', $id)
            ->select(
                'usr_app_clientes_profesional.id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
                DB::raw('COALESCE(cli.nit, cli.numero_identificacion) as nit'),
                'cli.razon_social'
            )->orderby('usr_app_clientes_profesional.id', 'DESC')->paginate();
        return response()->json($result);
    }

    public function byCliente($id)
    {
        $result = ClientesProfesionalModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_clientes_profesional.profesional_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->join('usr_app_clientes as cli', 'cli.id', 'usr_app_clientes_profesional.cliente_id')
            ->where('usr_app_clientes_profesional.cliente_id', '=', $id)
            ->select(
                'usr_app_clientes_profesional.id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
                DB::raw('COALESCE(cli.nit, cli.numero_identificacion) as nit'),
                'cli.razon_social'
            )->orderby('usr_app_clientes_profesional.id', 'DESC')->paginate();
        return response()->json($result);
    }


    public function create(Request $request)
    {
        $asunto = 'Asignación empresa';
        $cuerpo_mensaje = 'Se le ha asignado una nueva empresa: ';
        $profesionales = $request->profesionales;
        $cliente = $request->cliente;
        foreach ($cliente as $cliente) {
            $profesional_cliente = new ClientesProfesionalModel;
            $profesional_cliente->cliente_id = $cliente['id'];
            $profesional_cliente->profesional_id = $profesionales['usuario_id'];
            $profesional_cliente->save();
            $cuerpo_mensaje .= ' Empresa: ' . $cliente['nombre'];
            $resultado =  $this->notificaProfesional($asunto, $cuerpo_mensaje, $profesionales['correo'], 62, $profesional_cliente->id);
        }
        // return response()->json(['status' => 'success', 'message' => 'Reguistro guardado de manera exitosa.']);
        return $resultado;
    }

    public function notificaProfesional($subject, $body, $destinatario, $modulo, $registro_id)
    {
        $correo = [
            'subject' => $subject,
            'body' => $body,
            'formulario_ingreso' => "",
            'to' => $destinatario,
            'cc' => '',
            'cco' => '',
            'modulo' => $modulo,
            'registro_id' => $registro_id
        ];

        // Instanciar el controlador de envío de correo
        $EnvioCorreoController = new EnvioCorreoController();
        $requestEmail = Request::createFromBase(new Request($correo));

        // Enviar el correo
        return $EnvioCorreoController->sendEmail($requestEmail);
    }

    public function borradomasivo(Request $request)
    {
        $result = $request->all();
        for ($i = 0; $i < count($request->id); $i++) {
            $result = ClientesProfesionalModel::find($request->id[$i]);
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
        $result = ClientesProfesionalModel::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar registro.']);
        }
    }

    public function filtro($cadena)
    {
        try {
            $cadenaJSON = base64_decode($cadena);
            $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
            $valores = explode("/", $cadenaUTF8);
            $campo = $valores[0];
            $operador = $valores[1];
            $valor = $valores[2];
            $valor2 = isset($valores[3]) ? $valores[3] : null;
            $query = ClientesProfesionalModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_clientes_profesional.profesional_id')
                ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
                ->join('usr_app_clientes as cli', 'cli.id', 'usr_app_clientes_profesional.cliente_id')
                ->select(
                    'usr_app_clientes_profesional.id',
                    DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
                    DB::raw('COALESCE(cli.nit, cli.numero_identificacion) as nit'),
                    'cli.razon_social'
                )->orderby('usr_app_clientes_profesional.id', 'DESC');
            switch ($operador) {
                case 'Contiene':
                    if ($campo == "nit") {
                        $query->where(function ($q) use ($valor) {
                            $q->where('cli.nit', 'like', '%' . $valor . '%')
                                ->orWhere('cli.numero_identificacion', 'like', '%' . $valor . '%');
                        });
                    } else if ($campo == "razon_social") {
                        $query->where('cli.razon_social', 'like', '%' . $valor . '%');
                    } else if ($campo == "nombres") {
                        $query->where('ui.nombres', 'like', '%' . $valor . '%');
                    } else {
                        $query->where($campo, 'like', '%' . $valor . '%');
                    }
                    break;
                case 'Igual a':
                    if ($campo == "nit") {
                        $query->where(function ($q) use ($valor) {
                            $q->where('cli.nit', '=', $valor)
                                ->orWhere('cli.numero_identificacion', '=', $valor);
                        });
                    } else if ($campo == "razon_social") {
                        $query->where('cli.razon_social', '=', $valor);
                    } else if ($campo == "nombres") {
                        $query->where('ui.nombres', '=', $valor);
                    } else {
                        $query->where($campo, '=', $valor);
                    }
                    break;
            }

            $result = $query->paginate();
            return response()->json($result);
        } catch (\Exception $e) {
            return $e;
        }
    }
}
