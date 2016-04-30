<?php

namespace ViKon\DbExporter;

use Illuminate\Support\ServiceProvider;
use ViKon\DbExporter\Console\Commands\MigrateCommand;
use ViKon\DbExporter\Console\Commands\ModelsCommand;
use ViKon\DbExporter\Console\Commands\SeedCommand;

/**
 * Class DbExporterServiceProvider
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter
 */
class DbExporterServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
                             __DIR__ . '/../../config/config.php' => config_path('db-exporter.php'),
                         ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(MigrateCommand::class);
        $this->commands(ModelsCommand::class);
        $this->commands(SeedCommand::class);

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'vi-kon.db-exporter');

        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'vi-kon.db-exporter');

        $this->registerGenerator();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            Generator::class,
        ];
    }

    /**
     * Register generator
     *
     * @return void
     */
    protected function registerGenerator()
    {
        $this->app->singleton(Generator::class, function () {
            return new Generator($this->app);
        });
    }
}