<?php

namespace ViKon\DbExporter\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use ViKon\DbExporter\Generator;
use ViKon\DbExporter\Helper\DatabaseHelper;
use ViKon\DbExporter\Helper\DatabaseSchemaHelper;
use ViKon\DbExporter\Helper\TableHelper;
use ViKon\DbExporter\Meta\Migration;

/**
 * Class MigrateCommand
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Console\Commands
 */
class MigrateCommand extends Command
{
    use DatabaseSchemaHelper, DatabaseHelper, TableHelper;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->signature   = 'vi-kon:db-exporter:migrate'
                             . ' {--prefix=     : Table prefix for migrations}'
                             . ' {--connection= : Use specified connection for database migrations}'
                             . ' {--only*=      : Select only specified tables}'
                             . ' {--except*=    : Select every table except specified ones}'
                             . ' {--force       : Overwrite existing files}'
                             . ' {--path=       : Output path for migrations (Relative to project root)}';
        $this->description = 'Create migration file from database tables';

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Creating migration files from database...');

        $migration = $this->laravel->make(Generator::class)->migration();

        if ($this->input->hasOption('except')) {
            $migration->except($this->option('except'));
        }
        if ($this->input->hasOption('only')) {
            $migration->only($this->option('only'));
        }

        $migration->generate(base_path($this->option('path') === null ? 'database/migrations' : $this->option('path')), $this->option('force'));

        $this->info('Migration files successfully created');
    }

    /**
     * Create migration metadata objects
     *
     * @return \ViKon\DbExporter\Meta\Migration[]
     */
    public function createMigrations()
    {
        $path           = $this->option('path');
        $connectionName = $this->option('connection');

        $tableNames = $this->getDatabaseTableNames($connectionName);

        /** @var \ViKon\DbExporter\Meta\Migration[] $migrations */
        $migrations = [];
        foreach ($tableNames as $tableName) {
            if ($this->skipTable($tableName)) {
                continue;
            }

            $migrations[$tableName] = new Migration($connectionName, $tableName);
            $migrations[$tableName]->setPath($path);
        }

        return $migrations;
    }

    /**
     * Export table to file
     *
     * @param int                                $index      file index
     * @param \ViKon\DbExporter\Meta\Migration[] $migrations available tables instances
     * @param \ViKon\DbExporter\Meta\Migration   $migration  actual table instance files
     */
    protected function processMigration(&$index, array $migrations, Migration $migration)
    {
        if (in_array($migration->getStatus(), [Migration::STATUS_MIGRATED, Migration::STATUS_RECURSIVE_FOREIGN_KEY], true)) {
            return;
        }
        $migration->setStatus(Migration::STATUS_IN_PROGRESS);

        // Check foreign keys
        $tableNames = $migration->getTable()->getForeignTableNames();
        foreach ($tableNames as $tableName) {
            if ($migration->getTable()->getTableName() === $tableName) {
                continue;
            }

            // Check recursive foreign key
            if (isset($migrations[$tableName]) && $migrations[$tableName]->getStatus() === Migration::STATUS_IN_PROGRESS) {
                $migration->setStatus(Migration::STATUS_RECURSIVE_FOREIGN_KEY);
                continue;
            }

            $this->processMigration($index, $migrations, $migrations[$tableName]);
        }

        $migration->writeTableOut($index, $this->output, $this->option('force'));

        if ($migration->getStatus() !== Migration::STATUS_RECURSIVE_FOREIGN_KEY) {
            $migration->setStatus(Migration::STATUS_MIGRATED);
        }

        $index++;
    }
}
