<?php

namespace App\Providers;

use App\Models\User;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->configureTelescope();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        $this->configureModel();
        $this->configureFilament();




    }

    public function configureTelescope(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function configureModel(){
        Model::unguard();
        // Disable strict mode completely to allow lazy loading
        // We're not using any strict mode features to ensure compatibility
    }

    public function configureFilament(){
        FilamentColor::register([
            //indigo
            'indigo' => Color::Indigo,
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => '#FC0204',
            'success' => Color::Green,
            'warning' => Color::Amber,
            'cool-gray' => Color::Zinc,
        ]);

        // Register custom CSS
        FilamentAsset::register([
            Css::make('custom-styles', asset('css/custom.css')),
        ]);
    }

    public function configGates(){
        Gate::define('admin', function (User $user) {
            return $user->role === 'admin';
        });
        
        Gate::define('coordinator', function (User $user) {
            return $user->role === 'coordinator';
        });
    }
}
