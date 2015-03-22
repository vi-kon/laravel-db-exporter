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
        $this->commands('ViKon\DbExporter\Console\Commands\MigrateCommand');
        $this->commands('ViKon\DbExporter\Console\Commands\ModelsCommand');
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