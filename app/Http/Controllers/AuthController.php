<?php

namespace App\Http\Controllers;

use App\Models\SigContratoEmpleado;
use App\Models\SigEmpleados;
use App\Models\SigUsuarioContrato;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
     
        $ldapconn = ldap_connect('saitempsa.local');
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        
        try{
            if ($ldapconn) {
                try{
                    $ldapbind = ldap_bind($ldapconn, $request->email . '@saitempsa.local', $request->password);
                    if ($ldapbind) {
                        ldap_close($ldapconn);
                        return 'usuario logueado con exito';
                    } 
                    // else {
                    //     ldap_close($ldapconn);
                    //     return 'Credenciales incorrectas.';
                    // }
                }catch(\Exception $e){
                    ldap_close($ldapconn);
                    return 'Credenciales incorrectas.';
                }
            }
        }catch(\Exception $e){
            return response()->json(['status'=>'error','message'=>'No se pudo establecer la conexión con el servidor LDAP.']);
        }


        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['status' => 'error', 'message'=>'Por favor verifique sus datos de inicio de sesión e intente nuevamente']);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombres' => 'required|string|between:2,100',
            'apellidos' => 'required|string|between:2,100',
            'documento_identidad' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100',
            'password' => 'required|string|min:6',
            'rol_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
      
        // DB::beginTransaction();

        // try {

            $user = User::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
            ));

            // if ($request->contrato_id != null || $request->contrato_id != '') {
            //     $usuario_contrato = new SigUsuarioContrato;
            //     $usuario_contrato->usuario_id = $user->id;
            //     $usuario_contrato->contrato_id = $request->contrato_id;
            //     $usuario_contrato->save();

            //     // inserta un usuario como empleado y lo asigna a un contrato,
            //     //  falta que se pueda actuañlizar la información
            //     //  del empleado desde módulo usuario
            //     // if ($request->empleado == true) {
            //     //     $empleado = new SigEmpleados;
            //     //     $empleado->nombres = $request->nombres;
            //     //     $empleado->apellidos = $request->apellidos;
            //     //     $empleado->documento_identidad = $request->documento_identidad;
            //     //     $empleado->tipo_documento_identidad_id = 1;
            //     //     $empleado->sig_cargo_id = 1;
            //     //     $empleado->save();

            //     //     $contrato_empleado = new SigContratoEmpleado;
            //     //     $contrato_empleado->empleado_id = $empleado->id;
            //     //     $contrato_empleado->contrato_id = $request->contrato_id;
            //     //     $contrato_empleado->zona_id = 1;
            //     //     $contrato_empleado->save();
            //     // }
            // }
            //   DB::commit();
              return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        // } catch (\Exception$e) {
        //     DB::rollback();
        //     // something went wrong
        //      return response()->json(['status' => 'error', 'message' => 'Hubo un error al procesar la solicitud por favor intente nuevamente']);
        //    // return response()->json($e);
        // }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully signed out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            // 'user' => auth()->user()
        ]);
    }

}
