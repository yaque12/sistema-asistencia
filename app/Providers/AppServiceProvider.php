<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Empleado;
use App\Models\RazonAusentismo;
use App\Models\ReporteDiario;
use App\Models\Reporte;
use App\Policies\UsuarioPolicy;
use App\Policies\EmpleadoPolicy;
use App\Policies\RazonAusentismoPolicy;
use App\Policies\ReporteDiarioPolicy;
use App\Policies\ReportePolicy;
use App\Policies\ConsultaDescargaPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UsuarioPolicy::class,
        Empleado::class => EmpleadoPolicy::class,
        RazonAusentismo::class => RazonAusentismoPolicy::class,
        ReporteDiario::class => ReporteDiarioPolicy::class,
        Reporte::class => ReportePolicy::class,
    ];

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
        // Registrar las policies
        $this->registerPolicies();
    }

    /**
     * Registrar las policies de la aplicación
     */
    private function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
