<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Mail\VerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'curp' => ['required', 'string', 'size:18', Rule::unique(User::class)],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'institution_id' => ['required', 'exists:institutions,id'],
            'priority_area_id' => ['required', 'exists:priority_areas,id'],
            'sub_area_id' => ['required', 'exists:sub_areas,id'],
        ])->validate();

        // Generar código de verificación de 6 dígitos
        $verificationCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::create([
            'curp' => strtoupper($input['curp']),
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'institution_id' => $input['institution_id'],
            'priority_area_id' => $input['priority_area_id'],
            'sub_area_id' => $input['sub_area_id'],
            'email_verification_code' => $verificationCode,
            'email_verification_code_expires_at' => now()->addHours(24),
        ]);

        $user->assignRole('Docente');

        // Enviar correo de verificación
        Mail::to($user->email)->queue(new VerificationCode($user, $verificationCode));

        return $user;
    }
}
