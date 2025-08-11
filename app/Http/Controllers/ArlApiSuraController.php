<?php

namespace App\Http\Controllers;

use App\Models\cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use App\Models\DashboardIngresosRetiros;
use App\Models\DashboardActivos;
use App\Models\EmpleadoConvenioNovasoftModel;
use App\Models\HistoricoFondosModel;
use App\Models\HistoricoIngresoArlModel;
use Illuminate\Support\Carbon;
use App\Traits\Permisos;
use App\Traits\AutenticacionGuard;

class ArlApiSuraController extends Controller
{
    use Permisos;
    use AutenticacionGuard;

    public function buscarEmpleadoContratado($num_doc)
    {
        try {
            $empleado = DashboardActivos::leftjoin('gen_tipide as tipo_identificacion', 'tipo_identificacion.cod_tip', '=', 'rhh_emplea.tip_ide')
                ->leftjoin('GTH_Genero as genero', 'genero.cod_gen', '=', 'rhh_emplea.sex_emp')
                ->where('cod_emp', $num_doc)
                ->select(
                    'tipo_identificacion.tip_tip as tipo_identificacion',
                    'rhh_emplea.cod_emp',
                    'rhh_emplea.nom1_emp',
                    'rhh_emplea.nom2_emp',
                    'rhh_emplea.ap1_emp',
                    'rhh_emplea.ap2_emp',
                    'rhh_emplea.sex_emp',
                    'rhh_emplea.fec_nac',
                    'rhh_emplea.dir_res',
                    'rhh_emplea.ciu_res',
                    'rhh_emplea.tel_cel',
                    'rhh_emplea.e_mail',
                )->first();


            if ($empleado) {
                $ultimoHistorial = DashboardIngresosRetiros::leftjoin('rhh_cargos as cargo', 'cargo.cod_car', '=', 'rhh_hislab.cod_car')
                    ->where('cod_emp', $empleado->cod_emp)
                    ->where('nue_con', 1)
                    ->select(
                        'rhh_hislab.sal_bas',
                        'rhh_hislab.cod_CT',
                        'cargo.nom_car as nombre_cargo'
                    )
                    ->orderByDesc('fec_ini')
                    ->first();

                $empleado->historial_laboral = $ultimoHistorial;

                $eps = HistoricoFondosModel::leftjoin('rhh_tbfondos as eps', 'eps.cod_fdo', '=', 'rhh_hisfon.cod_fdo')
                    ->where('cod_emp', $empleado->cod_emp)
                    ->where('tip_nov', 1)
                    ->where('rhh_hisfon.tip_fdo', 2)
                    ->select('eps.cod_sgs')
                    ->orderByDesc('fec_afi')
                    ->first();

                $empleado->eps = $eps;

                $afp = HistoricoFondosModel::leftjoin('rhh_tbfondos as afp', 'afp.cod_fdo', '=', 'rhh_hisfon.cod_fdo')
                    ->where('cod_emp', $empleado->cod_emp)
                    ->where('tip_nov', 1)
                    ->where('rhh_hisfon.tip_fdo', 1)
                    ->select('afp.cod_sgs')
                    ->orderByDesc('fec_afi')
                    ->first();

                $empleado->afp = $afp;

                $empresa = EmpleadoConvenioNovasoftModel::leftjoin('rhh_Convenio as convenio', 'convenio.cod_conv', '=', 'rhh_EmpConv.cod_conv')
                    ->leftjoin('cxc_cliente as cliente', 'cliente.cod_cli', '=', 'convenio.cod_cli')
                    ->where('cod_emp', $num_doc)
                    ->where('fec_fin', null)
                    ->select(
                        'convenio.dir_conv as direccion',
                        'convenio.cod_ciu as cod_ciu',
                        'cliente.te1_cli',
                        'cliente.e_mail'
                    )
                    ->first();
                $empleado->empresa = $empresa;
            }

            return $empleado;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function ingresarEmpleado(Request $request)
    {


        $fecha_inicio_cobertura = Carbon::now()->addDay()->format('Y-m-d');
        $url_arl = Config::get('app.ARL_API_URL');
        $num_doc = $request['num_doc'];
        $empleado = $this->buscarEmpleadoContratado($num_doc);
        $api_key = Config::get('app.ARL_API_KEY');

        if ($empleado) {
            $user = $this->getUserRelaciones();
            $user = $user->getdata(true);
            $datos = [
                "codTipoCotizante" => "01",
                "tipoDocumento" => $empleado['tipo_identificacion'],
                "numeroDocumento" => $num_doc,
                "apellido1" => $empleado['ap1_emp'],
                "apellido2" => $empleado['ap2_emp'] ? $empleado['ap2_emp'] : null,
                "nombre1" => $empleado['nom1_emp'],
                "nombre2" => $empleado['nom2_emp'] ? $empleado['nom2_emp'] : null,
                "sexo" => $empleado['sex_emp'] == 2 ? "M" : "F",
                "fechaNacimiento" => Carbon::parse($empleado['fec_nac'])->format('Y-m-d'),
                "eps" => $empleado['eps']['cod_sgs'],
                "afp" => $empleado['afp']['cod_sgs'],
                "ibc" => (int)  $empleado['historial_laboral']['sal_bas'],
                "fechaInicioCobertura" => $fecha_inicio_cobertura,
                "direccionResidencia" => $empleado['dir_res'],
                "municipioResidencia" => $empleado['ciu_res'],
                "zonaResidencia" => "URBANA",
                "telefonoResidencia1" => $empleado['tel_cel'],
                "correoElectronico" => $empleado['e_mail'],
                "modalidad" => "PRESENCIAL",
                "codigoCentroTrabajo" => str_pad($empleado['historial_laboral']['cod_CT'], 10, '0', STR_PAD_LEFT),
                "direccionTrabajo" => $empleado['empresa']['direccion'],
                "municipioTrabajo" => $empleado['empresa']['cod_ciu'],
                "zonaTrabajo" => "URBANA",
                "telefono1Trabajo" => (int)$empleado['empresa']['te1_cli'],
                "telefono2Trabajo" => null,
                "correoElectronicoTrabajo" => $empleado['empresa']['e_mail'],
                "cargoEmpleado" => $empleado['historial_laboral']['nombre_cargo'],
                "jornadaEstablecida" => "UNICA",
                "tipoContrato" => "01",
                "usuario" => [
                    "documento" => [
                        "tipo" => "NI",
                        "numero" => "811025401"
                    ],
                    'correo' => "programador2@saitempsa.com"
                ]
            ];
            try {

                $response = Http::withHeaders([
                    'x-apikey' => $api_key
                ])->post($url_arl . '/ingreso', $datos);
                if ($response['result'] == false) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $response["message"],
                    ], $response->status());
                } else {
                    $historicoArl = new HistoricoIngresoArlModel();
                    $historicoArl->numero_documento = $datos['numeroDocumento'];
                    $historicoArl->tipo_documento = $datos['tipoDocumento'];
                    $historicoArl->apellido1 = $datos['apellido1'];
                    $historicoArl->apellido2 = $datos['apellido2'];
                    $historicoArl->nombre1 = $datos['nombre1'];
                    $historicoArl->nombre2 = $datos['nombre2'];
                    $historicoArl->sexo = $datos['sexo'];
                    $historicoArl->salario = $datos['ibc'];
                    $historicoArl->fecha_nacimiento = Carbon::parse($datos['fechaNacimiento'])->format('d-m-Y');
                    $historicoArl->eps = $datos['eps'];
                    $historicoArl->afp = $datos['afp'];
                    $historicoArl->ini_cobertura = Carbon::parse($datos['fechaInicioCobertura'])->format('d-m-Y');
                    $historicoArl->dir_res = $datos['direccionResidencia'];
                    $historicoArl->ciu_res = $datos['municipioResidencia'];
                    $historicoArl->zona_res = $datos['zonaResidencia'];
                    $historicoArl->tel_res = $datos['telefonoResidencia1'];
                    $historicoArl->email_candidato = $datos['correoElectronico'];
                    $historicoArl->modalidad = $datos['modalidad'];
                    $historicoArl->cod_CT = $datos['codigoCentroTrabajo'];
                    $historicoArl->trabajo_dir = $datos['direccionTrabajo'];
                    $historicoArl->ciu_trabajo = $datos['municipioTrabajo'];
                    $historicoArl->zona_trabajo = $datos['zonaTrabajo'];
                    $historicoArl->tel_trabajo = $datos['telefono1Trabajo'];
                    $historicoArl->email_trabajo = $datos['correoElectronicoTrabajo'];
                    $historicoArl->cargo = $datos['cargoEmpleado'];
                    $historicoArl->jornada = $datos['jornadaEstablecida'];
                    $historicoArl->tipo_contrato = $datos['tipoContrato'];
                    $historicoArl->usuario_id = $user['id'];
                    $historicoArl->nombre_usuario = $user['nombres'] . ' ' .  $user['apellidos'];
                    $historicoArl->save();
                    return response()->json([
                        'status' => 'success',
                        'message' => $response["message"],
                        'body' => $response->body()
                    ], $response->status());
                }
            } catch (\Exception $e) {
                return $e;
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Empleado no encontrado en novasoft.'
            ], 404);
        }
    }
}
