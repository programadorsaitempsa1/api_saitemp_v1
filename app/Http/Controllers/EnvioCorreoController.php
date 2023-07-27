<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\EnvioCorreo; 

class EnvioCorreoController extends Controller
{
    public function sendEmail(Request $request)
    {
        $request->validate([
            'from' => 'required|email',
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

    
        putenv("MAIL_USERNAME={$request->from}");
        putenv("MAIL_FROM_ADDRESS={$request->from}");
        putenv("MAIL_FROM_NAME=Saitemp");

        $from = $request->input('from');
        $to = $request->input('to');
        $subject = $request->input('subject');
        $body = $request->input('body');

        Mail::to($to)->send(new EnvioCorreo($subject, $body));

        return response()->json(['message' => 'Email sent successfully']);
    }
}
