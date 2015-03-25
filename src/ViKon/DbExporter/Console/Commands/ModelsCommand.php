<?php

namespace ViKon\DbExporter\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use ViKon\DbExporter\Helper\DatabaseHelper;
use ViKon\DbExporter\Helper\DatabaseSchemaHelper;
use ViKon\DbExporter\Helper\TemplateHelper;
use ViKon\DbExporter\Meta\Model;

/**
 * Class ModelsCommand
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Console\Commands
 */
class ModelsCommand extends Command {
    use DatabaseSchemaHelper, DatabaseHelper, TemplateHelper;

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

        $models = $this->createModels();

        $this->makeRelations($models);

        foreach ($models as $model) {
            $model->writeOut($this->output, $this->option('overwrite'));
        }

        $this->info('Models successfully created');
    }

    /**
     * Build up meta data
     *
     * @return \ViKon\DbExporter\Meta\Model[]
     */
    protected function createModels() {
        $path = $this->option('path');
        $namespace = $this->option('namespace');
        $connectionName = $this->option('connection');

        $tableNames = $this->getDatabaseTableNames($connectionName);

        /** @var \ViKon\DbExporter\Meta\Model[] $models */
        $models = [];
        foreach ($tableNames as $tableName) {
            if ($this->skipTable($tableName)) {
                continue;
            }

            $models[$tableName] = new Model($connectionName, $tableName);
            $models[$tableName]->setPath($path);
            $models[$tableName]->setNamespace($namespace);
            $models[$tableName]->map();
        }

        return $models;
    }

    /**
     * Generate relation to each table
     *
     * @param \ViKon\DbExporter\Meta\Model[] $models
     */
    protected function makeRelations(array $models) {
        foreach ($models as $localModel) {
            $localTable = $localModel->getTable();
            $foreignKeys = $localTable->getTableForeignKeys();

            foreach ($foreignKeys as $foreignKey) {
                if (!isset($models[$foreignKey->getForeignTableName()])) {
                    continue;
                }

                $foreignModel = $models[$foreignKey->getForeignTableName()];
                $foreignTable = $foreignModel->getTable();

                $localColumns = $foreignKey->getLocalColumns();
                $foreignColumns = $foreignKey->getForeignColumns();

                $localIndex = $localTable->getTableIndexByColumnsName($localColumns);
                $foreignIndex = $foreignTable->getTableIndexByColumnsName($foreignColumns);

                $localTableClass = $localModel->getFullClass();
                $foreignTableClass = $foreignModel->getFullClass();

                $localColumn = reset($localColumns);
                $foreignColumn = reset($foreignColumns);

                // Guess foreign method name
                $localMethodName = str_replace(['_id'], '', snake_case($localColumn));
                $foreignMethodName = $localModel->getClass();

                // Try to find out connection type
                if ($localIndex !== false && $foreignIndex !== false && $localIndex->isUnique() && $foreignIndex->isUnique()) {
                    // One To One
                    $localTable->addHasOneRelation($foreignTableClass, $localMethodName, $foreignColumn, $localColumn);
                    $foreignTable->addBelongsToRelation($localTableClass, $foreignMethodName, $localColumn, $foreignColumn);
                } elseif ($foreignIndex !== false && $foreignIndex->isUnique()) {
                    // Many To One
                    $localTable->addBelongsToRelation($foreignTableClass, $localMethodName, $localColumn, $foreignColumn);
                    $foreignTable->addHasManyRelation($localTableClass, $foreignMethodName, $localColumn, $foreignColumn);
                } elseif ($localIndex !== false && $localIndex->isUnique()) {
                    // One To Many
                    $localTable->addHasManyRelation($foreignTableClass, $localMethodName, $foreignColumn, $localColumn);
                    $foreignTable->addBelongsToRelation($localTableClass, $foreignMethodName, $localColumn, $foreignColumn);
                } else {
                    // Many To Many without pivot table
                    $localTable->addHasManyRelation($foreignTableClass, $localMethodName, $foreignColumn, $localColumn);
                    $foreignTable->addHasManyRelation($localTableClass, $foreignMethodName, $localColumn, $foreignColumn);
                }
            }
        }
    }

    /**
     * Check if table is selected or not
     *
     * @param string $tableName table name
     *
     * @return bool
     */
    protected function skipTable($tableName) {
        // Check if select options is set and table name is not selected
        if (count($this->option('select')) > 0 && !in_array($tableName, $this->option('select'))) {
            return true;
        }

        // Check if table name is ignored
        if (in_array($tableName, $this->option('ignore'))) {
            return true;
        }

        return false;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return [
            ['prefix', null, InputOption::VALUE_OPTIONAL, 'Table prefix in models', config('db-exporter.prefix')],
            ['select', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Select specified database tables only', config('db-exporter.select')],
            ['ignore', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Ignore specified database tables', config('db-exporter.ignore')],
            ['connection', null, InputOption::VALUE_OPTIONAL, 'Specify connection name', config('db-exporter.connection')],
            ['overwrite', null, InputOption::VALUE_NONE, 'Overwrite exists models'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'Models base namespace', config('db-exporter.model.namespace')],
            ['path', null, InputOption::VALUE_OPTIONAL, 'Output destination path relative to project root', config('db-exporter.model.path')],
        ];
    }
}
