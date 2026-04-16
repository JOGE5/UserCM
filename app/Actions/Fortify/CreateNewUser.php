<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\UsuarioCampusMarket;
use App\Models\Categorias;
use App\Models\Carrera;
use App\Models\Roles;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'foto_base64' => $input['fotoBase64'] ?? null,
            'descriptor_facial' => isset($input['descriptorFacial']) ? $input['descriptorFacial'] : null,
        ]);

        // Enviar el correo de bienvenida al nuevo usuario
        \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\UserWelcomeMail($user));

        return $user;
    }
}
