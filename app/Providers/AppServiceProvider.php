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
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
