<?php

namespace ViKon\DbExporter\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
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
class MigrateCommand extends Command {
    use DatabaseSchemaHelper, DatabaseHelper, TableHelper;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db-exporter:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create migration file from database tables';

    /**
     * Create a new command instance.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {
        $this->info('Creating migration files from database...');

        $migrations = $this->createMigrations();

        $index = 0;

        // Write migration files
        foreach ($migrations as $migration) {
            $this->processMigration($index, $migrations, $migration);
        }

        // Write separated foreign keys if migration has recursive foreign key
        foreach ($migrations as $migration) {
            if ($migration->getStatus() === Migration::STATUS_RECURSIVE_FOREIGN_KEY) {
                $migration->writeForeignKeysOut($index, $this->output, $this->option('force'));
                $index++;
            }
        }

        $this->info('Migration files successfully created');

        $this->call('clear-compiled');
        $this->call('optimize');
    }

    public function createMigrations() {
        $path = $this->option('path');
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
    protected function processMigration(&$index, array $migrations, Migration $migration) {
        if (in_array($migration->getStatus(), [Migration::STATUS_MIGRATED, Migration::STATUS_RECURSIVE_FOREIGN_KEY])) {
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

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            ['prefix', null, InputOption::VALUE_OPTIONAL, 'Table prefix in migration files', config('db-exporter.prefix')],
            ['select', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Select specified database tables only', config('db-exporter.select')],
            ['ignore', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Ignore specified database tables', config('db-exporter.ignore')],
            ['connection', null, InputOption::VALUE_OPTIONAL, 'Specify database connection name', config('db-exporter.database')],
            ['force', null, InputOption::VALUE_NONE, 'Overwrite existing migration files'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Output destination path relative to project root', config('db-exporter.migration.path')],
        ];
    }
}
