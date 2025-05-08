<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecepcionEmpleado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\ReferenciasModel;
use App\Models\ReferenciasFormularioEmpleado;
use App\Models\UsuariosCandidatosModel;
use App\Models\ReferenciasPersonalesCandidatosModel;
use App\Models\ExperienciasLaboralesCandidatosModel;
use App\Models\DashboardActivos;
use App\Models\IdiomasCandidatosModel;
use App\Models\Municipios;
use App\Models\ListaTrump;
use App\Models\formularioGestionIngreso;
use App\Models\User;
use Illuminate\Support\Str;

class RecepcionEmpleadoController extends Controller
{

    public function index()
    {
        $novasoft = RecepcionEmpleado::where('cod_emp', "11")->first();
        if ($novasoft) {
            return response()->json($novasoft);
        } else {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
    }


    public function createNovasoft(Request $request)
    {
        try {
            $novasoft = new RecepcionEmpleado;
            $fechaNacimientoFormated = Carbon::parse($request->fec_nac)->format('d-m-Y H:i:s');
            $fechaExpedicionFormated = Carbon::parse($request->fec_expdoc)->format('d-m-Y H:i:s');
            $novasoft->cod_emp = $request->cod_emp;
            $novasoft->ap1_emp = $request->ap1_emp;
            $novasoft->ap2_emp = $request->ap2_emp;
            $novasoft->nom_emp = $request->nom1_emp;
            $novasoft->nom1_emp = $request->nom1_emp;
            $novasoft->nom2_emp = $request->nom2_emp ?? '';
            $novasoft->tip_ide = $request->tip_ide;
            $novasoft->pai_exp = $request->pai_exp;
            $novasoft->ciu_exp = $request->ciu_exp;
            $novasoft->fec_nac = $fechaNacimientoFormated;
            $novasoft->cod_pai = $request->cod_pai;
            $novasoft->cod_dep = $request->cod_dep;
            $novasoft->cod_ciu = $request->cod_ciu;
            $novasoft->sex_emp = $request->sex_emp;
            $novasoft->gru_san = $request->gru_san;
            $novasoft->fac_rhh = $request->fac_rhh;
            $novasoft->est_civ = $request->est_civ;
            $novasoft->dir_res = $request->dir_res;
            $novasoft->tel_res = $request->tel_res;
            $novasoft->nac_emp = $request->nac_emp;
            $novasoft->pai_res = $request->pai_res;
            $novasoft->dpt_res = $request->dpt_res;
            $novasoft->per_car = $request->per_car;
            $novasoft->e_mail = $request->e_mail;
            $novasoft->tel_cel = $request->tel_cel;
            $novasoft->dpt_exp = $request->dpt_exp;
            $novasoft->Niv_aca = $request->Niv_aca;
            $novasoft->barrio = $request->barrio;
            $novasoft->cta_ban = $request->cta_ban;
            $novasoft->raza = $request->raza;
            $novasoft->cod_grupo = $request->cod_grupo;
            $novasoft->cod_ban = $request->cod_ban;
            $novasoft->fec_expdoc = $fechaExpedicionFormated;
            $novasoft->ciu_res = $request->ciu_res;
            $novasoft->est_soc = $request->est_soc;
            $novasoft->num_ide = $request->cod_emp;
            $novasoft->save();



            foreach ($request->referencias as $referencia) {

                if ($referencia['nom_ref'] == '' || $referencia['nom_ref'] == null) {
                } else {
                    $novasoftReferencia = new ReferenciasFormularioEmpleado;
                    $novasoftReferencia->cod_emp = $novasoft->cod_emp;
                    $novasoftReferencia->num_ref = $referencia['num_ref'];
                    $novasoftReferencia->parent = $referencia['parent'];
                    $novasoftReferencia->cel_ref = $referencia['cel_ref'];
                    $novasoftReferencia->nom_ref = $referencia['nom_ref'];
                    $novasoftReferencia->tip_ref = $referencia['tip_ref'];
                    $novasoftReferencia->ocu_ref = 0;
                    $novasoftReferencia->save();
                }
            }
            foreach ($request->familiares as $referencia) {
                $novasoftReferencia = new ReferenciasModel;
                if ($referencia['ap1_fam'] != "") {
                    $fechaNacimientoFormated = Carbon::parse($referencia['fec_nac'])->format('d-m-Y H:i:s');
                    $novasoftReferencia->cod_emp = $novasoft->cod_emp;
                    $novasoftReferencia->ap1_fam = $referencia['ap1_fam'];
                    $novasoftReferencia->ap2_fam = $referencia['ap2_fam'];
                    $novasoftReferencia->nom_fam = $referencia['nom_fam'];
                    $novasoftReferencia->tip_fam = $referencia['tip_fam'];
                    $novasoftReferencia->fec_nac = $fechaNacimientoFormated;
                    $novasoftReferencia->ocu_fam = $referencia['ocu_fam'];
                    $novasoftReferencia->save();
                }
            }





            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa', 'id' => $novasoft]);
        } catch (\Exception $e) {
            throw $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente nova']);
        }
    }

    public function searchByCodEmp($cod_emp, $noJsonresponse = false)
    {
        try {
            $novasoft = RecepcionEmpleado::leftJoin('gen_tipide as tipoId', 'tipoId.cod_tip', '=', 'GTH_RptEmplea.tip_ide')
                ->leftJoin('gen_paises as pais_exp_name', 'pais_exp_name.cod_pai', '=', 'GTH_RptEmplea.pai_exp')
                ->leftJoin('gen_paises as pais_res_name', 'pais_res_name.cod_pai', '=', 'GTH_RptEmplea.pai_res')
                ->leftJoin('gen_paises as pais_nac_name', 'pais_nac_name.cod_pai', '=', 'GTH_RptEmplea.cod_pai')
                ->leftJoin('gen_deptos as depto_exp_name', function ($join) {
                    $join->on('depto_exp_name.cod_dep', '=', 'GTH_RptEmplea.dpt_exp')
                        ->whereColumn('depto_exp_name.cod_pai', 'GTH_RptEmplea.pai_exp'); // Corregida relación con país
                })
                ->leftJoin('gen_deptos as depto_res_name', function ($join) {
                    $join->on('depto_res_name.cod_dep', '=', 'GTH_RptEmplea.dpt_res')
                        ->whereColumn('depto_res_name.cod_pai', 'GTH_RptEmplea.pai_res'); // Corregida relación con país
                })
                ->leftJoin('gen_deptos as depto_nac_name', function ($join) {
                    $join->on('depto_nac_name.cod_dep', '=', 'GTH_RptEmplea.cod_dep')
                        ->whereColumn('depto_nac_name.cod_pai', 'GTH_RptEmplea.cod_pai'); // Corregida relación con país
                })
                ->leftJoin('gen_ciudad as ciudad_exp_name', function ($join) {
                    $join->on('ciudad_exp_name.cod_ciu', '=', 'GTH_RptEmplea.ciu_exp')
                        ->whereColumn('ciudad_exp_name.cod_dep', 'GTH_RptEmplea.dpt_exp');
                })
                ->leftJoin('gen_ciudad as ciudad_res_name', function ($join) {
                    $join->on('ciudad_res_name.cod_ciu', '=', 'GTH_RptEmplea.ciu_res')
                        ->whereColumn('ciudad_res_name.cod_dep', 'GTH_RptEmplea.dpt_res');
                })
                ->leftJoin('gen_ciudad as ciudad_nac_name', function ($join) {
                    $join->on('ciudad_nac_name.cod_ciu', '=', 'GTH_RptEmplea.cod_ciu')
                        ->whereColumn('ciudad_nac_name.cod_dep', 'GTH_RptEmplea.cod_dep');
                })
                ->leftJoin('gen_bancos as banco_name', 'banco_name.cod_ban', '=', 'GTH_RptEmplea.cod_ban')
                ->leftJoin('rhh_tbclaest as nivelAcademico_name', 'nivelAcademico_name.tip_est', '=', 'GTH_RptEmplea.Niv_aca')
                ->leftJoin('GTH_EstCivil as estadoCivil_name', 'estadoCivil_name.cod_est', '=', 'GTH_RptEmplea.cod_est')
                ->leftJoin('gen_grupoetnico as etnia_name', 'etnia_name.cod_grupo', '=', 'GTH_RptEmplea.cod_grupo')
                ->where('cod_emp', $cod_emp)
                ->select(
                    'tipoId.des_tip as tipIde_nombre',
                    'pais_exp_name.nom_pai as pais_exp_nombre',
                    'pais_res_name.nom_pai as pais_res_nombre',
                    'pais_nac_name.nom_pai as pais_nac_nombre',
                    'depto_exp_name.nom_dep as dep_exp_nombre',
                    'depto_res_name.nom_dep as dep_res_nombre',
                    'depto_nac_name.nom_dep as dep_nac_nombre',
                    'ciudad_exp_name.nom_ciu as ciudad_exp_nombre',
                    'ciudad_res_name.nom_ciu as ciudad_res_nombre',
                    'ciudad_nac_name.nom_ciu as ciudad_nac_nombre',
                    'banco_name.nom_ban as banco_nombre',
                    'nivelAcademico_name.des_est as nivelAcademico_nombre',
                    'estadoCivil_name.des_est as estadoCivil_nombre',
                    'etnia_name.descripcion as etnia_nombre',
                    '*'
                )->first();

            if ($novasoft) {
                // Formatear los nombres
                foreach (
                    [
                        'pais_exp_nombre',
                        'pais_res_nombre',
                        'pais_nac_nombre',
                        'dep_exp_nombre',
                        'dep_res_nombre',
                        'dep_nac_nombre',
                        'ciudad_exp_nombre',
                        'ciudad_res_nombre',
                        'ciudad_nac_nombre',
                        'banco_nombre',
                        'nivelAcademico_nombre'
                    ] as $campo
                ) {
                    $novasoft->$campo = $novasoft->$campo ? Str::ucfirst(Str::lower($novasoft->$campo)) : null;
                }
            }

            $referencias = ReferenciasFormularioEmpleado::where('cod_emp', $cod_emp)->get();
            $novasoft["referencias"] = $referencias;
            $familiares = ReferenciasModel::where('cod_emp', $cod_emp)->get();
            $novasoft["familiares"] = $familiares;

            if ($novasoft && $novasoft->cod_emp) {
                return $noJsonresponse ? $novasoft : response()->json(['status' => 'success', 'data' => $novasoft]);
            } else {
                return $noJsonresponse ? $novasoft : response()->json(['status' => 'error', 'message' => 'Empleado no encontrado'], 404);
            }
        } catch (\Exception $e) {
            return $noJsonresponse
                ? null
                : response()->json(['status' => 'error', 'message' => 'Error al buscar el empleado, por favor intenta nuevamente']);
        }
    }
    public function updateByCodEmpNovasoft(Request $request, $cod_emp)
    {
        try {

            $novasoft = RecepcionEmpleado::find($cod_emp);
            $fechaNacimientoFormated = Carbon::parse($request->fec_nac)->format('d-m-Y H:i:s');
            $fechaExpedicionFormated = Carbon::parse($request->fec_expdoc)->format('d-m-Y H:i:s');
            $novasoft->ap1_emp = $request->ap1_emp;
            $novasoft->ap2_emp = $request->ap2_emp;
            $novasoft->nom_emp = $request->nom1_emp;
            $novasoft->nom1_emp = $request->nom1_emp;
            $novasoft->nom2_emp = $request->nom2_emp ?? '';
            $novasoft->tip_ide = $request->tip_ide;
            $novasoft->pai_exp = $request->pai_exp;
            $novasoft->ciu_exp = $request->ciu_exp;
            $novasoft->fec_nac = $fechaNacimientoFormated;
            $novasoft->cod_pai = $request->cod_pai;
            $novasoft->cod_dep = $request->cod_dep;
            $novasoft->cod_ciu = $request->cod_ciu;
            $novasoft->sex_emp = $request->sex_emp;
            $novasoft->gru_san = $request->gru_san;
            $novasoft->fac_rhh = $request->fac_rhh;
            $novasoft->est_civ = $request->est_civ;
            $novasoft->dir_res = $request->dir_res;
            $novasoft->tel_res = $request->tel_res;
            $novasoft->nac_emp = $request->nac_emp;
            $novasoft->pai_res = $request->pai_res;
            $novasoft->dpt_res = $request->dpt_res;
            $novasoft->ciu_res = $request->ciu_res;
            $novasoft->per_car = $request->per_car;
            $novasoft->e_mail = $request->e_mail;
            $novasoft->tel_cel = $request->tel_cel;
            $novasoft->dpt_exp = $request->dpt_exp;
            $novasoft->Niv_aca = $request->Niv_aca;
            $novasoft->barrio = $request->barrio;
            $novasoft->cta_ban = $request->cta_ban;
            $novasoft->raza = $request->raza;
            $novasoft->cod_grupo = $request->cod_grupo;
            $novasoft->cod_ban = $request->cod_ban;
            $novasoft->fec_expdoc = $fechaExpedicionFormated;
            $novasoft->est_soc = $request->est_soc;
            $novasoft->num_ide = $request->cod_emp;
            $novasoft->save();
            foreach ($request->referencias as $referencia) {
                if (isset($referencia['cod_emp']) && isset($referencia['num_ref'])) {
                    $novasoftReferencia = ReferenciasFormularioEmpleado::where('cod_emp', $referencia['cod_emp'])
                        ->where('num_ref', $referencia['num_ref'])
                        ->first();

                    if ($novasoftReferencia) {
                        $novasoftReferencia->parent = $referencia['parent'];
                        $novasoftReferencia->cel_ref = $referencia['cel_ref'];
                        $novasoftReferencia->nom_ref = $referencia['nom_ref'];
                        $novasoftReferencia->ocu_ref = 0; // Si necesitas cambiar este valor, asegúrate de que sea correcto
                        $novasoftReferencia->save();
                    } else {
                        $novasoftReferencia = new ReferenciasFormularioEmpleado;
                        $novasoftReferencia->cod_emp = $novasoft->cod_emp;
                        $novasoftReferencia->num_ref = $referencia['num_ref'];
                        $novasoftReferencia->parent = $referencia['parent'];
                        $novasoftReferencia->cel_ref = $referencia['cel_ref'];
                        $novasoftReferencia->nom_ref = $referencia['nom_ref'];
                        $novasoftReferencia->tip_ref = $referencia['tip_ref'];
                        $novasoftReferencia->ocu_ref = 0;
                        $novasoftReferencia->save();
                    }
                } else {
                    $novasoftReferencia = new ReferenciasFormularioEmpleado;
                    $novasoftReferencia->cod_emp = $novasoft->cod_emp;
                    $novasoftReferencia->num_ref = $referencia['num_ref'];
                    $novasoftReferencia->parent = $referencia['parent'];
                    $novasoftReferencia->cel_ref = $referencia['cel_ref'];
                    $novasoftReferencia->nom_ref = $referencia['nom_ref'];
                    $novasoftReferencia->tip_ref = $referencia['tip_ref'];
                    $novasoftReferencia->ocu_ref = 0;
                    $novasoftReferencia->save();
                }
            }
            foreach ($request->familiaresConsulta as $index => $referencia) {
                $requestFamiliares = $request->familiares;
                if (isset($referencia['cod_emp']) && isset($referencia['ap1_fam'])) {
                    $novasoftReferencia = ReferenciasModel::where('cod_emp', $referencia['cod_emp'])
                        ->where('ap1_fam', $referencia['ap1_fam'])
                        ->where('nom_fam', $referencia['nom_fam'])
                        ->first();
                    $fechaNacimientoFormated = Carbon::parse($requestFamiliares[$index]['fec_nac'])->format('d-m-Y H:i:s');
                    $novasoftReferencia->ap1_fam = $requestFamiliares[$index]['ap1_fam'];
                    $novasoftReferencia->ap2_fam = $requestFamiliares[$index]['ap2_fam'];
                    $novasoftReferencia->nom_fam = $requestFamiliares[$index]['nom_fam'];
                    $novasoftReferencia->tip_fam = $requestFamiliares[$index]['tip_fam'];
                    $novasoftReferencia->fec_nac = $fechaNacimientoFormated;
                    $novasoftReferencia->ocu_fam = $requestFamiliares[$index]['ocu_fam'];
                    $novasoftReferencia->save();
                }
            }
            if (count($request->familiaresConsulta) < count($request->familiares)) {
                $tamanoInicial = count($request->familiaresConsulta);
                for ($i = $tamanoInicial; $i < count($request->familiares); $i++) {
                    $referencia = $request->familiares[$i];
                    $novasoftReferencia = new ReferenciasModel;
                    if ($referencia['ap1_fam'] != "") {
                        $fechaNacimientoFormated = Carbon::parse($referencia['fec_nac'])->format('d-m-Y H:i:s');
                        $novasoftReferencia->cod_emp = $novasoft->cod_emp;
                        $novasoftReferencia->ap1_fam = $referencia['ap1_fam'];
                        $novasoftReferencia->ap2_fam = $referencia['ap2_fam'];
                        $novasoftReferencia->nom_fam = $referencia['nom_fam'];
                        $novasoftReferencia->tip_fam = $referencia['tip_fam'];
                        $novasoftReferencia->fec_nac = $fechaNacimientoFormated;
                        $novasoftReferencia->ocu_fam = $referencia['ocu_fam'];
                        $novasoftReferencia->save();
                    }
                }
            }

            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa', 'id' => $novasoft->cod_emp]);
        } catch (\Exception $e) {

            throw $e;

            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }

    public function createSeiya(Request $request, $usuario_id)
    {
        try {
            DB::beginTransaction();


            /*  $novasoft->est_civ = $request->est_civ; */

            $ciu_exp_formated = trim($request->ciu_exp, '0');
            $ciu_nac_formated = trim($request->cod_ciu, '0');
            $ciu_res_formated = trim($request->ciu_res, '0');

            $ciu_exp_seiya =  Municipios::where('codigo_dane', $ciu_exp_formated)->first();
            $ciu_nac_seiya =  Municipios::where('codigo_dane', $ciu_nac_formated)->first();
            $ciu_res_seiya =  Municipios::where('codigo_dane', $ciu_res_formated)->first();


            $fechaNacimientoFormated = Carbon::parse($request->fec_nac)->format('d-m-Y H:i:s');
            $fechaExpedicionFormated = Carbon::parse($request->fec_expdoc)->format('d-m-Y H:i:s');
            $user = UsuariosCandidatosModel::where('usuario_id', $usuario_id)->first();
            $user->primer_nombre = $request->nom1_emp;
            $user->primer_apellido = $request->ap1_emp;
            $user->segundo_nombre = $request->nom2_emp ?? '';
            $user->segundo_apellido = $request->ap2_emp;
            $user->fecha_nacimiento = $fechaNacimientoFormated;
            $user->direccion_residencia = $request->dir_res;
            $user->fecha_expedicion = $fechaExpedicionFormated;
            $user->telefono = $request->tel_res;
            $user->celular = $request->tel_cel;
            $user->cuenta_bancaria = $request->cta_ban;
            $user->personas_cargo = $request->per_car;
            $user->grupo_sanguineo = $request->gru_san;
            $user->factor_rh = $request->fac_rhh;
            $user->estrato = $request->est_soc;
            $user->sector_academico_id = $request->sector_academico_id;
            $user->acidente_laboral = $request->acidente_laboral;
            $user->enfermedad = $request->enfermedad;
            $user->tratamiento_medico = $request->tratamiento_medico;
            $user->tratamiento_psiquiatrico = $request->tratamiento_psiquiatrico;
            $user->tratamiento_psicologico = $request->tratamiento_psicologico;
            $user->tratamiento_odontologico = $request->tratamiento_odontologico;
            $user->cirugia = $request->cirugia;
            $user->alerigia = $request->alerigia;
            $user->fractura = $request->fractura;
            $user->sustencia_psicoactiva = $request->sustencia_psicoactiva;
            $user->estatura = $request->estatura;
            $user->peso = $request->peso;
            $user->lentes = $request->lentes;
            $user->eps_id = $request->eps_id;
            $user->afp_id = $request->afp_id;
            $user->descripcion_salud = $request->descripcion_salud;
            $user->vehiculo_propio = $request->vehiculo_propio;
            $user->transporte_publico = $request->transporte_publico;
            $user->licencia_conduccion = $request->licencia_conduccion;
            $user->categoria_licencia = $request->categoria_licencia;
            $user->tip_doc_id = $request->tip_ide;
            $user->ciudad_expedicion_id = $ciu_exp_seiya ? $ciu_exp_seiya['id'] : null;
            $user->ciudad_nacimiento_id = $ciu_nac_seiya ? $ciu_nac_seiya['id'] : null;
            $user->ciudad_residencia_id = $ciu_res_seiya ? $ciu_res_seiya['id'] : null;
            $user->gen_banco_id = $request->cod_ban;
            $user->nivel_academico_id = $request->Niv_aca;
            $user->genero_id = $request->sex_emp;
            $user->grupo_etnico_id = $request->cod_grupo;
            $user->concepto = $request->concepto;
            $user->save();

            /*      foreach ($request->referencias as $item) {
                if ($item['nom_ref'] != "") {
                    $referencia = new ReferenciasPersonalesCandidatosModel;
                    $referencia->usuario_id =  $usuario_id;
                    $referencia->nombre = $item['nom_ref'];
                    $referencia->telefono = $item['cel_ref'];
                    $referencia->relacion = $item['parent'];
                    $referencia->fecha_nacimiento = $item['fecha_nacimiento']; 
                    $referencia->save();
                }
            } */

            if (count($request->experiencias_laborales) > 0) {
                foreach ($request->experiencias_laborales as $item) {
                    if (isset($item['id'])) {
                        $fecha_inicio = Carbon::parse($item['fecha_inicio'])->format('27-m-Y H:i:s');
                        $fecha_fin = Carbon::parse($item['fecha_fin'])->format('28-m-Y H:i:s');
                        $experiencia = ExperienciasLaboralesCandidatosModel::find($item['id']);
                        $experiencia->usuario_id = $usuario_id;
                        $experiencia->empresa = $item['empresa'];
                        $experiencia->funciones = $item['funciones'];
                        $experiencia->cargo = $item['cargo'];
                        $experiencia->sector_econimico_id = $item['sector_econimico_id'];
                        $experiencia->motivo_retiro = $item['motivo_retiro'];
                        $experiencia->fecha_inicio = $fecha_inicio;
                        $experiencia->fecha_fin = $fecha_fin;
                        $experiencia->tiempo = Carbon::parse($fecha_inicio)
                            ->diffInMonths(Carbon::parse($fecha_fin));
                        $experiencia->save();
                    } else {
                        $fecha_inicio = Carbon::parse($item['fecha_inicio'])->format('27-m-Y H:i:s');
                        $fecha_fin = Carbon::parse($item['fecha_fin'])->format('28-m-Y H:i:s');
                        $experiencia = new ExperienciasLaboralesCandidatosModel;
                        $experiencia->usuario_id = $usuario_id;
                        $experiencia->empresa = $item['empresa'];
                        $experiencia->funciones = $item['funciones'];
                        $experiencia->cargo = $item['cargo'];
                        $experiencia->sector_econimico_id = $item['sector_econimico_id'];
                        $experiencia->motivo_retiro = $item['motivo_retiro'];
                        $experiencia->fecha_inicio = $fecha_inicio;
                        $experiencia->fecha_fin = $fecha_fin;
                        $experiencia->tiempo = Carbon::parse($fecha_inicio)
                            ->diffInMonths(Carbon::parse($fecha_fin));
                        $experiencia->save();
                    }
                }
            }

            if (count($request->idiomas) > 0) {

                foreach ($request->idiomas as $item) {
                    if (isset($item['id'])) {
                        $idioma = IdiomasCandidatosModel::find($item['id']);
                        $idioma->usuario_id = $usuario_id;
                        $idioma->idioma_id = $item['idioma_id'];
                        $idioma->nivel = $item['nivel'];
                        $idioma->save();
                    } else {

                        $idioma = new IdiomasCandidatosModel;
                        $idioma->usuario_id = $usuario_id;
                        $idioma->idioma_id = $item['idioma_id'];
                        $idioma->nivel = $item['nivel'];
                        $idioma->save();
                    }
                }
            }

            $novasoft = RecepcionEmpleado::where('cod_emp', $user->num_doc)
                ->where('tip_ide', $request->tip_ide)
                ->first();

            $response = "";
            if ($novasoft) {
                $response = $this->updateByCodEmpNovasoft($request, $user->num_doc);
            } else {
                $response = $this->createNovasoft($request);
            }
            $responseData = $response->getData(true);
            DB::commit();

            if ($responseData['status'] == "success") {
                return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa', 'id' => $user->id]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return $e;
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }

    public function searchByIdOnUsuariosCandidato($usuario_id)
    {
        $user = auth()->user();
        $user_candidato = UsuariosCandidatosModel::leftjoin('usr_app_eps_c as eps', 'eps.id', 'usr_app_candidatos_c.eps_id')
            ->leftjoin('usr_app_afp as afp', 'afp.id', 'usr_app_candidatos_c.afp_id')
            ->leftjoin('usr_app_sector_academico_c as sector_academico', 'sector_academico.id', 'usr_app_candidatos_c.sector_academico_id')
            ->leftjoin('usr_app_municipios as ciudad_expedicion', 'ciudad_expedicion.id', 'usr_app_candidatos_c.ciudad_expedicion_id')
            ->leftjoin('usr_app_municipios as ciudad_nacimiento', 'ciudad_nacimiento.id', 'usr_app_candidatos_c.ciudad_nacimiento_id')
            ->leftjoin('usr_app_municipios as ciudad_residencia', 'ciudad_residencia.id', 'usr_app_candidatos_c.ciudad_residencia_id')
            ->leftjoin('gen_tipide as tipo_documento', 'tipo_documento.cod_tip', 'usr_app_candidatos_c.tip_doc_id')
            ->leftjoin('gen_bancos as banco', 'banco.cod_ban', 'usr_app_candidatos_c.gen_banco_id')
            ->leftjoin('rhh_tbclaest as nivel_academico', 'nivel_academico.tip_est', 'usr_app_candidatos_c.nivel_academico_id')
            ->leftjoin('usr_app_genero as genero', 'genero.id', 'usr_app_candidatos_c.genero_id')
            ->leftjoin('gen_grupoetnico as grupo_etnico', 'grupo_etnico.cod_grupo', 'usr_app_candidatos_c.grupo_etnico_id')
            ->select(
                'usr_app_candidatos_c.*',
                'eps.nombre as eps_nombre',
                'afp.nombre as afp_nombre',
                'sector_academico.nombre as sector_academico_nombre',
                'tipo_documento.des_tip as des_tip',
                'banco.nom_ban as nom_ban',
                'nivel_academico.des_est as des_est',
                'genero.nombre as genero_nombre',
                'grupo_etnico.descripcion as descripcion'
            )
            ->where('usuario_id', $usuario_id)->first();

        if ($user_candidato) {
            // Formatear el campo eps_nombre
            $user_candidato->eps_nombre = Str::ucfirst(Str::lower($user_candidato->eps_nombre));
            $user_candidato->nom_ban = Str::ucfirst(Str::lower($user_candidato->nom_ban));
        }

        $experiencias_laborales = ExperienciasLaboralesCandidatosModel::join('usr_app_sector_econimico_c as sector_economico', 'sector_economico.id', 'usr_app_experiencias_laborales_c.sector_econimico_id')
            ->select(
                'usr_app_experiencias_laborales_c.*',
                'sector_economico.nombre as nombre',
            )
            ->where('usuario_id', $usuario_id)->get();

        $idiomas = IdiomasCandidatosModel::join('usr_app_idiomas_c as idioma', 'idioma.id', 'usr_app_candidatos_idiomas_c.idioma_id')
            ->select(
                'usr_app_candidatos_idiomas_c.*',
                'idioma.nombre as nombre',
            )
            ->where('usuario_id', $usuario_id)->get();

        $user_candidato['experiencias_laborales'] = $experiencias_laborales;
        $user_candidato['idiomas'] = $idiomas;
        $novasoft = $this->searchByCodEmp($user_candidato->num_doc, true);
        $user_candidato["novasoft"] = $novasoft;
        return response()->json($user_candidato);
    }
    /*  public function searchByIdOnUsuariosCandidato(Request $request, usuario_id){

        $user = UsuariosCandidatosModel::where('usuario_id', $usuario_id)->first();

    } */

    public function deleteExperienciaLaboral($id)
    {
        try {
            $result = ExperienciasLaboralesCandidatosModel::find($id);
            $result->delete();
            return response()->json(['status' => 'success', 'message' => 'Experiencia laboral eliminada de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar experiencia, por favor intenta nuevamente']);
        }
    }
    public function deleteIdiomaCandidato($id)
    {
        try {
            $result = IdiomasCandidatosModel::find($id);
            $result->delete();
            return response()->json(['status' => 'success', 'message' => 'Idioma eliminado de manera exitosa']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al eliminar exeriencia, por favor intenta nuevamente']);
        }
    }
    public function indexFormularioCandidatos($cantidad)
    {
        $result = UsuariosCandidatosModel::leftjoin('usr_app_municipios as ciudad_residencia', 'ciudad_residencia.id', 'usr_app_candidatos_c.ciudad_residencia_id')
            ->leftjoin('usr_app_genero as genero', 'genero.id', 'usr_app_candidatos_c.genero_id')
            ->leftjoin('gen_tipide as tipo_documento', 'tipo_documento.cod_tip', 'usr_app_candidatos_c.tip_doc_id')
            ->select(

                'usr_app_candidatos_c.id',
                'usr_app_candidatos_c.num_doc',
                'tipo_documento.des_tip as tipo_documento',
                'usr_app_candidatos_c.primer_nombre',
                'usr_app_candidatos_c.primer_apellido',
                'usr_app_candidatos_c.fecha_nacimiento',
                'ciudad_residencia.nombre as ciudad_residencia',
                'usr_app_candidatos_c.celular',
                'genero.nombre as genero_nombre',
                'usr_app_candidatos_c.created_at',
                'usr_app_candidatos_c.usuario_id',
            )->paginate($cantidad);

        return response()->json($result);
    }
    public function candidatosFiltro($cadena)
    {

        try {
            $cadenaJSON = base64_decode($cadena);
            $cadenaUTF8 = mb_convert_encoding($cadenaJSON, 'UTF-8', 'ISO-8859-1');
            $valores = explode("/", $cadenaUTF8);
            $campo = $valores[0];
            $operador = $valores[1];
            $valor = $valores[2];
            $valor2 = isset($valores[3]) ? $valores[3] : null;
            $query = UsuariosCandidatosModel::leftjoin('usr_app_municipios as ciudad_residencia', 'ciudad_residencia.id', 'usr_app_candidatos_c.ciudad_residencia_id')
                ->leftjoin('usr_app_genero as genero', 'genero.id', 'usr_app_candidatos_c.genero_id')
                ->leftjoin('gen_tipide as tipo_documento', 'tipo_documento.cod_tip', 'usr_app_candidatos_c.tip_doc_id')
                ->select(
                    'usr_app_candidatos_c.id',
                    'usr_app_candidatos_c.num_doc',
                    'tipo_documento.des_tip as tipo_documento',
                    'usr_app_candidatos_c.primer_nombre',
                    'usr_app_candidatos_c.primer_apellido',
                    'usr_app_candidatos_c.fecha_nacimiento',
                    'ciudad_residencia.nombre as ciudad_residencia',
                    'usr_app_candidatos_c.celular',
                    'genero.nombre as genero_nombre',
                    'usr_app_candidatos_c.created_at',
                    'usr_app_candidatos_c.usuario_id',
                )->orderby('usr_app_candidatos_c.id', 'DESC');

            switch ($operador) {
                case 'Contiene':
                    if ($campo == "num_doc") {
                        $query->where('usr_app_candidatos_c.num_doc', 'like', '%' . $valor . '%');
                    } else if ($campo == "tipo_documento") {
                        $query->where('tipo_documento.des_tip', 'like', '%' . $valor . '%');
                    } else if ($campo == "primer_nombre") {
                        $query->where('usr_app_candidatos_c.primer_nombre', 'like', '%' . $valor . '%');
                    } else if ($campo == "primer_apellido") {
                        $query->where('usr_app_candidatos_c.primer_apellido', 'like', '%' . $valor . '%');
                    } else if ($campo == "ciudad_residencia") {
                        $query->where('ciudad_residencia.nombre', 'like', '%' . $valor . '%');
                    } else if ($campo == "celular") {
                        $query->where('usr_app_candidatos_c.celular', 'like', '%' . $valor . '%');
                    } else {
                        $query->where($campo, 'like', '%' . $valor . '%');
                    }
                    break;
                case 'Igual a':
                    if ($campo == "num_doc") {
                        $query->where('usr_app_candidatos_c.num_doc', '=',  $valor);
                    } else if ($campo == "tipo_documento") {
                        $query->where('tipo_documento.des_tip', '=', $valor);
                    } else if ($campo == "primer_nombre") {
                        $query->where('usr_app_candidatos_c.primer_nombre', '=', $valor);
                    } else if ($campo == "primer_apellido") {
                        $query->where('usr_app_candidatos_c.primer_apellido', '=', $valor);
                    } else if ($campo == "ciudad_residencia") {
                        $query->where('ciudad_residencia.nombre',  '=', $valor);
                    } else if ($campo == "celular") {
                        $query->where('usr_app_candidatos_c.celular', '=', $valor);
                    } else {
                        $query->where($campo, '=', $valor);
                    }
                    break;
                case 'Igual a fecha':
                    $query->whereDate('usr_app_matriz_riesgo.' . $campo, '=', $valor);
                    break;
                case 'Entre':
                    $query->whereDate('usr_app_matriz_riesgo.' . $campo, '>=', $valor)
                        ->whereDate('usr_app_matriz_riesgo.' . $campo, '<=', $valor2);
                    break;
            }

            $result = $query->paginate();
            return response()->json($result);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function validacandidato($numero_identificacion, $index, $tipo_documento, $validacion_interna = false)
    {

        $trump = ListaTrump::where('cod_emp', '=',  (string) $numero_identificacion)->select('nombre', 'bloqueado')->first();
        if (isset($trump) && $trump->bloqueado == '1') {
            if ($validacion_interna) {
                return  response()->json(['status' => 'error', 'motivo' => '1', 'documento' => $numero_identificacion]);
            }
            return response()->json(['status' => 'error', 'titulo' => 'Error', 'message' => 'El candidato con número de documento ' . $numero_identificacion . ' no pudo ser registrado, por favor ponsagase en contacto con un asesor',  'documento' => $numero_identificacion]);
        }

        $activo = DashboardActivos::where('cod_emp', '=',  (string) $numero_identificacion)
            ->where('tip_ide', '=', $tipo_documento)
            ->select()
            ->first();
        if (isset($activo)) {
            if ($validacion_interna) {
                return  response()->json(['status' => 'error', 'motivo' => '1', 'documento' => $numero_identificacion]);
            }
            return response()->json(['status' => 'error', 'titulo' => 'Candidato laborando', 'message' => 'El candidato con número de documento ' . $numero_identificacion . ' se encuentra laborando actualmente, comuniquese con un asesor para más información.', 'documento' => $numero_identificacion]);
        }

        $en_proceso = formularioGestionIngreso::join('usr_app_usuarios as us', 'us.id', 'usr_app_formulario_ingreso.candidato_id')
            ->join('usr_app_candidatos_c as can', 'can.usuario_id', 'us.id')
            ->join('usr_app_estados_ingreso as est', 'est.id', 'usr_app_formulario_ingreso.estado_ingreso_id')
            ->where('can.num_doc', '=', (string) $numero_identificacion)
            ->where('can.tip_doc_id', '=', $tipo_documento)
            ->select(
                'us.id',
                'est.nombre as nombre_estado',
                'est.id as estado_id'
            )
            ->first();
        $estados_id = array(1, 35, 2, 3, 4, 5, 6, 13, 33, 34, 14, 16, 36, 37, 9, 10); // estos son los id de los estados de seiya que son bloqueantes para registrar un candidato en un servicio
        if (isset($en_proceso)) {
            $estado = $en_proceso->estado_id;
            if (in_array($estado, $estados_id)) {
                if ($validacion_interna) {
                    return  response()->json(['status' => 'error', 'motivo' => '1', 'documento' => $numero_identificacion]);
                }
                return response()->json(['status' => 'error', 'titulo' => 'Candidato en proceso', 'message' => 'El candidato con número de documento ' . $numero_identificacion . ' se encuentra en proceso de seleción o contratación actualmente, comuniquese con un asesor para más información.', 'documento' => $numero_identificacion]);
            }
        }

        $usuario = UsuariosCandidatosModel::join('usr_app_usuarios as us', 'us.id', 'usr_app_candidatos_c.usuario_id')
            ->where('num_doc', '=', (string) $numero_identificacion)
            ->where('tip_doc_id', '=', $tipo_documento)
            ->select(
                'usr_app_candidatos_c.usuario_id',
                DB::RAW("CONCAT(usr_app_candidatos_c.primer_nombre,' ',usr_app_candidatos_c.segundo_nombre) AS nombres"),
                DB::RAW("CONCAT(usr_app_candidatos_c.primer_apellido,' ',usr_app_candidatos_c.segundo_apellido) AS apellidos"),
                'usr_app_candidatos_c.celular',
                'us.email as correo'
            )
            ->first();
        if (isset($usuario)) {
            if ($validacion_interna) {
                return  response()->json(['status' => 'success', 'motivo' => '1', 'usuario' => $usuario]);
            }
            $usuario->index = $index;
            return response()->json(['status' => 'success', 'titulo' => 'success', 'index' => $index, 'motivo' => '1',  'usuario_id' => $usuario]);
        }

        if ($validacion_interna) {
            return  response()->json(['status' => 'success', 'motivo' => '2']);
        }
        return response()->json(['status' => 'success', 'titulo' => 'success', 'index' => $index, 'motivo' => '2']);
    }

    public function validaCorreoCandidato($correo)
    {
        $usuario = User::where('email', '=', $correo)->first();
        if ($usuario) {
            return response()->json(['status' => 'error', 'correo' => $correo]);
        }
    }
}
