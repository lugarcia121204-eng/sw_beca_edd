<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PriorityArea;
use App\Mail\VerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\RegisterUserRequest;
use Inertia\Inertia;

class RegisterController extends Controller
{
    /**
     * Muestra el formulario de registro
     */
    public function create()
    {
        return Inertia::render('Auth/Register', [
            'institutions' => \App\Models\Institution::orderBy('name')->get(['id', 'name']),
            'priorityAreas' => PriorityArea::with('subAreas')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Muestra el formulario de registro para evaluadores
     */
    public function createEvaluador()
    {
        return Inertia::render('Auth/RegisterEvaluator', [
            'institutions' => \App\Models\Institution::orderBy('name')->get(['id', 'name']),
            'priorityAreas' => PriorityArea::with('subAreas')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Maneja el registro del usuario
     */
    public function store(RegisterUserRequest $request)
    {
        Log::info('📝 RegisterController::store - Inicio del registro');

        $validated = $request->validated();
        $protectedEmails = ['admin@gmail.com', 'superadmin@gmail.com'];
        $requestEmail = strtolower(trim((string) $request->email));

        if (in_array($requestEmail, $protectedEmails, true)) {
            throw ValidationException::withMessages([
                'email' => ['Este correo esta reservado para la administracion del sistema y no puede usarse en registro publico.'],
            ]);
        }

        // Generar código de verificación de 6 dígitos
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Log::info('🔐 Código generado: ' . $verificationCode);

        // Verificar si el usuario ya existe por email o CURP
        $user = User::where('email', $request->email)
            ->orWhere('curp', strtoupper($request->curp))
            ->first();

        $roleType = $request->input('role_type', 'docente');
        $targetRole = $roleType === 'evaluador' ? 'Evaluador' : 'Docente';

        if ($user) {
            $existingUserEmail = strtolower(trim((string) $user->email));

            if (in_array($existingUserEmail, $protectedEmails, true)) {
                throw ValidationException::withMessages([
                    'email' => ['Esta cuenta pertenece a la administracion del sistema y no puede modificarse desde registro.'],
                ]);
            }

            // Seguridad: impedir re-registro del mismo rol para evitar sobrescrituras no autorizadas.
            if ($user->hasRole($targetRole)) {
                throw ValidationException::withMessages([
                    'curp' => ["Ya existe una cuenta registrada como {$targetRole} con este CURP."],
                ]);
            }

            Log::info('🔄 Usuario existente encontrado: ' . $user->email . '. Intentando fusionar roles.');

            // Actualizar created_at para que actúe como cuenta nueva (sin notificaciones previas)
            $user->created_at = now();

            // Actualizar datos básicos si es necesario (el nombre viene de RENAPO, así que debería ser igual)
            $user->update([
                'institution_id' => $request->institution_id,
                'priority_area_id' => $request->priority_area_id,
                'sub_area_id' => $request->sub_area_id,
                'email_verification_code' => $verificationCode,
                'email_verification_code_expires_at' => now()->addHours(24),
                // Opcional: Podríamos actualizar la contraseña si queremos permitir "sobrescribirla" en este paso, 
                // pero por seguridad es mejor mantener la existente o pedirle que use la misma.
            ]);

            // Eliminar la marca de verificación para forzar que valide el nuevo acceso
            $user->email_verified_at = null;
            $user->save();
        } else {
            // Crear el usuario si no existe
            $user = User::create([
                'curp' => strtoupper($request->curp),
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'institution_id' => $request->institution_id,
                'priority_area_id' => $request->priority_area_id,
                'sub_area_id' => $request->sub_area_id,
                'email_verification_code' => $verificationCode,
                'email_verification_code_expires_at' => now()->addHours(24),
            ]);
            Log::info('👤 Nuevo usuario creado: ' . $user->email);
        }

        // Asignar rol según el tipo de registro
        if ($roleType === 'evaluador') {
            $user->forceFill(['evaluator_status' => 'pending', 'evaluator_level' => null,
                'evaluator_institution_id' => null, 'evaluator_state_id' => null,
                'evaluator_authorized_by' => null, 'evaluator_authorized_at' => null])->save();
            if (!$user->hasRole('Evaluador')) {
                $user->assignRole('Evaluador');
                Log::info('👨‍🏫 Rol asignado: Evaluador');
            } else {
                Log::info('ℹ️ El usuario ya tenía el rol de Evaluador');
            }
        } else {
            if (!$user->hasRole('Docente')) {
                $user->assignRole('Docente');
                Log::info('👨‍🏫 Rol asignado: Docente');
            } else {
                Log::info('ℹ️ El usuario ya tenía el rol de Docente');
            }
        }

        // Enviar correo de verificación
        try {
            Mail::to($user->email)->queue(new VerificationCode($user, $verificationCode));
            Log::info('📧 Correo enviado exitosamente a: ' . $user->email);
            $mailStatus = 'Código de verificación enviado a tu correo electrónico.';
        } catch (\Exception $e) {
            Log::error('❌ Error al enviar correo de verificación: ' . $e->getMessage());
            $mailStatus = 'Registro exitoso. Revisa tu correo (puede tardar unos minutos).';
        }

        Log::info('🔄 Redirigiendo a verification.notice con email: ' . $user->email);

        // Iniciar sesión para mantener el contexto del usuario
        \Illuminate\Support\Facades\Auth::login($user);

        // Redirigir a verificación
        return redirect()->route('verification.notice')
            ->with('status', $mailStatus);
    }
}
