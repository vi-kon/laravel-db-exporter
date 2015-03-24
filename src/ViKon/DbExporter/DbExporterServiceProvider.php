<?php

namespace ViKon\DbExporter;

use Illuminate\Support\ServiceProvider;

/**
 * Class DbExporterServiceProvider
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter
 */
class DbExporterServiceProvider extends ServiceProvider {


    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->publishes([
            __DIR__ . '/../../config/config.php' => config_path('db-exporter.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->commands('ViKon\DbExporter\Console\Commands\MigrateCommand');
        $this->commands('ViKon\DbExporter\Console\Commands\ModelsCommand');

        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'db-exporter');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return [];
    }
}