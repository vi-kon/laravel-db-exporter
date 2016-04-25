<?php

namespace ViKon\DbExporter\Generator;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Str;
use ViKon\DbExporter\Exception\DbExporterException;
use ViKon\DbExporter\Generator\Meta\Migration;
use ViKon\DbExporter\Helper1\ContainerHelper;
use ViKon\DbExporter\Helper1\DatabaseHelper;
use ViKon\DbExporter\Helper1\SchemaHelper;
use ViKon\DbExporter\Helper1\ViewHelper;

/**
 * Class MigrationGenerator
 *
 * @package ViKon\DbExporter\Generator
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 */
class MigrationGenerator
{
    use ContainerHelper;
    use DatabaseHelper;
    use SchemaHelper;
    use ViewHelper;

    /** @type string[] */
    protected $only = [];

    /** @type string[] */
    protected $except = [];

    /** @type int */
    protected $index;

    /** @type \ViKon\DbExporter\Generator\Meta\Migration[] */
    protected $migrations;

    /**
     * Select only specified tables
     *
     * @param array $tableNames
     *
     * @return $this
     */
    public function only(array $tableNames)
    {
        $this->only   = $tableNames;
        $this->except = [];

        return $this;
    }

    /**
     * Select every table except specified ones
     *
     * @param array $tableNames
     *
     * @return $this
     */
    public function except(array $tableNames)
    {
        $this->only   = [];
        $this->except = $tableNames;

        return $this;
    }

    /**
     * Generate migration files
     */
    public function generate()
    {
        $this->index = 0;

        $this->createMigration();

        foreach ($this->migrations as $tableName => $migration) {
            $this->processMigration($migration);
        }
    }

    /**
     * Create migration files
     */
    protected function createMigration()
    {
        $this->migrations = [];

        $tableNames = $this->getTableNames();

        foreach ($tableNames as $tableName) {
            if ($this->shouldSkipTable($tableName)) {
                continue;
            }

            $this->migrations[$tableName] = new Migration($tableName);
        }
    }

    /**
     * Check if table should skip or not
     *
     * @param string $tableName
     *
     * @return bool
     */
    protected function shouldSkipTable($tableName)
    {
        if ($this->only !== [] && in_array($tableName, $this->only, true)) {
            return true;
        }

        if ($this->except !== [] && !in_array($tableName, $this->except, true)) {
            return true;
        }

        return $this->only === [] && $this->except === [];
    }

    protected function processMigration(Migration $migration)
    {
        if (in_array($migration->getStatus(), [Migration::STATUS_MIGRATED, Migration::STATUS_RECURSIVE_FOREIGN_KEY], true)) {
            return;
        }

        $tableName = $migration->getTableName();

        $foreignTableNames = $this->getForeignTableNames($tableName);
        foreach ($foreignTableNames as $foreignTableName) {
            // Skip tables with same name
            if ($tableName === $foreignTableName) {
                continue;
            }

            // Set migration to write out foreign keys separately
            if (isset($this->migrations[$foreignTableName])) {
                $foreignMigration = $this->migrations[$foreignTableName];

                if ($foreignMigration->getStatus() === Migration::STATUS_IN_PROGRESS) {
                    $foreignMigration->setStatus(Migration::STATUS_RECURSIVE_FOREIGN_KEY);
                    continue;
                }
            }

            // Process foreign table's migration
            $this->processMigration($this->migrations[$foreignTableName]);
        }

        $class    = Str::studly('create_' . Str::snake($tableName) . '_table');
        $fileName = date('Y_m_d_') . str_pad($this->index, 6, '0', STR_PAD_LEFT) . '_' . Str::snake($class) . '.php';

        $content = $this->renderTable($tableName, $class, $migration);

        // Write out migration file
        file_put_contents($fileName, $content);

        if ($migration->getStatus() !== Migration::STATUS_RECURSIVE_FOREIGN_KEY) {
            $migration->setStatus(Migration::STATUS_MIGRATED);
        }

        $this->index++;
    }

    /**
     * Render table for migration
     *
     * @param string                                     $tableName
     * @param string                                     $class
     * @param \ViKon\DbExporter\Generator\Meta\Migration $migration
     *
     * @return string
     */
    protected function renderTable($tableName, $class, Migration $migration)
    {
        return $this->render('vi-kon.db-exporter::migration/create-table', [
            'className'   => $class,
            'tableName'   => $tableName,
            'columns'     => $this->renderColumns($tableName),
            'indexes'     => $this->renderIndexes($tableName),
            'foreignKeys' => $migration->getStatus() === Migration::STATUS_IN_PROGRESS
                ? $this->renderForeignKeys($tableName)
                : '',
        ]);
    }

    /**
     * Render columns for template
     *
     * @param string $tableName
     *
     * @return string
     *
     * @throws \ViKon\DbExporter\Exception\DbExporterException
     */
    protected function renderColumns($tableName)
    {
        $columns = $this->getTableColumns($tableName);

        if ($columns === []) {
            return '';
        }

        $output = '';

        foreach ($columns as $column) {
            list($method, $parameters) = $this->parseColumnType($column);

            array_unshift($parameters, $this->renderColumnNames($column->getName()));

            $output .= '$table->' . $method . '(' . implode(', ', $parameters) . ')';
            if ($column->getUnsigned()) {
                $output .= "\n" . '      ->unsigned()';
            }
            if (!$column->getNotnull()) {
                $output .= "\n" . '      ->nullable()';
            }
            if ($column->getDefault() || ($column->getDefault() === null && !$column->getNotnull())) {
                $output .= "\n" . '      ->default(' . var_export($column->getDefault(), true) . ')';
            }
            $output .= ';' . "\n";
        }

        return $output;
    }

