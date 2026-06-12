<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'apellidos' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'Cod_Carrera' => ['nullable', 'integer', 'exists:carreras,Cod_Carrera'],
            'Cod_Universidad' => ['nullable', 'integer', 'exists:universidades,Cod_Universidad'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }

        // Actualizar datos extendidos
        if ($user->usuarioCampusMarket) {
            $user->usuarioCampusMarket->update([
                'apellidos' => $input['apellidos'] ?? $user->usuarioCampusMarket->apellidos,
                'telefono' => $input['telefono'] ?? $user->usuarioCampusMarket->telefono,
                'Cod_Carrera' => $input['Cod_Carrera'] ?? $user->usuarioCampusMarket->Cod_Carrera,
                'Cod_Universidad' => $input['Cod_Universidad'] ?? $user->usuarioCampusMarket->Cod_Universidad,
            ]);
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
