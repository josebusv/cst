<?php

namespace App\Providers;

use App\Models\Accesorio;
use App\Models\ClasificacionBiomedica;
use App\Models\Consumible;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Municipio;
use App\Models\Sede;
use App\Models\TipoEquipo;
use App\Models\User;
use App\Observers\CatalogoObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
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
        // Limiter general de la API (antes en RouteServiceProvider).
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Login: limite por cuenta (email+IP) y un tope por IP.
        RateLimiter::for('login', function (Request $request) {
            $email = mb_strtolower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by('login:' . $email . '|' . $request->ip()),
                Limit::perMinute(20)->by('login-ip:' . $request->ip()),
            ];
        });

        // Politica de contraseñas: minimo 8, letras y numeros. En produccion
        // ademas se verifica contra filtraciones conocidas (HIBP).
        Password::defaults(function () {
            $rule = Password::min(8)->letters()->numbers();

            return app()->isProduction() ? $rule->uncompromised() : $rule;
        });

        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Super-Admin')) {
                return true; // Otorga acceso completo si el rol es 'Super-Admin'
            }
        });

        foreach ([
            Empresa::class,
            Sede::class,
            User::class,
            Role::class,
            Permission::class,
            Departamento::class,
            Municipio::class,
            Accesorio::class,
            Consumible::class,
            TipoEquipo::class,
            ClasificacionBiomedica::class,
        ] as $model) {
            $model::observe(CatalogoObserver::class);
        }
    }
}
