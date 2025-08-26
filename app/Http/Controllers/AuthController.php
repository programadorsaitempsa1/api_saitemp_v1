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
use App\Models\UsuariosInternosModel;
use App\Models\Rol;


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
    // public function login(Request $request)
    // {
    //     if ($request->password == '') {
    //         return response()->json(['status' => 'error', 'message' => 'Por favor ingrese una contraseña correcta']);
    //     }

    //     // Extraer usuario del email (si tiene @)
    //     $user = str_contains($request->email, '@') ? explode('@', $request->email)[0] : $request->email;

    //     $ldapconn = ldap_connect('saitempsa.local');
    //     ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

    //     try {
    //         if ($ldapconn) {
    //             try {
    //                 $ldapbind = ldap_bind($ldapconn, $user . '@saitempsa.local', $request->password);
    //                 if ($ldapbind) {
    //                     ldap_close($ldapconn);

    //                     $user = User::where('email', $request->email)->first();

    //                     if (!$user) {
    //                         return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado.']);
    //                     }

    //                     if ($user->estado_id == 2) {
    //                         return response()->json(['status' => 'error', 'message' => 'Este usuario se encuentra inactivo, por favor consulte al administrador del sistema']);
    //                     }

    //                     // 🚨 Validación para usuarios tipo 3
    //                     if ($user->tipo_usuario_id == 3 && ($user->confirma_correo == 0)) {
    //                         return response()->json([
    //                             'status' => 'error',
    //                             'message' => 'Debe confirmar su correo y aceptar los términos para iniciar sesión.'
    //                         ]);
    //                     }

    //                     // Generar marca de tiempo
    //                     $uuid = $this->guadarMarcaTemporal($user->email);

    //                     Auth::guard('no-password-validation')->login($user);
    //                     $token = JWTAuth::fromUser($user);

    //                     return response()->json([
    //                         'access_token' => $token,
    //                         'token_type' => 'bearer',
    //                         'tipo_usuario_id' => $user->tipo_usuario_id,
    //                         'expires_in' => auth()->factory()->getTTL() / 60 / 60 / 8,
    //                         'marca' => $uuid
    //                     ]);
    //                 }
    //             } catch (\Exception $e) {
    //                 ldap_close($ldapconn);

    //                 $user = User::where('email', $request->email)->first();

    //                 if (!$user) {
    //                     return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado.']);
    //                 }

    //                 $validator = Validator::make($request->all(), [
    //                     'email' => 'required',
    //                     'password' => 'required|string|min:6',
    //                 ]);

    //                 if ($validator->fails()) {
    //                     return response()->json($validator->errors(), 422);
    //                 }

    //                 // 🚨 Validación para usuarios tipo 3
    //                 if ($user->tipo_usuario_id == 3 && ($user->confirma_correo == 0)) {
    //                     return response()->json([
    //                         'status' => 'error',
    //                         'message' => 'Debe confirmar su correo y aceptar los términos para iniciar sesión.'
    //                     ]);
    //                 }

    //                 $token = auth()->attempt($validator->validated());

    //                 if (!$token) {
    //                     return response()->json(['status' => 'error', 'message' => 'Por favor verifique sus datos de inicio de sesión e intente nuevamente']);
    //                 }

    //                 return $this->createNewToken($token, $user->tipo_usuario_id);
    //             }
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'message' => 'No se pudo establecer la conexión con el servidor LDAP.']);
    //     }
    // }

      public function login(Request $request)
    {
        if ($request->password == '') {
            return response()->json(['status' => 'error', 'message' => 'Por favor ingrese una contraseña correcta']);
        }

        // Extraer usuario del email (si tiene @)
        $user = str_contains($request->email, '@') ? explode('@', $request->email)[0] : $request->email;

        $ldapconn = ldap_connect('saitempsa.local');
        ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

        try {
            if ($ldapconn) {
                try {
                    $ldapbind = ldap_bind($ldapconn, $user . '@saitempsa.local', $request->password);
                    if ($ldapbind) {
                        ldap_close($ldapconn);

                        $user = User::where('email', $request->email)->first();

                        if (!$user) {
                            return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado.']);
                        }

                        if ($user->estado_id == 2) {
                            return response()->json(['status' => 'error', 'message' => 'Este usuario se encuentra inactivo, por favor consulte al administrador del sistema']);
                        }

                        // 🚨 Validación para usuarios tipo 3
                        if ($user->tipo_usuario_id == 3 && ($user->confirma_correo == 0)) {
                            return response()->json([
                                'status' => 'error',
                                'message' => 'Debe confirmar su correo y aceptar los términos para iniciar sesión.'
                            ]);
                        }

                        // Generar marca de tiempo
                        $uuid = $this->guadarMarcaTemporal($user->email);

                        Auth::guard('no-password-validation')->login($user);
                        $token = JWTAuth::fromUser($user);

                        return response()->json([
                            'access_token' => $token,
                            'token_type' => 'bearer',
                            'tipo_usuario_id' => $user->tipo_usuario_id,
                            'expires_in' => auth()->factory()->getTTL() / 60 / 60 / 8,
                            'marca' => $uuid
                        ]);
                    }
                } catch (\Exception $e) {
                    ldap_close($ldapconn);

                    $user = User::where('email', $request->email)->first();

                    if (!$user) {
                        return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado.']);
                    }

                    $validator = Validator::make($request->all(), [
                        'email' => 'required',
                        'password' => 'required|string|min:6',
                    ]);

                    if ($validator->fails()) {
                        return response()->json($validator->errors(), 422);
                    }

                    // 🚨 Validación para usuarios tipo 3
                    if ($user->tipo_usuario_id == 3 && ($user->confirma_correo == 0)) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Debe confirmar su correo y aceptar los términos para iniciar sesión.'
                        ]);
                    }

                    $token = auth()->attempt($validator->validated());

                    if (!$token) {
                        return response()->json(['status' => 'error', 'message' => 'Por favor verifique sus datos de inicio de sesión e intente nuevamente']);
                    }

                    return $this->createNewToken($token, $user->tipo_usuario_id);
                }
            }
        } catch (\Exception $e) {
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
        try {
            DB::beginTransaction();
            $user = new User;
            $user->email = $request->usuario;
            $user->password = bcrypt($request->password);
            $user->rol_id = $request->rol_id;
            $user->save();
            $user_interno = new UsuariosInternosModel;
            $user_interno->nombres = $request->nombres;
            $user_interno->apellidos = $request->apellidos;
            $user_interno->rol_usuario_interno_id = $request->rol_interno_id;
            $user_interno->documento_identidad = $request->documento_identidad;
            $user_interno->correo = $request->email;
            $user_interno->contrasena_correo = Crypt::encryptString($request->contrasena_correo);
            $user_interno->usuario_id = $user->id;
            $archivos = $request->files->all();
            $contador = 1;
            foreach ($archivos as $archivo) {
                if ($contador <= 2) {

                    $nombreArchivoOriginal = ($archivo)->getClientOriginalName();
                    $nuevoNombre = Carbon::now()->timestamp . "_" . $nombreArchivoOriginal;

                    $carpetaDestino = './upload/';
                    ($archivo)->move($carpetaDestino, $nuevoNombre);
                    $user_interno->{'imagen_firma_' . $contador} = ltrim($carpetaDestino, '.') . $nuevoNombre;
                    $contador++;
                }
            }
            $user_interno->save();
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado exitosamente']);
        } catch (\Exception $e) {
            DB::rollback();
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
    protected function createNewToken($token, $tipo_usuario_id, $uuid = null)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'tipo_usuario_id' => $tipo_usuario_id,
            'expires_in' => auth()->factory()->getTTL() * 60 * 60 * 8
        ]);
    }
}
