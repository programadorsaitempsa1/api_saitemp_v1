<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecepcionEmpleado;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\ReferenciasModel;
use App\Models\ReferenciasFormularioEmpleado;

class RecepcionEmpleadoController extends Controller
{

    public function index()
    {
        $result = RecepcionEmpleado::where('cod_emp', "11")->first();
        if ($result) {
            return response()->json($result);
        } else {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
    }


    public function create(Request $request)
    {

        DB::beginTransaction();
        try {
            $result = new RecepcionEmpleado;
            $fechaNacimientoFormated = Carbon::parse($request->fec_nac)->format('d-m-Y H:i:s');
            $fechaExpedicionFormated = Carbon::parse($request->fec_expdoc)->format('d-m-Y H:i:s');
            $result->cod_emp = $request->cod_emp;
            $result->ap1_emp = $request->ap1_emp;
            $result->ap2_emp = $request->ap2_emp;
            $result->nom_emp = $request->nom1_emp;
            $result->nom1_emp = $request->nom1_emp;
            $result->nom2_emp = $request->nom2_emp ?? '';
            $result->tip_ide = $request->tip_ide;
            $result->pai_exp = $request->pai_exp;
            $result->ciu_exp = $request->ciu_exp;
            $result->fec_nac = $fechaNacimientoFormated;
            $result->cod_pai = $request->cod_pai;
            $result->cod_dep = $request->cod_dep;
            $result->cod_ciu = $request->cod_ciu;
            $result->sex_emp = $request->sex_emp;
            $result->gru_san = $request->gru_san;
            $result->fac_rhh = $request->fac_rhh;
            $result->est_civ = $request->est_civ;
            $result->dir_res = $request->dir_res;
            $result->tel_res = $request->tel_res;
            $result->nac_emp = $request->nac_emp;
            $result->pai_res = $request->pai_res;
            $result->dpt_res = $request->dpt_res;
            $result->per_car = $request->per_car;
            $result->e_mail = $request->e_mail;
            $result->tel_cel = $request->tel_cel;
            $result->dpt_exp = $request->dpt_exp;
            $result->Niv_aca = $request->Niv_aca;
            $result->barrio = $request->barrio;
            $result->cta_ban = $request->cta_ban;
            $result->raza = $request->raza;
            $result->cod_grupo = $request->cod_grupo;
            $result->cod_ban = $request->cod_ban;
            $result->fec_expdoc = $fechaExpedicionFormated;
            $result->ciu_res = $request->ciu_res;
            $result->est_soc = $request->est_soc;
            $result->num_ide = $request->cod_emp;
            $result->save();

            foreach ($request->referencias as $referencia) {
                $resultReferencia = new ReferenciasFormularioEmpleado;
                $resultReferencia->cod_emp = $result->cod_emp;
                $resultReferencia->num_ref = $referencia['num_ref'];
                $resultReferencia->parent = $referencia['parent'];
                $resultReferencia->cel_ref = $referencia['cel_ref'];
                $resultReferencia->nom_ref = $referencia['nom_ref'];
                $resultReferencia->tip_ref = $referencia['tip_ref'];
                $resultReferencia->ocu_ref = 0;
                $resultReferencia->save();
            }
            foreach ($request->familiares as $referencia) {
                $resultReferencia = new ReferenciasModel;
                if ($referencia['ap1_fam'] != "") {
                    $fechaNacimientoFormated = Carbon::parse($referencia['fec_nac'])->format('d-m-Y H:i:s');
                    $resultReferencia->cod_emp = $result->cod_emp;
                    $resultReferencia->ap1_fam = $referencia['ap1_fam'];
                    $resultReferencia->ap2_fam = $referencia['ap2_fam'];
                    $resultReferencia->nom_fam = $referencia['nom_fam'];
                    $resultReferencia->tip_fam = $referencia['tip_fam'];
                    $resultReferencia->fec_nac = $fechaNacimientoFormated;
                    $resultReferencia->ocu_fam = $referencia['ocu_fam'];
                    $resultReferencia->save();
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa', 'id' => $result]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }

    public function searchByCodEmp($cod_emp)
    {
        try {
            $result = RecepcionEmpleado::join('gen_tipide as tipoId', 'tipoId.cod_tip', 'GTH_RptEmplea.tip_ide')
                ->join('gen_paises as pais_exp_name', 'pais_exp_name.cod_pai', 'GTH_RptEmplea.pai_exp')
                ->join('gen_paises as pais_res_name', 'pais_res_name.cod_pai', 'GTH_RptEmplea.pai_res')
                ->join('gen_paises as pais_nac_name', 'pais_nac_name.cod_pai', 'GTH_RptEmplea.cod_pai')
                ->join('gen_deptos as depto_exp_name', function ($join) {
                    $join->on('depto_exp_name.cod_dep', '=', 'GTH_RptEmplea.dpt_exp')
                        ->orOn('depto_exp_name.cod_dep', '=', 'GTH_RptEmplea.pai_exp');
                })
                ->join('gen_deptos as depto_res_name', function ($join) {
                    $join->on('depto_res_name.cod_dep', '=', 'GTH_RptEmplea.dpt_res')
                        ->orOn('depto_res_name.cod_dep', '=', 'GTH_RptEmplea.pai_res');
                })
                ->join('gen_deptos as depto_nac_name', function ($join) {
                    $join->on('depto_nac_name.cod_dep', '=', 'GTH_RptEmplea.cod_dep')
                        ->orOn('depto_nac_name.cod_dep', '=', 'GTH_RptEmplea.cod_pai');
                })
                ->join('gen_ciudad as ciudad_exp_name', function ($join) {
                    $join->on('ciudad_exp_name.cod_ciu', '=', 'GTH_RptEmplea.ciu_exp')
                        ->orOn('ciudad_exp_name.cod_ciu', '=', 'GTH_RptEmplea.dpt_exp')
                        ->where('ciudad_exp_name.cod_pai', '=', 'GTH_RptEmplea.pai_exp');
                })
                ->join('gen_ciudad as ciudad_res_name', function ($join) {
                    $join->on('ciudad_res_name.cod_ciu', '=', 'GTH_RptEmplea.ciu_res')
                        ->orOn('ciudad_res_name.cod_ciu', '=', 'GTH_RptEmplea.dpt_res')
                        ->where('ciudad_res_name.cod_pai', '=', 'GTH_RptEmplea.pai_res');
                })
                ->join('gen_ciudad as ciudad_nac_name', function ($join) {
                    $join->on('ciudad_nac_name.cod_ciu', '=', 'GTH_RptEmplea.cod_ciu')
                        ->orOn('ciudad_nac_name.cod_ciu', '=', 'GTH_RptEmplea.cod_dep')
                        ->where('ciudad_nac_name.cod_pai', '=', 'GTH_RptEmplea.cod_pai');
                })
                ->join('gen_bancos as banco_name', 'banco_name.cod_ban', 'GTH_RptEmplea.cod_ban')
                ->join('rhh_tbclaest as nivelAcademico_name', 'nivelAcademico_name.tip_est', 'GTH_RptEmplea.Niv_aca')
                ->join('GTH_EstCivil as estadoCivil_name', 'estadoCivil_name.cod_est', 'GTH_RptEmplea.cod_est')
                ->join('gen_grupoetnico as etnia_name', 'etnia_name.cod_grupo', 'GTH_RptEmplea.cod_grupo')
                ->where('cod_emp', $cod_emp)
                ->select(
                    'tipoId.des_tip as tipIde_nombre',
                    'pais_exp_name.nom_pai as pais_exp_nombre',
                    'pais_res_name.nom_pai as pais_res_nombre',
                    'pais_res_name.nom_pai as pais_nac_nombre',
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
            $referencias = ReferenciasFormularioEmpleado::where('cod_emp', $cod_emp)->get();
            $result["referencias"] = $referencias;
            $familiares = ReferenciasModel::where('cod_emp', $cod_emp)->get();
            $result["familiares"] = $familiares;
            if ($result->cod_emp != null) {
                return response()->json(['status' => 'success', 'data' => $result]);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Empleado no encontrado'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al buscar el empleado, por favor intenta nuevamente']);
        }
    }

    public function updateByCodEmp(Request $request, $cod_emp)
    {
        try {
            DB::beginTransaction();
            $result = RecepcionEmpleado::find($cod_emp);
            $fechaNacimientoFormated = Carbon::parse($request->fec_nac)->format('d-m-Y H:i:s');
            $fechaExpedicionFormated = Carbon::parse($request->fec_expdoc)->format('d-m-Y H:i:s');
            $result->ap1_emp = $request->ap1_emp;
            $result->ap2_emp = $request->ap2_emp;
            $result->nom_emp = $request->nom1_emp;
            $result->nom1_emp = $request->nom1_emp;
            $result->nom2_emp = $request->nom2_emp ?? '';
            $result->tip_ide = $request->tip_ide;
            $result->pai_exp = $request->pai_exp;
            $result->ciu_exp = $request->ciu_exp;
            $result->fec_nac = $fechaNacimientoFormated;
            $result->cod_pai = $request->cod_pai;
            $result->cod_dep = $request->cod_dep;
            $result->cod_ciu = $request->cod_ciu;
            $result->sex_emp = $request->sex_emp;
            $result->gru_san = $request->gru_san;
            $result->fac_rhh = $request->fac_rhh;
            $result->est_civ = $request->est_civ;
            $result->dir_res = $request->dir_res;
            $result->tel_res = $request->tel_res;
            $result->nac_emp = $request->nac_emp;
            $result->pai_res = $request->pai_res;
            $result->dpt_res = $request->dpt_res;
            $result->per_car = $request->per_car;
            $result->e_mail = $request->e_mail;
            $result->tel_cel = $request->tel_cel;
            $result->dpt_exp = $request->dpt_exp;
            $result->Niv_aca = $request->Niv_aca;
            $result->barrio = $request->barrio;
            $result->cta_ban = $request->cta_ban;
            $result->raza = $request->raza;
            $result->cod_grupo = $request->cod_grupo;
            $result->cod_ban = $request->cod_ban;
            $result->fec_expdoc = $fechaExpedicionFormated;
            $result->ciu_res = $request->ciu_res;
            $result->est_soc = $request->est_soc;
            $result->num_ide = $request->cod_emp;
            $result->save();
            foreach ($request->referencias as $referencia) {
                if (isset($referencia['cod_emp']) && isset($referencia['num_ref'])) {
                    $resultReferencia = ReferenciasFormularioEmpleado::where('cod_emp', $referencia['cod_emp'])
                        ->where('num_ref', $referencia['num_ref'])
                        ->first();

                    if ($resultReferencia) {
                        $resultReferencia->parent = $referencia['parent'];
                        $resultReferencia->cel_ref = $referencia['cel_ref'];
                        $resultReferencia->nom_ref = $referencia['nom_ref'];
                        $resultReferencia->ocu_ref = 0; // Si necesitas cambiar este valor, asegúrate de que sea correcto
                        $resultReferencia->save();
                    } else {
                    }
                } else {
                }
            }
            foreach ($request->familiaresConsulta as $index => $referencia) {
                $requestFamiliares = $request->familiares;
                if (isset($referencia['cod_emp']) && isset($referencia['ap1_fam'])) {
                    $resultReferencia = ReferenciasModel::where('cod_emp', $referencia['cod_emp'])
                        ->where('ap1_fam', $referencia['ap1_fam'])
                        ->where('nom_fam', $referencia['nom_fam'])
                        ->first();
                    $fechaNacimientoFormated = Carbon::parse($requestFamiliares[$index]['fec_nac'])->format('d-m-Y H:i:s');
                    $resultReferencia->ap1_fam = $requestFamiliares[$index]['ap1_fam'];
                    $resultReferencia->ap2_fam = $requestFamiliares[$index]['ap2_fam'];
                    $resultReferencia->nom_fam = $requestFamiliares[$index]['nom_fam'];
                    $resultReferencia->tip_fam = $requestFamiliares[$index]['tip_fam'];
                    $resultReferencia->fec_nac = $fechaNacimientoFormated;
                    $resultReferencia->ocu_fam = $requestFamiliares[$index]['ocu_fam'];
                    $resultReferencia->save();
                }
            }
            if (count($request->familiaresConsulta) < count($request->familiares)) {
                $tamanoInicial = count($request->familiaresConsulta);
                for ($i = $tamanoInicial; $i < count($request->familiares); $i++) {
                    $referencia = $request->familiares[$i];
                    $resultReferencia = new ReferenciasModel;
                    if ($referencia['ap1_fam'] != "") {
                        $fechaNacimientoFormated = Carbon::parse($referencia['fec_nac'])->format('d-m-Y H:i:s');
                        $resultReferencia->cod_emp = $result->cod_emp;
                        $resultReferencia->ap1_fam = $referencia['ap1_fam'];
                        $resultReferencia->ap2_fam = $referencia['ap2_fam'];
                        $resultReferencia->nom_fam = $referencia['nom_fam'];
                        $resultReferencia->tip_fam = $referencia['tip_fam'];
                        $resultReferencia->fec_nac = $fechaNacimientoFormated;
                        $resultReferencia->ocu_fam = $referencia['ocu_fam'];
                        $resultReferencia->save();
                    }
                }
            }
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro actualizado de manera exitosa', 'id' => $result->cod_emp]);
        } catch (\Exception $e) {
            return $e;
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }
}
