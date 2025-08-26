<?php

namespace App\Traits;

use App\Models\Permiso;

trait Permisos
{

    public function permisos()
    {
        $user = auth()->user();
        if (!empty($user)) {
            $result = Permiso::leftJoin('usr_app_permisos_roles as pr', 'pr.permiso_id', '=', 'usr_app_permisos.id')
                ->leftJoin('usr_app_permisos_usuarios as pu', 'pu.permiso_id', '=', 'usr_app_permisos.id')
                ->where(function ($query) use ($user) {
                    $query->where('pr.rol_id', '=', $user['rol_id'])
                        ->orWhere('pu.usuario_id', '=', $user['id']);
                })
                ->select(
                    'usr_app_permisos.alias'
                )
                ->distinct()
                ->get();

            $permisos = $result->pluck('alias') 
                ->map(function ($alias) {
                    return substr($alias, 1);
                })
                ->sort() 
                ->values() 
                ->toArray(); 

            return $permisos;
        } else {
            return [['alias' => 'P3']];
        }
    }
}