    /**
     * Render indexes for template
     *
     * @param string $tableName
     *
     * @return string
     */
    protected function renderIndexes($tableName)
    {
        $indexes = $this->getTableIndexes($tableName);

        if ($indexes === []) {
            return '';
        }

        $output = '';

        foreach ($indexes as $index) {
            $parameters = [
                $this->renderColumnNames($index->getColumns()),
                $this->renderIndexNames($index->getName()),
            ];
            $method     = $this->parseIndexType($index);

            $output .= "\n" . '$table->' . $method . '(' . implode(', ', $parameters) . ');';
        }

        return $output;
    }

    protected function renderForeignKeys($tableName)
    {
        $foreignKeys = $this->getTableForeignKeys($tableName);

        if ($foreignKeys === []) {
            return '';
        }

        $output = '';
        foreach ($foreignKeys as $foreignKey) {
            $name             = $this->renderIndexNames($foreignKey->getName());
            $localColumns     = $this->renderColumnNames($foreignKey->getLocalColumns());
            $foreignColumns   = $this->renderColumnNames($foreignKey->getForeignColumns());
            $foreignTableName = '\'' . Str::snake($foreignKey->getForeignTableName()) . '\'';

            $output .= "\n" . '$table->foreign(' . $localColumns . ', ' . $name . ')';
            $output .= "\n" . '      ->references(' . $foreignColumns . ')';
            $output .= "\n" . '      ->on(' . $foreignTableName . ')';

            // Add constraints
            if (($onDelete = $foreignKey->getOption('onUpdate')) !== null) {
                $onDelete = var_export($onDelete, true);
                $output .= "\n" . '      ->onUpdate(' . $onDelete . ')';
            }
            if (($onUpdate = $foreignKey->getOption('onDelete')) !== null) {
                $onUpdate = var_export($onUpdate, true);
                $output .= "\n" . '      ->onUpdate(' . $onUpdate . ')';
            }
            $output .= ';' . "\n";
        }

        // Remove last line break
        return trim($output);
    }

    /**
     * Parse column for type and additional parameters
     *
     * @param \Doctrine\DBAL\Schema\Column $column
     *
     * @return array
     *
     * @throws \ViKon\DbExporter\Exception\DbExporterException
     */
    protected function parseColumnType(Column $column)
    {
        $type = $column->getType()->getName();

        switch ($type) {
            case Type::INTEGER:
                return ['integer', []];

            case Type::SMALLINT:
                return ['smallInteger', []];

            case Type::BIGINT:
                return ['bigInteger', []];

            case Type::STRING:
                return ['string', [$column->getLength()]];

            case Type::GUID:
            case Type::TEXT:
                return ['text', []];

            case Type::FLOAT:
                return ['float', []];

            case Type::DECIMAL:
                return ['decimal', [$column->getPrecision(), $column->getScale()]];

            case Type::BOOLEAN:
                return ['boolean', []];

            case Type::DATETIME:
            case Type::DATETIMETZ:
                return ['dateTime', []];

            case Type::DATE:
                return ['date', []];

            case Type::TIME:
                return ['time', []];

            case Type::BINARY:
            case Type::BLOB:
                return ['binary', []];

            case Type::JSON_ARRAY:
                return ['json', []];

            default:
                throw new DbExporterException('Database column type is not supported:' . $type);
        }
    }

    /**
     * Parse index for type
     *
     * @param \Doctrine\DBAL\Schema\Index $index
     *
     * @return string
     */
    protected function parseIndexType(Index $index)
    {
        if ($index->isPrimary()) {
            return 'primary';
        }

        if ($index->isUnique()) {
            return 'unique';
        }

        return 'index';
    }

    /**
     * Render column names for template
     *
     * @param string|string[] $columnNames
     *
     * @return string
     */
    protected function renderColumnNames($columnNames)
    {
        $columnNames = (array)$columnNames;

        foreach ($columnNames as &$columnName) {
            $columnName = '\'' . Str::snake($columnName) . '\'';
        }
        unset($columnName);

        // Base on parameter count return array or single result
        return count($columnNames) > 1
            ? '[' . implode(', ', $columnNames) . ']'
            : reset($columnNames);
    }

    /**
     * Render index names for template
     *
     * @param string|string[] $indexNames
     *
     * @return string
     */
    protected function renderIndexNames($indexNames)
    {
        $indexNames = (array)$indexNames;

        foreach ($indexNames as &$indexName) {
            $indexName = '\'' . Str::snake($indexName) . '\'';
        }
        unset($indexName);

        // Base on parameter count return array or single result
        return count($indexNames) > 1
            ? '[' . implode(', ', $indexNames) . ']'
            : reset($indexNames);
    }
}