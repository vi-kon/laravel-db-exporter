<?php

namespace ViKon\DbExporter\Meta;

use Doctrine\DBAL\Types\Type;
use ViKon\DbExporter\DbExporterException;
use ViKon\DbExporter\Helper\DatabaseSchemaHelper;
use ViKon\DbExporter\Helper\TableHelper;

/**
 * Class MigrationTable
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta
 */
class MigrationTable {
    use DatabaseSchemaHelper, TableHelper;

    /**
     * @param string|null $connectionName connection name
     * @param string|null $tableName      table name
     */
    public function __construct($connectionName, $tableName) {
        $this->setConnectionName($connectionName);
        $this->setTableName($tableName);
    }

    /**
     * Render table columns create schema builder methods
     *
     * @return string
     *
     * @throws \ViKon\DbExporter\DbExporterException
     */
    public function renderCreateColumns() {
        $columns = $this->getTableColumns();

        if (!$columns) {
            return '';
        }

        $source = '';

        foreach ($columns as $column) {
            $attributes = [$this->serializeArrayToAttributes($column->getName())];
            $type = $column->getType()->getName();

            switch ($type) {
                case Type::INTEGER:
                    $method = 'integer';
                    break;
                case Type::SMALLINT:
                    $method = 'smallInteger';
                    break;
                case Type::BIGINT:
                    $method = 'bigInteger';
                    break;
                case Type::STRING:
                    $attributes[] = $column->getLength();
                    $method = 'string';
                    break;
                case Type::GUID:
                case Type::TEXT:
                    $method = 'text';
                    break;
                case Type::FLOAT:
                    $method = 'float';
                    break;
                case Type::DECIMAL:
                    $attributes[] = $column->getPrecision();
                    $attributes[] = $column->getScale();
                    $method = 'decimal';
                    break;
                case Type::BOOLEAN:
                    $method = 'boolean';
                    break;
                case Type::DATETIME:
                case Type::DATETIMETZ:
                    $method = 'dateTime';
                    break;
                case Type::DATE:
                    $method = 'date';
                    break;
                case Type::TIME:
                    $method = 'time';
                    break;
                case Type::BINARY:
                case Type::BLOB:
                    $method = 'binary';
                    break;
                case Type::JSON_ARRAY:
                    $method = 'json';
                    break;
                default:
                    throw new DbExporterException('Not supported database column type' . $type);
                    break;
            }

            $source .= '$table->' . $method . '(' . implode(', ', $attributes) . ')';
            if ($column->getUnsigned()) {
                $source .= "\n" . '      ->unsigned()';
            }
            if (!$column->getNotnull()) {
                $source .= "\n" . '      ->nullable()';
            }
            if ($column->getDefault() || $column->getDefault() === null && !$column->getNotnull()) {
                $source .= "\n" . '      ->default(' . var_export($column->getDefault(), true) . ')';
            }
            $source .= ';' . "\n";
        }

        return trim($source);
    }

    /**
     * Render table indexes create schema builder methods
     *
     * @return string
     */
    public function renderCreateIndexes() {
        $indexes = $this->getTableIndexes();
        if (!$indexes) {
            return '';
        }
        $source = '';
        foreach ($indexes as $index) {
            $columns = $this->serializeArrayToAttributes($index->getColumns());
            $name = $this->serializeIndexNameToAttribute($index->getName());
            if ($index->isPrimary()) {
                $source .= "\n" . '$table->primary(' . $columns . ', ' . $name . ');';
            } elseif ($index->isUnique()) {
                $source .= "\n" . '$table->unique(' . $columns . ', ' . $name . ');';
            } else {
                $source .= "\n" . '$table->index(' . $columns . ', ' . $name . ');';
            }
        }

        return trim($source);
    }

    /**
     * Render table foreign keys create schema builder methods
     *
     * @return string
     */
    public function renderCreateForeignKeys() {
        $foreignKeys = $this->getTableForeignKeys();

        if (!$foreignKeys) {
            return '';
        }

        $source = '';
        foreach ($foreignKeys as $foreignKey) {
            $name = $this->serializeIndexNameToAttribute($foreignKey->getName());
            $localColumns = $this->serializeArrayToAttributes($foreignKey->getLocalColumns());
            $foreignColumns = $this->serializeArrayToAttributes($foreignKey->getForeignColumns());
            $foreignTableName = '\'' . snake_case($foreignKey->getForeignTableName()) . '\'';
            $source .= "\n" . '$table->foreign(' . $localColumns . ', ' . $name . ')';
            $source .= "\n" . '      ->references(' . $foreignColumns . ')';
            $source .= "\n" . '      ->on(' . $foreignTableName . ')';
            if (($onDelete = $foreignKey->getOption('onUpdate')) !== null) {
                $onDelete = var_export($onDelete, true);
                $source .= "\n" . '      ->onUpdate(' . $onDelete . ')';
            }
            if (($onUpdate = $foreignKey->getOption('onDelete')) !== null) {
                $onUpdate = var_export($onUpdate, true);
                $source .= "\n" . '      ->onUpdate(' . $onUpdate . ')';
            }
            $source .= ';' . "\n";
        }

        return trim($source);
    }

    /**
     * Render table foreign keys drop schema builder methods
     *
     * @return string
     */
    public function renderDropForeignKeys() {
        $foreignKeys = $this->getTableForeignKeys();

        if (!$foreignKeys) {
            return '';
        }

        $source = '';
        foreach ($foreignKeys as $foreignKey) {
            $name = $this->serializeIndexNameToAttribute($foreignKey->getName());
            $source .= "\n" . '$table->dropForeign(' . $name . ');';
        }

        return trim($source);
    }
}