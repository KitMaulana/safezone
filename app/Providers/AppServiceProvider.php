<?php

namespace App\Providers;

use App\Models\Student;
use App\Models\Vehicle;
use App\Models\VehiclePermit;
use App\Policies\StudentPolicy;
use App\Policies\VehiclePermitPolicy;
use App\Policies\VehiclePolicy;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingService::class);
    }

    public function boot(): void
    {
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(VehiclePermit::class, VehiclePermitPolicy::class);

        // Deteksi N+1 di luar produksi (SPEC §Fase 11).
        // Di lokal cukup dicatat ke log agar halaman tidak ikut gagal; saat pengujian dilempar sebagai error.
        Model::preventLazyLoading(! app()->isProduction());
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        if (! app()->runningUnitTests()) {
            Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
                logger()->warning('Lazy loading terdeteksi: '.$model::class.'::'.$relation);
            });
        }

        if (app()->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
