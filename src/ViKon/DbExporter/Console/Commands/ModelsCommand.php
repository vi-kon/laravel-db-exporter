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
            foreach ($table->getForeignKeys() as $foreignKey) {
                if (!isset($tables[$foreignKey->getForeignTableName()])) {
                    continue;
                }

                $foreignTable = $tables[$foreignKey->getForeignTableName()];

                $localIndex = $table->getIndexByColumn($foreignKey->getLocalColumns());
                $foreignIndex = $table->getIndexByColumn($foreignKey->getForeignColumns());

                $localTableClass = $this->option('namespace') . '\\' . studly_case($table->getName(true));
                $foreignTableClass = $this->option('namespace') . '\\' . studly_case($foreignTable->getName(true));

                $localMethodName = studly_case($table->getName());
                $foreignMethodName = studly_case($foreignTable->getName());

                $localColumn = $foreignKey->getLocalColumns();
                $localColumn = reset($localColumn);
                $foreignColumn = $foreignKey->getForeignColumns();
                $foreignColumn = reset($foreignColumn);

                // Try to find out connection type
                if ($localIndex !== false && $foreignIndex !== false && $localIndex->isUnique() && $foreignIndex->isUnique()) {
                    // One To One
                    $table->addHasOneRelation($foreignTableClass, str_singular($foreignMethodName), $foreignColumn, $localColumn);
                    $foreignTable->addBelongsToRelation($localTableClass, str_singular($localMethodName), $localColumn, $foreignColumn);
                } elseif ($localIndex === false && $foreignIndex !== false && $foreignIndex->isUnique()) {
                    // Many To One
                    $table->addBelongsToRelation($foreignTableClass, str_singular($foreignMethodName), $foreignColumn, $localColumn);
                    $foreignTable->addHasManyRelation($localTableClass, str_plural($localMethodName), $localColumn, $foreignColumn);
                } elseif ($foreignIndex === false && $localIndex !== false && $localIndex->isUnique()) {
                    // One To Many
                    $table->addHasManyRelation($foreignTableClass, str_plural($foreignMethodName), $foreignColumn, $localColumn);
                    $foreignTable->addBelongsToRelation($localTableClass, str_singular($localMethodName), $localColumn, $foreignColumn);
                } else {
                    // Many To Many without pivot table
                }
            }
        }

        foreach ($tables as $table) {
            $foreignKeySource = $table->getRelationsSource();
            $this->writeModelFile(studly_case(str_singular($table->getName(true))), snake_case($table->getName(true)), $foreignKeySource);
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
     * @param string $className   class name (and file names)
     * @param string $tableName   model's table name
     * @param string $foreignKeys rendered foreign keys
     */
    protected function writeModelFile($className, $tableName, $foreignKeys) {
        $this->writeToFileFromTemplate('model', $className . '.php', [
            '{{namespace}}'   => $this->option('namespace'),
            '{{className}}'   => $className,
            '{{tableName}}'   => $tableName,
            '{{foreignKeys}}' => $foreignKeys,
        ]);
    }

}
