<?php

namespace ViKon\DbExporter\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use ViKon\DbExporter\Helper\DatabaseHelper;
use ViKon\DbExporter\Helper\DatabaseSchemaHelper;
use ViKon\DbExporter\Helper\TableHelper;
use ViKon\DbExporter\Helper\TemplateHelper;
use ViKon\DbExporter\Meta\Seed;

/**
 * Class SeedCommand
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Console\Commands
 */
class SeedCommand extends Command {
    use DatabaseSchemaHelper, DatabaseHelper, TableHelper, TemplateHelper;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db-exporter:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create seed files from database tables';

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
        $this->info('Creating seeds from database...');

        $path = $this->option('path');
        $connectionName = $this->option('connection');

        $tableNames = $this->getDatabaseTableNames($connectionName);

        /** @var \ViKon\DbExporter\Meta\Seed[] $seeds */
        $seeds = [];
        $seederCall = [];
        foreach ($tableNames as $tableName) {
            if ($this->skipTable($tableName)) {
                continue;
            }

            $seeds[$tableName] = new Seed($connectionName, $tableName);
            $seeds[$tableName]->setPath($path);
            $seeds[$tableName]->writeSeedOut($this->output, $this->option('force'));
            $seederCall[] = '$this->call(\'' . $seeds[$tableName]->getClass() . '\');';
        }

        $this->writeToFileFromTemplate($path . '/Test.php', 'seederCall', $this->output, [
            'className' => 'Test',
            'call'      => implode("\n", $seederCall),
        ], $this->option('force'));

        $this->info('Seeds successfully created');
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
            ['force', null, InputOption::VALUE_NONE, 'Overwrite existing seeding files'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Output destination path relative to project root', config('db-exporter.seed.path')],
        ];
    }
}
