<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;
use App\Models\User;
use App\Models\UsuarioCampusMarket;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        Fortify::authenticateUsing(function (Request $request) {
            \Log::info('AuthenticateUsing: Begin login attempt for email: '.$request->email);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                \Log::info('AuthenticateUsing: User not found.');
                return null;
            }

            $usuarioCampusMarket = UsuarioCampusMarket::where('user_id', $user->id)->first();

            if (!$usuarioCampusMarket) {
                \Log::info('AuthenticateUsing: UsuarioCampusMarket record not found.');
                // If related record does not exist, deny login with message
                throw ValidationException::withMessages([
                    Fortify::username() => __('No se encontró el perfil del usuario. Por favor contacte al soporte.'),
                ]);
            }

            \Log::info('AuthenticateUsing: UsuarioCampusMarket Estado: ' . $usuarioCampusMarket->Estado);

            if ($usuarioCampusMarket->Estado !== 'Activo') {
                \Log::info('AuthenticateUsing: User inactive, login denied.');
                throw ValidationException::withMessages([
                    Fortify::username() => __('Usted está inactivo. Espere a que su situación cambie o contacte con el soporte del campus.'),
                ]);
            }

            if (Hash::check($request->password, $user->password)) {
                \Log::info('AuthenticateUsing: Password verified, login success.');
                return $user;
            }

            \Log::info('AuthenticateUsing: Password incorrect.');
            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
