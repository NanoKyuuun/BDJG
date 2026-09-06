<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Domains\Users\Enums\UserStatus;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, function () {
            return new class implements LoginResponseContract {
                public function toResponse($request)
                {
                    return response()->json([
                        'two_factor' => false,
                        'message' => 'Authenticated successfully.',
                    ]);
                }
            };
        });

        $this->app->singleton(TwoFactorLoginResponseContract::class, function () {
            return new class implements TwoFactorLoginResponseContract {
                public function toResponse($request)
                {
                    return response()->json([
                        'two_factor' => true,
                        'message' => 'Two factor challenge required.',
                    ]);
                }
            };
        });

        $this->app->singleton(LogoutResponseContract::class, function () {
            return new class implements LogoutResponseContract {
                public function toResponse($request)
                {
                    return response()->json([
                        'message' => 'Logged out successfully.',
                    ]);
                }
            };
        });
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (
                $user
                && \Hash::check($request->password, $user->password)
                && $user->status === UserStatus::Active
            ) {
                return $user;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip())
            );
        });
    }
}
