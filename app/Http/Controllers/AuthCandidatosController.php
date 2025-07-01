<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\UsuariosCandidatosModel;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Http\Controllers\EnvioCorreoController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\LoginUsuariosModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use App\Models\ExperienciasLaboralesCandidatosModel;
use Illuminate\Support\Facades\Http;


class AuthCandidatosController extends Controller
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!$token = Auth::guard()->attempt([
            'email' => $request->email,
            'password' => $request->password,
            'estado_id' => "1",

        ])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Credenciales inválidas'
            ], 401);
        }
        return response()->json([
            'status'       => 'success',
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() / 60 / 60 / 8,
        ], 200);
    }


    public function createUserCandidato(Request $request)
    {
        // Validar presencia del token
        if (!$request->has('captchaToken')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token de seguridad faltante'
            ], 400);
        }

        // Verificar CAPTCHA con manejo de errores
        try {
            $captchaValid = $this->verificarCaptcha($request->captchaToken);

            if (!$captchaValid) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Verificación de seguridad fallida'
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo validar la seguridad del formulario'
            ], 500);
        }
        $existingUser = UsuariosCandidatosModel::where('num_doc', $request->numero_documento)
            ->where('tip_doc_id', $request->doc_tip_id)
            ->first();

        if ($existingUser) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ya existe un usuario registrado con este número de documento'
            ], 422);
        }
        $existingUser2 = User::where('email', $request->email)->first();

        if ($existingUser2) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ya existe un usuario registrado con este correo electrónico'
            ], 422);
        }
        DB::beginTransaction();
        $token = Str::random(60);
        try {
            $loginUser =  new User;
            $loginUser->email = $request->email;
            $loginUser->password = bcrypt($request->password);
            $loginUser->estado_id = "1";
            $loginUser->rol_id = 54;
            $loginUser->oculto = 0;
            $loginUser->tipo_usuario_id = 3;
            $loginUser->marca_temporal = $token;
            $loginUser->confirma_correo = 0;
            $loginUser->confirma_terminos = 1;
            $loginUser->save();

            $candidato = new UsuariosCandidatosModel;
            $candidato->usuario_id = $loginUser->id;
            $candidato->primer_nombre = $request->nombre;
            $candidato->primer_apellido = $request->apellidos;
            $candidato->num_doc = $request->numero_documento;
            $candidato->tip_doc_id = $request->doc_tip_id;
            $candidato->celular = $request->telefono;
            $candidato->save();

            $this->enviarBienvenida($candidato->tip_doc_id, $candidato->num_doc);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario, por favor intenta nuevamente']);
        }
    }

    public function mostrarUsuarios()
    {
        $users = UsuariosCandidatosModel::get();
        return response()->json($users);
    }

    public function enviarTokenRecuperacion(Request $request, $num_doc)
    {

        $urlFront = Config::get('app.URL_FRONT') . "#";
        $candidato = UsuariosCandidatosModel::where('num_doc', $num_doc)->where('tip_doc_id', $request->doc_tip_id)->first();
        if (!$candidato) {
            return response()->json(['status' => 'error', 'message' => 'No existe cuenta con este número de documento']);
        }
        $user = User::where('id', $candidato->usuario_id)->first();

        // Generar token
        $token = Str::random(60);

        $resetUrl = $urlFront . "/reset-password?token=$token&email={$user->email}";
        $subject = 'Cambio de contraseña';
        $nomb_membrete = 'Informe de servicio';

        $body = "<h2>Recuperación de contraseña</h2>
        <br>Hemos recibido una solicitud para el reestablecimiento de su contraseña.<br><br> 
        Por favor <a href=\"$resetUrl\">clic aquí</a> para continuar con el proceso.<br><br>
        Cualquier información adicional podrá ser atendida en la línea Servisai de Saitemp S.A. marcando al (604) 4485744. Con gusto uno de nuestros facilitadores atenderá su llamada.<br><br> 
        <b>Simplificando conexiones, facilitando experiencias.</b>";


        // Datos del correo
        $correo = [
            'subject' => $subject,
            'body' => $body,
            'to' => $user->email,
            'cc' => '',
        ];

        $user->marca_temporal = $token;
        $user->save();
        $EnvioCorreoController = new EnvioCorreoController();
        $requestEmail = Request::createFromBase(new Request($correo));


        return $EnvioCorreoController->sendEmailExternal($requestEmail);
    }

    public function recuperarContraseña(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email'      => 'required|email|exists:usr_app_usuarios,email',
            'password'  => 'required|string|min:6|confirmed',
            'token'       => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $user = User::where('email', $request->email)
            ->where('marca_temporal', $request->token)
            ->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Enlace inválido o expirado.'], 400);
        }


        // Verificar caducidad (1 hora)
        $tokenLifetime = 10; // En minutos
        if (Carbon::parse($user->updated_at)->addMinutes($tokenLifetime)->isPast()) {
            return response()->json(['status' => 'error', 'message' => 'El enlace ha expirado.'], 400);
        }
        // Actualizar contraseña
        $user->password = bcrypt($request->password);
        $user->marca_temporal = null; // Eliminar el token
        $user->updated_at = now();
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'La contraseña se ha restablecido correctamente.']);
    }

    public function updateCandidatoUser(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $login = User::find($id);
            $user = UsuariosCandidatosModel::where('usuario_id', $id)->first();

            if (!$user) {
                return response()->json(['status' => 'error', 'message' => 'Usuario no encontrado'], 404);
            }

            $existingUser = UsuariosCandidatosModel::where('num_doc', $user->num_doc)
                ->where('tip_doc_id', $request->tip_doc_id)
                ->first();
            if ($existingUser && $existingUser->usuario_id != $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ya existe un usuario registrado con este número de documento'
                ], 422);
            }
            $existingUser2 = User::where('email', $request->email)->first();

            if ($existingUser2 && $existingUser2->id != $id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Ya existe un usuario registrado con este correo electrónico'
                ], 422);
            }

            $user->primer_nombre = $request->nombre;
            $login->estado_id = $request->estado_id;
            $user->primer_apellido = $request->apellidos;
            $user->celular = $request->celular;
            $user->tip_doc_id = $request->tip_doc_id;
            $login->email = $request->email;
            if (!empty($request->password)) {
                $login->password = bcrypt($request->password);
            }
            $user->save();
            $login->save();

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Usuario actualizado exitosamente']);
        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['status' => 'error', 'message' => 'Error al guardar el formulario: utilice otro correo electrónico']);
        }
    }
    public function enviarBienvenida($doc_tip_id, $num_doc)
    {

        $urlFront = Config::get('app.URL_FRONT') . "#";
        $candidato = UsuariosCandidatosModel::where('num_doc', $num_doc)->where('tip_doc_id', $doc_tip_id)->first();
        if (!$candidato) {
            return response()->json(['status' => 'error', 'message' => 'No existe cuenta con este número de documento']);
        }
        $user = User::where('id', $candidato->usuario_id)->first();
        $token = $user->marca_temporal;
        // Generar token
        $resetUrl = $urlFront . "/logincandidatos?token=$token&email={$user->email}";
        /*    $resetUrl = $urlFront . "/logincandidatos"; */
        $subject = 'Bienvenido(a) al portal Saitemp';
        $nomb_membrete = 'Informe de servicio';
        $nombre = ucwords(strtolower($candidato->primer_nombre));

        $body = "<h2>Bienvenido(a) $nombre</h2>
        <brSu cuenta ha sido creada satisfactoriamente<br><br> 
        Para iniciar sesión haz <a href=\"$resetUrl\">clic aquí</a> en donde podrás completar tu hoja de vida.<br><br>
        Cualquier información adicional podrá ser atendida en la línea Servisai de Saitemp S.A. marcando al (604) 4485744. Con gusto uno de nuestros facilitadores atenderá su llamada.<br><br> 
        <b>Simplificando conexiones, facilitando experiencias.</b>";


        // Datos del correo
        $correo = [
            'subject' => $subject,
            'body' => $body,
            'to' => $user->email,
            'cc' => '',
        ];

        $EnvioCorreoController = new EnvioCorreoController();
        $requestEmail = Request::createFromBase(new Request($correo));


        return $EnvioCorreoController->sendEmailExternal($requestEmail);
    }
    public function verificarCaptcha($token)
    {
        $secret = Config::get('app.CAPTCHA_KEY');


        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => request()->ip()
        ]);

        if ($response->failed()) {
            throw new \Exception('Error en la API de reCAPTCHA: ' . $response->status());
        }

        $body = $response->json();

        if (!isset($body['success'])) {
            throw new \Exception('Respuesta inválida de reCAPTCHA');
        }

        // Para reCAPTCHA v3: Verificar score (ajustar según necesidades)
        if (isset($body['score']) && $body['score'] < 0.5) {
            return false;
        }

        return $body['success'];
    }
    public function verificarCorreo(Request $request)
    {
        $user = User::where('email', $request->email)
            ->where('marca_temporal', $request->token)
            ->first();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Enlace inválido o expirado.'], 400);
        }


        // Verificar caducidad (1 hora)
        $tokenLifetime = 1440; // En minutos
        if (Carbon::parse($user->updated_at)->addMinutes($tokenLifetime)->isPast()) {
            return response()->json(['status' => 'error', 'message' => 'El enlace ha expirado.'], 400);
        }
        $user->confirma_correo = 1;
        $user->marca_temporal = null; // Eliminar el token
        $user->updated_at = now();
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Correo verificado, ahora puede iniciar sesión']);
    }
    public function updateTratamientoDatos($id)
    {
        $login = User::find($id);
        $login->confirma_terminos = 1;
        if ($login->save()) {
            return response()->json(['status' => 'success', 'message' => 'Politica de tratamiento de daots actualizada, ahora puede iniciar sesión']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Error a actualizar'], 400);
        }
    }
}