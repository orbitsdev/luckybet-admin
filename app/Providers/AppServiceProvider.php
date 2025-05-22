<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;

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
        $this->configureScramble();


       
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
        Model::shouldBeStrict();
    }

    public function configureFilament(){
        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::Amber,
            'success' => Color::Green,
            'warning' => Color::Amber,
        ]);

        // Register custom CSS
        FilamentAsset::register([
            Css::make('custom-styles', asset('css/custom.css')),
        ]);
    }

    public function configureScramble(){
        Scramble::configure()
        ->withDocumentTransformers(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });
    }
}
