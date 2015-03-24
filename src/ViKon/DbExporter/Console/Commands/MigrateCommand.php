<?php

namespace ViKon\DbExporter\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use ViKon\DbExporter\DatabaseHelper;
use ViKon\DbExporter\MigrationMetaData;
use ViKon\DbExporter\TemplateHelper;

class MigrateCommand extends Command {
    use DatabaseHelper, TemplateHelper;

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

        $tableOptions = [
            'tablePrefix' => $this->option('prefix'),
        ];

        $tableNames = $this->getTableNames($this->option('database'));

        /** @var \ViKon\DbExporter\MigrationMetaData[] $tables */
        $tables = [];
        foreach ($tableNames as $tableName) {
            if (in_array($tableName, $this->option('ignore'))) {
                continue;
            }

            $tables[$tableName] = new MigrationMetaData($tableName, $this->option('database'), $tableOptions);
        }

        $index = 0;
        foreach ($tables as $table) {
            $this->createMigrationForTable($index, $tables, $table);
        }
        foreach ($tables as $table) {
            if ($table->getStatus() === MigrationMetaData::STATUS_RECURSIVE_FOREIGN_KEY) {
                $this->makeAddForeignKeysToTableMigrationFile($index, $table);
                $index++;
            }
        }

        $this->info('Migration files successfully created');
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
            ['database', null, InputOption::VALUE_OPTIONAL, 'Specify database name', config('db-exporter.database')],
            ['overwrite', null, InputOption::VALUE_NONE, 'Overwrite exists migration files'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Output destination path relative to project root', config('db-exporter.migration.path')],
        ];
    }

    /**
     * Export table to file
     *
     * @param int                                   $index  file index
     * @param \ViKon\DbExporter\Meta\MigrationMetaData[] $tables available tables instances
     * @param \ViKon\DbExporter\Meta\MigrationMetaData   $table  actual table instance files
     */
    protected function createMigrationForTable(&$index, array $tables, MigrationMetaData $table) {
        if (in_array($table->getStatus(), [MigrationMetaData::STATUS_MIGRATED, MigrationMetaData::STATUS_RECURSIVE_FOREIGN_KEY])) {
            return;
        }

        $table->setStatus(MigrationMetaData::STATUS_IN_PROGRESS);

        // Check foreign keys
        foreach ($table->getForeignTableNames() as $tableName) {
            if ($table->getName() === $tableName) {
                continue;
            }

            // Check recursive foreign key
            if (isset($tables[$tableName]) && $tables[$tableName]->getStatus() === MigrationMetaData::STATUS_IN_PROGRESS) {
                $table->setStatus(MigrationMetaData::STATUS_RECURSIVE_FOREIGN_KEY);
                continue;
            }

            $this->createMigrationForTable($index, $tables, $tables[$tableName]);
        }

        $this->makeCreateTableMigrationFile($index, $table);

        if ($table->getStatus() !== MigrationMetaData::STATUS_RECURSIVE_FOREIGN_KEY) {
            $table->setStatus(MigrationMetaData::STATUS_MIGRATED);
        }

        $index++;
    }

    /**
     * Make Create{...}Table migration file
     *
     * @param int                              $index
     * @param \ViKon\DbExporter\MigrationMetaData $table
     *
     * @throws \ViKon\DbExporter\DbExporterException
     */
    protected function makeCreateTableMigrationFile($index, MigrationMetaData $table) {
        $up = 'Schema::create(\'' . snake_case($table->getName(true)) . '\', function(Blueprint $table) {' . "\n";
        $up .= $table->getColumnsSource();
        $up .= $table->getIndexesSource();
        if ($table->getStatus() === MigrationMetaData::STATUS_IN_PROGRESS) {
            $up .= $table->getForeignKeysSource();
        }
        $up .= '});';

        $down = 'Schema::drop(\'' . snake_case($table->getName(true)) . '\');';

        $prefix = date('Y_m_d') . '_' . str_pad($index, 6, '0', STR_PAD_LEFT);
        $fileName = $prefix . '_create_' . snake_case($table->getName(true)) . '_table.php';

        $this->writeMigrationFile($fileName, 'Create' . studly_case($table->getName(true)) . 'Table', $up, $down);
    }

    /**
     * Make AddForeignKeysTo{...}Table migration file
     *
     * @param int                              $index
     * @param \ViKon\DbExporter\MigrationMetaData $table
     */
    protected function makeAddForeignKeysToTableMigrationFile($index, MigrationMetaData $table) {
        $up = 'Schema::table(\'' . snake_case($table->getName(true)) . '\', function(Blueprint $table) {' . "\n";
        $up .= $table->getForeignKeysSource();
        $up .= '});';

        $down = 'Schema::table(\'' . snake_case($table->getName(true)) . '\', function(Blueprint $table) {' . "\n";
        $down .= $table->getDropForeignKeysSource();
        $down .= '});';

        $prefix = date('Y_m_d') . '_' . str_pad($index, 6, '0', STR_PAD_LEFT);
        $fileName = $prefix . '_add_foreign_keys_to_' . snake_case($table->getName(true)) . '_table.php';

        $this->writeMigrationFile($fileName, 'AddForeignKeysTo' . studly_case($table->getName(true)) . 'Table', $up, $down);
    }

    /**
     * Write migration to file
     *
     * @param string $fileName   migration file name
     * @param string $className  migration class name
     * @param string $upMethod   migration class up method content
     * @param string $downMethod migration class down method content
     */
    protected function writeMigrationFile($fileName, $className, $upMethod, $downMethod) {
        $this->writeToFileFromTemplate('migration', $fileName, [
            '{{className}}' => $className,
            '{{up}}'        => $upMethod,
            '{{down}}'      => $downMethod,
        ]);
    }

}
