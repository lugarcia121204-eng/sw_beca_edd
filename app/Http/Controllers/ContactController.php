<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Institution;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;
use App\Mail\ContactConfirmationMail;
use App\Http\Requests\ContactFormRequest;

class ContactController extends Controller
{
    public function getInstitutions()
    {
        try {
            $institutions = \App\Models\Institution::with('state')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($institution) {
                    return [
                        'id' => $institution->id,
                        'name' => $institution->name,
                        'state' => $institution->state->name ?? ''
                    ];
                });

            return response()->json($institutions);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar instituciones'], 500);
        }
    }

    public function sendContact(ContactFormRequest $request)
    {
        try {
            // Get Institution
            $institution = \App\Models\Institution::with('state')->find($request->institution_id);

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'institucion' => $institution->name . ' (' . ($institution->state->name ?? '') . ')',
                'message' => $request->message,
            ];

            \App\Models\ContactMessage::create([
                'name' => $data['name'], 'email' => $data['email'],
                'institution_name' => $data['institucion'], 'message' => $data['message'],
            ]);
            try {
                Mail::to(config('mail.from.address'))->queue(new ContactFormMail($data));
                Mail::to($request->email)->queue(new ContactConfirmationMail($data));
            } catch (\Throwable $mailError) {
                report($mailError);
            }

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado correctamente. Nos pondremos en contacto contigo pronto.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje. Por favor, intenta de nuevo más tarde.'
            ], 500);
        }
    }
}
