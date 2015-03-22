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
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('db-exporter.migration', 'ViKon\DbExporter\Migration');

        $this->commands('ViKon\DbExporter\Console\Commands\MigrateCommand');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return ['db-exporter.migration'];
    }
}