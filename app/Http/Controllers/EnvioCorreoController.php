<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Mail;
// use Swift_SmtpTransport;
// use App\Mail\EnvioCorreo;
use Swift_SmtpTransport;
use Swift_Mailer;
use Swift_Message;
use Illuminate\Support\Facades\Crypt;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;

class EnvioCorreoController extends Controller
{
    public function sendEmail(Request $request)
    {

            $user = auth()->user();

            if ($user->usuario == '' || $user->usuario == null) {
                return 'Usuario no cuenta con correo electrónico';
            }

            if ($user->contrasena_correo == '' || $user->contrasena_correo == null) {
                return 'Usuario no cuenta con contraseña del correo';
            }
            // return $user;

            // $data = $request->to;
            // $encryptedData = Crypt::encryptString($data);

            // Desencriptar datos
            $password = Crypt::decryptString($user->contrasena_correo);

            // Obtener las credenciales de acceso dinámicas de alguna manera
            $smtpHost = 'smtp.gmail.com';
            $smtpPort = 587;
            $smtpEncryption = 'tls';
            $smtpUsername = $user->usuario;
            $smtpPassword = $password;

            // Configurar el DSN con las credenciales dinámicas
            $dsn = "smtp://$smtpUsername:$smtpPassword@$smtpHost:$smtpPort?encryption=$smtpEncryption";

            // Crear el transporte con el DSN configurado
            $transport = Transport::fromDsn($dsn);

            // Crear el mailer con el transporte configurado
            $mailer = new Mailer($transport);

            // Crear el mensaje de correo electrónico
            $email = (new Email())
                // ->from($user->usuario)
                ->from(new Address($user->usuario, $user->nombres))
                ->to($request->to) // Asegúrate de proporcionar una dirección de correo válida aquí
                // ->cc($request->cc_)
                // ->bcc($request->cco_)
                ->subject($request->subject)
                // ->text('This is a plain text email.')
                ->html($request->body);
                if (!empty($request->cc)) {
                    $email->cc($request->cc);
                }
                
                // Verificar si hay una dirección de correo para copia oculta (BCC) y agregarla si existe
                if (!empty($request->cco)) {
                    $email->bcc($request->cco);
                }

            // Enviar el mensaje
            $mailer->send($email);
            if ($mailer) {
                // El correo se envió correctamente, puedes agregar lógica adicional aquí si lo deseas
                return 'El correo electrónico se ha enviado correctamente.';
            } else {
                // Hubo un error al enviar el correo, puedes manejarlo adecuadamente aquí
                return 'Hubo un error al enviar el correo electrónico.';
            }
            // return $decryptedData;

            // return $encryptedData . ' '.  $decryptedData;

            // $request->validate([
            //     'from' => 'required|email',
            //     'to' => 'required|email',
            //     'subject' => 'required|string',
            //     'body' => 'required|string',
            // ]);



            // $from = $request->input('from');
            // $to = $request->input('to');
            // $subject = $request->input('subject');
            // $body = $request->input('body');

            // Mail::to($to)->send(new EnvioCorreo($subject, $body));
            // Mail::setSwiftMailer(null);

            // return response()->json(['message' => 'Email sent successfully']);
            // Configuración del transporte SMTP

            // ***************************************************************
            // $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
            //     ->setUsername($user->usuario)
            //     ->setPassword($password);

            // // Crear el Mailer usando el transporte configurado
            // $mailer = new Swift_Mailer($transport);

            // // Crear un mensaje
            // $message = (new Swift_Message($request->subject))
            //     ->setFrom([$user->email => 'Saitemp']) // Remitente
            //     ->setTo([$request->to]) // Destinatarios
            //     ->setBody($request->body, 'text/html'); // Contenido del mensaje

            // // Enviar el mensaje
            // $result = $mailer->send($message);

            // if ($result) {
            //     // El correo se envió correctamente, puedes agregar lógica adicional aquí si lo deseas
            //     return 'El correo electrónico se ha enviado correctamente.';
            // } else {
            //     // Hubo un error al enviar el correo, puedes manejarlo adecuadamente aquí
            //     return 'Hubo un error al enviar el correo electrónico.';
            // }
            //code...
       
    }
}
