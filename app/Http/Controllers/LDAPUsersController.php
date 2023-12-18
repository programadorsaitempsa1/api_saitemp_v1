<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\User;
use Mockery\Undefined;

class LDAPUsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($cantidad)
    {
        // Configuración de conexión
        $ldap_server = "saitempsa.local";  // URL del servidor LDAP
        $ldap_user = "programador1@saitempsa.local";   // Usuario con permisos para buscar en el directorio
        $ldap_pass = "Micro123*#";              // Contraseña del usuario
        $ldap_base_dn = "dc=saitempsa,dc=local";  // DN de la unidad organizativa o el dominio a buscar

        // Conexión al servidor LDAP
        $ldap_conn = ldap_connect($ldap_server);
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_bind($ldap_conn, $ldap_user, $ldap_pass);

        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);
        // ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        $search_attrs = array("*", "memberOf");

        // Búsqueda de usuarios
        $search_filter = "(objectClass=user)";  // Filtro de búsqueda para usuarios
        $search_attrs = array("cn", "samaccountname", "mail");  // Atributos a devolver para cada usuario
        $search_result = ldap_search($ldap_conn, $ldap_base_dn, $search_filter, $search_attrs);
        $users = ldap_get_entries($ldap_conn, $search_result);

        // Cierre de la conexión
        ldap_unbind($ldap_conn);

        $usuarios = [];
        $usuario = [];
        foreach ($users as $user) {
            if (is_array($user) && isset($user["samaccountname"])) {
                $usuario['nombre'] = $user["cn"][0];
                $usuario['usuario'] = $user["samaccountname"][0];
                array_push($usuarios, $usuario);
            }
        }

        $collection = new Collection($usuarios);

        $perPage = $cantidad;
        $page = request('page', 1);

        $paginatedData = $collection->slice(($page - 1) * $perPage, $perPage)->all();

        $paginated = new LengthAwarePaginator(
            $paginatedData,
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginated;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $rol = $request[0]['rol'];
        $array = $request->all();
        $errors = 'Los usuarios ';
        $swich = false;
        for ($i = 1; $i < count($array); $i++) {
            try {
                $user = new User;
                $user->nombres = $array[$i]['nombre'];
                $user->apellidos = $request->apellidos;
                $user->documento_identidad = $request->documento_identidad;
                $user->email = $array[$i]['usuario'];
                $user->password = '';
                $user->rol_id = $rol == '' ? 3 : $rol;
                $user->save();
            } catch (\Exception $e) {
                $errors .= $array[$i]['nombre'] . ', ';
                $swich = true;
            }
        }
        if ($swich) {
            return response()->json(['status' => 'error', 'message' => $errors . ' ya se encuentran registrados']);
        } else {
            return response()->json(['status' => 'success', 'message' => 'Usuarios guardados de manera exitosa']);
        }
    }

    public function userById($user)
    {
        // return $user;
        $ldap_server = "saitempsa.local";
        // Autenticación en el Directorio Activo
        $ldaprdn = "programador1@saitempsa.local"; // usuario de administración del Directorio Activo
        $ldappass = "Micro123*#"; // contraseña del usuario de administración del Directorio Activo
       
        $ldap = ldap_connect($ldap_server) or die("No se pudo conectar al servidor de Directorio Activo.");

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
        $ldapbind = ldap_bind($ldap, $ldaprdn, $ldappass);

        if ($ldapbind) {
            // Búsqueda de usuarios por nombre o usuario
            $filter = "(&(objectClass=user)(|(sAMAccountName=*$user*)(cn=*$user*)))";
            $result = ldap_search($ldap, "dc=saitempsa,dc=local", $filter);
            $entries = ldap_get_entries($ldap, $result);

            $usuarios = [];
            $usuario = [];
            foreach ($entries as $user) {
                if (is_array($user) && isset($user["samaccountname"])) {
                    $usuario['nombre'] = $user["cn"][0];
                    $usuario['usuario'] = $user["samaccountname"][0];
                    array_push($usuarios, $usuario);
                }
            }
    
            $collection = new Collection($usuarios);
    
            $perPage = 12;
            $page = request('page', 1);
    
            $paginatedData = $collection->slice(($page - 1) * $perPage, $perPage)->all();
    
            $paginated = new LengthAwarePaginator(
                $paginatedData,
                $collection->count(),
                $perPage,
                $page,
                ['path' => request()->url(), 'query' => request()->query()]
            );
    
            return $paginated;
        } else {
            return "No se pudo autenticar en el servidor de Directorio Activo.";
        }

        // Cierre de la conexión al servidor de Directorio Activo
        ldap_close($ldap);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
