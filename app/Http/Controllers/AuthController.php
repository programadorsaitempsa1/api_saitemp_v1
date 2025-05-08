<?php

namespace App\Http\Controllers;

use App\Models\SigContratoEmpleado;
use App\Models\SigEmpleados;
use App\Models\SigUsuarioContrato;
use App\Models\User;
use App\Models\UsuarioDebidaDiligencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;


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
        if ($request->password == '') {
            return response()->json(['status' => 'error', 'message' => 'Por favor ingrese una contraseña correcta']);
        }
        if (str_contains($request->email, '@')) {
            $user = explode('@', $request->email)[0];
        } else {
            $user = $request->email;
        }

        $ldapconn = ldap_connect('saitempsa.local');
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
        try {
            if ($ldapconn) {
                try {
                    $ldapbind = ldap_bind($ldapconn, $user . '@saitempsa.local',  $request->password);
                    if ($ldapbind) {
                        ldap_close($ldapconn);
                        $user = User::where('email', $request->email)->first();
                        $uuid = $this->guadarMarcaTemporal($user->email);
                        if ($user->estado_id == 2) {
                            return response()->json(['status' => 'error', 'message' => 'Este usuario se encuentra inactivo, por favor consulte al administrador del sistema']);
                        }
                        if ($user) {
                            Auth::guard('no-password-validation')->login($user);
                            $token = JWTAuth::fromUser($user);
                            return response()->json([
                                'access_token' => $token,
                                'token_type' => 'bearer',
                                'expires_in' => auth()->factory()->getTTL() / 60 / 60 / 8,
                                'marca' => $uuid
                            ]);
                        }

                        return response()->json(['error' => 'Unauthenticated.'], 401);
                    }
                } catch (\Exception $e) {
                    ldap_close($ldapconn);
                    $validator = Validator::make($request->all(), [
                        'email' => 'required',
                        // 'email' => 'required|email',
                        'password' => 'required|string|min:6',
                    ]);

                    if ($validator->fails()) {
                        return response()->json($validator->errors(), 422);
                    }

                    $token = auth()->attempt($validator->validated());
                    if (!$token) {
                        return response()->json(['status' => 'error', 'message' => 'Por favor verifique sus datos de inicio de sesión e intente nuevamente']);
                    }

                    $token = $this->createNewToken($token);
                    return $token;
                }
            }
        } catch (\Exception $e) {
            return $e;
            return response()->json(['status' => 'error', 'message' => 'No se pudo establecer la conexión con el servidor LDAP.']);
        }
    }

    public function guadarMarcaTemporal($email)
    {
        $user = User::where('email', $email)->first();
        $uuid = (string) Str::orderedUuid();
        $user->marca_temporal =  $uuid;
        if ($user->save()) {
            return $user->marca_temporal;
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $user = new User;
        $user->nombres = $request->nombres;
        $user->apellidos = $request->apellidos;
        $user->documento_identidad = $request->documento_identidad;
        $user->usuario = $request->usuario;
        $user->contrasena_correo = Crypt::encryptString($request->contrasena_correo);
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        if (is_numeric($request->email)) { // se verifica si el usuario a registrar es un número de documento para asignar el rol de cliente
            $user->rol_id = 53;
            $user->empresa_cliente = 1;
        } else {
            $user->rol_id = $request->rol_id == '' ? 3 : $request->rol_id;
        }
        $archivos = $request->files->all();
        $contador = 1;
        foreach ($archivos as $archivo) {
            if ($contador <= 2) {

                $nombreArchivoOriginal = ($archivo)->getClientOriginalName();
                $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                $carpetaDestino = './upload/';
                ($archivo)->move($carpetaDestino, $nuevoNombre);
                $user->{'imagen_firma_' . $contador} = ltrim($carpetaDestino, '.') . $nuevoNombre;
                $contador++;
            }
        }

        if ($user->save()) {
            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Ha ocurrido un error al guardar los datos de usuario']);
        }
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
    protected function createNewToken($token, $uuid = null)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60 * 60 * 8
        ]);
    }
}