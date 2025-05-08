<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SectorEconomicoProfesionalModel;
use App\Traits\AutenticacionGuard;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\returnSelf;

class SectorEconomicoProfesionalController extends Controller
{
    use AutenticacionGuard;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        $result = SectorEconomicoProfesionalModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_sector_economico_profesional.profesional_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->join('usr_app_sector_economico as se', 'se.id', 'usr_app_sector_economico_profesional.sector_economico_id')
            ->select(
                'usr_app_sector_economico_profesional.id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
                'se.nombre as sector economico'
            )->orderby('usr_app_sector_economico_profesional.id', 'DESC')->paginate($cantidad);
        return response()->json($result);
    }

    public function usuariointernorol($id)
    {
        $users =  $this->usuariosInternosRol($id);
        return $users;
    }

    public function byid($id)
    {
        $result = SectorEconomicoProfesionalModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_sector_economico_profesional.profesional_id')
            ->join('usr_app_usuarios_internos as ui', 'ui.usuario_id', 'us.id')
            ->join('usr_app_sector_economico as se', 'se.id', 'usr_app_sector_economico_profesional.sector_economico_id')
            ->where('usr_app_sector_economico_profesional.profesional_id', '=', $id)
            ->select(
                'usr_app_sector_economico_profesional.id',
                DB::raw("CONCAT(ui.nombres,' ',ui.apellidos)  AS nombres"),
                'se.nombre as sector economico'
            )->orderby('usr_app_sector_economico_profesional.id', 'DESC')->paginate();
        return response()->json($result);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $asunto = 'Asignación sector económico';
        $cuerpo_mensaje = 'Se le ha asignado un nuevo sector económico';
        $profesionales = $request->profesionales;
        $sector = $request->sector;
        foreach ($profesionales as $profesional) {
            $profesional_sector = new SectorEconomicoProfesionalModel;
            $profesional_sector->sector_economico_id = $sector['id'];
            $profesional_sector->profesional_id = $profesional['usuario_id'];
            $profesional_sector->save();
            $cuerpo_mensaje .= ' Sector: '.$sector['nombre'];
           $resultado =  $this->notificaProfesional($asunto, $cuerpo_mensaje, $profesional['correo'],62,$profesional_sector->id);
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

    public function borradomasivo(Request $request){
        $result = $request->all();
        for ($i = 0; $i < count($request->id); $i++) {
            $result = SectorEconomicoProfesionalModel::find($request->id[$i]);
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
        $result = SectorEconomicoProfesionalModel::find($id);
        if ($result->delete()) {
            return response()->json(['status' => 'success', 'message' => 'Registro eliminado de manera exitosa.']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar registro.']);
        }
    }
}
