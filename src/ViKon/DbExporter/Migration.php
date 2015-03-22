<?php

namespace ViKon\DbExporter;

class Migration {
    use DatabaseHelper;

    /**
     * Create migrations from database
     *
     * @param string|null $connectionName database connection name (if null default connection is used)
     * @param array       $options        array of options
     *
     * Available options
     *
     * * ignoredTableNames - array of table names which are ignored (default is empty array)
     * * tablePrefix       - table prefix for generated migration files (default is null)
     * * overwrite         - overwrite existing migration files
     *
     * @throws \ViKon\DbExporter\DbExporterException
     */
    public function export($connectionName = null, array $options = []) {
        $tableNames = $this->getTableNames($connectionName);

        /** @var \ViKon\DbExporter\MigrateTable[] $tables */
        $tables = [];

        $tableOptions = [
            'tablePrefix' => isset($options['tablePrefix']) ? $options['tablePrefix'] : null,
        ];

        foreach ($tableNames as $tableName) {
            if (isset($options['ignoredTableNames']) && in_array($tableName, $options['ignoredTableNames'])) {
                continue;
            }

            $tables[$tableName] = new MigrateTable($tableName, $connectionName, $tableOptions);
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
    }

    /**
     * Export table to file
     *
     * @param int                              $index   file index
     * @param \ViKon\DbExporter\MigrateTable[] $tables  available tables instances
     * @param \ViKon\DbExporter\MigrateTable   $table   actual table instance files
     * @param array                            $options options
     */
    protected function createMigrationForTable(&$index, array $tables, MigrateTable $table, $options = []) {
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

            $this->createMigrationForTable($index, $tables, $tables[$tableName], $options);
        }

        $this->makeCreateTableMigrationFile($index, $table, $options);

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
     * @param array                          $options
     *
     * @throws \ViKon\DbExporter\DbExporterException
     */
    protected function makeCreateTableMigrationFile($index, MigrateTable $table, $options = []) {
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

        $overwrite = isset($options['overwrite']) ? $options['overwrite'] : false;

        $this->writeTemplate($fileName, 'Create' . studly_case($table->getName(true)) . 'Table', $up, $down, $overwrite);
    }

    /**
     * Make AddForeignKeysTo{...}Table migration file
     *
     * @param int                            $index
     * @param \ViKon\DbExporter\MigrateTable $table
     * @param array                          $options
     */
    protected function makeAddForeignKeysToTableMigrationFile($index, MigrateTable $table, $options = []) {
        $up = 'Schema::table(\'' . snake_case($table->getName(true)) . '\', function(Blueprint $table) {' . "\n";
        $up .= $table->getForeignKeysSource();
        $up .= '});';

        $down = 'Schema::table(\'' . snake_case($table->getName(true)) . '\', function(Blueprint $table) {' . "\n";
        $down .= $table->getDropForeignKeysSource();
        $down .= '});';

        $prefix = date('Y_m_d') . '_' . str_pad($index, 6, '0', STR_PAD_LEFT);
        $fileName = $prefix . '_add_foreign_keys_to_' . snake_case($table->getName(true)) . '_table.php';

        $overwrite = isset($options['overwrite']) ? $options['overwrite'] : false;

        $this->writeTemplate($fileName, 'AddForeignKeysTo' . studly_case($table->getName(true)) . 'Table', $up, $down, $overwrite);
    }

    /**
     * Write template to file
     *
     * @param string $fileName   migration file name
     * @param string $className  migration class name
     * @param string $upMethod   migration class up method content
     * @param string $downMethod migration class down method content
     * @param bool   $overwrite  overwrite migration file if exists
     *
     * @return bool|string
     */
    protected function writeTemplate($fileName, $className, $upMethod, $downMethod, $overwrite = false) {
        $path = base_path('database/migrations/' . $fileName);

        if ($overwrite || !file_exists($path) && !is_dir($path)) {
            $variables = [
                '{{className}}' => $className,
                '{{up}}'        => $upMethod,
                '{{down}}'      => $downMethod,
            ];

            $template = file_get_contents(__DIR__ . '/../../stub/migration');
            $template = str_replace(array_keys($variables), array_values($variables), $template);

            file_put_contents($path, $template);

            return $path;
        }

        return false;
    }


}