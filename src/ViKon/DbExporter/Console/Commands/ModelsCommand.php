<?php

namespace ViKon\DbExporter\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use ViKon\DbExporter\DatabaseHelper;
use ViKon\DbExporter\ModelTable;
use ViKon\DbExporter\TemplateHelper;

class ModelsCommand extends Command {
    use DatabaseHelper, TemplateHelper;

    const TYPE_ONE_TO_ONE = 1;
    const TYPE_MANY_TO_ONE = 2;
    const TYPE_ONE_TO_MANY = 3;
    const TYPE_MANY_TO_MANY = 4;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db-exporter:models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create models from database tables';

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
        $this->info('Creating models from database...');

        $tableOptions = [
            'tablePrefix' => $this->option('prefix'),
        ];

        $tableNames = $this->getTableNames($this->option('database'));

        /** @var \ViKon\DbExporter\ModelTable[] $tables */
        $tables = [];
        foreach ($tableNames as $tableName) {
            if (in_array($tableName, $this->option('ignore'))) {
                continue;
            }

            $tables[$tableName] = new ModelTable($tableName, $this->option('database'), $tableOptions);
        }

        foreach ($tables as $table) {
            $this->writeModelFile(studly_case(str_singular($table->getName(true))), snake_case($table->getName(true)));
        }

        $this->info('Models successfully created');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            ['prefix', null, InputOption::VALUE_OPTIONAL, 'Table prefix in models'],
            ['ignore', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Ignore specified database tables', ['migrations']],
            ['database', null, InputOption::VALUE_OPTIONAL, 'Specify database name'],
            ['overwrite', null, InputOption::VALUE_NONE, 'Overwrite exists models'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'Models namespace', 'App\Models'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Output destination path relative to project root', 'app/Models'],
        ];
    }

    /**
     * Write model to file
     *
     * @param string $className class name (and file names)
     * @param string $tableName model's table name
     */
    protected function writeModelFile($className, $tableName) {
        $this->writeToFileFromTemplate('model', $className . '.php', [
            '{{namespace}}'   => $this->option('namespace'),
            '{{className}}'   => $className,
            '{{tableName}}'   => $tableName,
            '{{foreignKeys}}' => '',
        ]);
    }

}
