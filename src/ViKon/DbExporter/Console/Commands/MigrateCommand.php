<?php

namespace ViKon\DbExporter\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use ViKon\DbExporter\DatabaseHelper;
use ViKon\DbExporter\MigrateTable;

class MigrateCommand extends Command {
    use DatabaseHelper;

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

        $tableNames = $this->getTableNames($this->option('database'));

        /** @var \ViKon\DbExporter\MigrateTable[] $tables */
        $tables = [];

        $tableOptions = [
            'tablePrefix' => $this->option('prefix'),
        ];

        foreach ($tableNames as $tableName) {
            if (in_array($tableName, $this->option('ignore'))) {
                continue;
            }

            $tables[$tableName] = new MigrateTable($tableName, $this->option('database'), $tableOptions);
        }

        $index = 0;
        foreach ($tables as $table) {
            $this->createMigrationForTable($index, $tables, $table);
        }
        foreach ($tables as $table) {
            if ($table->getStatus() === MigrateTable::STATUS_RECURSIVE_FOREIGN_KEY) {
                $this->makeAddForeignKeysToTableMigrationFile($index, $table);
                $index++;
            }
        }

        $this->info('Migration files created');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            ['prefix', null, InputOption::VALUE_OPTIONAL, 'Table prefix in migration files'],
            ['ignore', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Ignore specified database tables', ['migrations']],
            ['database', null, InputOption::VALUE_OPTIONAL, 'Specify database name'],
            ['overwrite', null, InputOption::VALUE_NONE, 'Overwrite exists migration files'],
        ];
    }

    /**
     * Export table to file
     *
     * @param int                              $index  file index
     * @param \ViKon\DbExporter\MigrateTable[] $tables available tables instances
     * @param \ViKon\DbExporter\MigrateTable   $table  actual table instance files
     */
    protected function createMigrationForTable(&$index, array $tables, MigrateTable $table) {
        if (in_array($table->getStatus(), [MigrateTable::STATUS_MIGRATED, MigrateTable::STATUS_RECURSIVE_FOREIGN_KEY])) {
            return;
        }

        $table->setStatus(MigrateTable::STATUS_IN_PROGRESS);

        foreach ($table->getForeignTableNames() as $tableName) {
            if ($table->getName() === $tableName) {
                continue;
            }

            // Check recursive foreign key
            if ($tables[$tableName]->getStatus() === MigrateTable::STATUS_IN_PROGRESS) {
                $table->setStatus(MigrateTable::STATUS_RECURSIVE_FOREIGN_KEY);
                continue;
            }

            $this->createMigrationForTable($index, $tables, $tables[$tableName]);
        }

        $this->makeCreateTableMigrationFile($index, $table);

        if ($table->getStatus() !== MigrateTable::STATUS_RECURSIVE_FOREIGN_KEY) {
            $table->setStatus(MigrateTable::STATUS_MIGRATED);
        }

        $index++;
    }

    /**
     * Make Create{...}Table migration file
     *
     * @param int                            $index
     * @param \ViKon\DbExporter\MigrateTable $table
     *
     * @throws \ViKon\DbExporter\DbExporterException
     */
    protected function makeCreateTableMigrationFile($index, MigrateTable $table) {
        $up = 'Schema::create(\'' . snake_case($table->getName(true)) . '\', function(Blueprint $table) {' . "\n";
        $up .= $table->getColumnsSource();
        $up .= $table->getIndexesSource();
        if ($table->getStatus() === MigrateTable::STATUS_IN_PROGRESS) {
            $up .= $table->getForeignKeysSource();
        }
        $up .= '});';

        $down = 'Schema::drop(\'' . snake_case($table->getName(true)) . '\');';

        $prefix = date('Y_m_d') . '_' . str_pad($index, 6, '0', STR_PAD_LEFT);
        $fileName = $prefix . '_create_' . snake_case($table->getName(true)) . '_table.php';

        $this->writeTemplate($fileName, 'Create' . studly_case($table->getName(true)) . 'Table', $up, $down);
    }

    /**
     * Make AddForeignKeysTo{...}Table migration file
     *
     * @param int                            $index
     * @param \ViKon\DbExporter\MigrateTable $table
     */
    protected function makeAddForeignKeysToTableMigrationFile($index, MigrateTable $table) {
        $up = 'Schema::table(\'' . snake_case($table->getName(true)) . '\', function(Blueprint $table) {' . "\n";
        $up .= $table->getForeignKeysSource();
        $up .= '});';

        $down = 'Schema::table(\'' . snake_case($table->getName(true)) . '\', function(Blueprint $table) {' . "\n";
        $down .= $table->getDropForeignKeysSource();
        $down .= '});';

        $prefix = date('Y_m_d') . '_' . str_pad($index, 6, '0', STR_PAD_LEFT);
        $fileName = $prefix . '_add_foreign_keys_to_' . snake_case($table->getName(true)) . '_table.php';

        $this->writeTemplate($fileName, 'AddForeignKeysTo' . studly_case($table->getName(true)) . 'Table', $up, $down);
    }

    /**
     * Write template to file
     *
     * @param string $fileName   migration file name
     * @param string $className  migration class name
     * @param string $upMethod   migration class up method content
     * @param string $downMethod migration class down method content
     */
    protected function writeTemplate($fileName, $className, $upMethod, $downMethod) {
        $path = base_path('database/migrations/' . $fileName);

        if ($this->option('overwrite') || !file_exists($path) && !is_dir($path)) {
            $variables = [
                '{{className}}' => $className,
                '{{up}}'        => $upMethod,
                '{{down}}'      => $downMethod,
            ];

            $template = file_get_contents(__DIR__ . '/../../../../stub/migration');
            $template = str_replace(array_keys($variables), array_values($variables), $template);

            file_put_contents($path, $template);
            $this->output->writeln('<info>File created:</info> ' . $fileName);
        } else {
            $this->output->writeln('<info>File already exists:</info> ' . $fileName . ' <comment>(Overwrite disabled)</comment>');
        }
    }

}
