<?php

namespace ViKon\DbExporter;


use Doctrine\DBAL\Types\Type;

class MigrationMetaData {
    use DatabaseHelper;

    const STATUS_INIT = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_RECURSIVE_FOREIGN_KEY = 2;
    const STATUS_MIGRATED = 3;

    /** @var string */
    protected $name;

    /** @var string */
    protected $connectionName;

    /** @var int */
    protected $status = self::STATUS_INIT;

    /**
     * @param string $name           table name
     * @param string $connectionName connection name
     * @param array  $options        array of options
     */
    public function __construct($name, $connectionName, array $options = []) {
        $this->name = $name;
        $this->connectionName = $connectionName;
        $this->tablePrefix = isset($options['tablePrefix']) ? $options['tablePrefix'] . '__' : '';
    }

    /**
     * Get table name
     *
     * @param bool $withPrefix table name with prefix or not
     *
     * @return string
     */
    public function getName($withPrefix = false) {
        return ($withPrefix ? $this->tablePrefix : '') . $this->name;
    }

    /**
     * Get all foreign keys foreign table names
     *
     * @return array
     */
    public function getForeignTableNames() {
        $foreignTableNames = [];
        $foreignKeys = $this->getTableForeignKeys($this->name, $this->connectionName);

        foreach ($foreignKeys as $foreignKey) {
            $foreignTableNames[] = $foreignKey->getForeignTableName();
        }

        return array_unique($foreignTableNames);
    }

    /**
     * Get table migration status
     *
     * @return int
     */
    public function getStatus() {
        return $this->status;
    }


    /**
     * Set migration status
     *
     * @param string $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }


    /**
     * Get table columns as blueprint code source
     *
     * @return string
     *
     * @throws \ViKon\DbExporter\DbExporterException
     */
    public function getColumnsSource() {
        $columns = $this->getTableColumns($this->name, $this->connectionName);

        if (!$columns) {
            return '';
        }

        $source = '';
        foreach ($columns as $column) {
            $attributes = [$this->serializeColumns($column->getName())];
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
                    var_dump($column->getPrecision());
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

        return $source;
    }

    /**
     * Get table indexes as blueprint code source
     *
     * @return string
     */
    public function getIndexesSource() {
        $indexes = $this->getTableIndexes($this->name, $this->connectionName);

        if (!$indexes) {
            return '';
        }

        $source = '';
        foreach ($indexes as $index) {
            $columns = $this->serializeColumns($index->getColumns());
            $name = $this->serializeIndexName($index->getName());

            if ($index->isPrimary()) {
                $source .= "\n" . '$table->primary(' . $columns . ', ' . $name . ');';
            } elseif ($index->isUnique()) {
                $source .= "\n" . '$table->unique(' . $columns . ', ' . $name . ');';
            } else {
                $source .= "\n" . '$table->index(' . $columns . ', ' . $name . ');';
            }
        }

        return $source . "\n";
    }

    /**
     * Get table foreign keys as blueprint code source
     *
     * @return string
     */
    public function getForeignKeysSource() {
        $foreignKeys = $this->getTableForeignKeys($this->name, $this->connectionName);

        if (!$foreignKeys) {
            return '';
        }

        $source = '';
        foreach ($foreignKeys as $foreignKey) {
            $name = $this->serializeIndexName($foreignKey->getName());
            $localColumns = $this->serializeColumns($foreignKey->getLocalColumns());
            $foreignColumns = $this->serializeColumns($foreignKey->getForeignColumns());
            $foreignTableName = '\'' . snake_case($this->tablePrefix . $foreignKey->getForeignTableName()) . '\'';

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

        return $source;
    }

    /**
     * Get table drop foreign keys as blueprint code source
     *
     * @return string
     */
    public function  getDropForeignKeysSource() {
        $foreignKeys = $this->getTableForeignKeys($this->name, $this->connectionName);
        if (!$foreignKeys) {
            return '';
        }
        $source = '';
        foreach ($foreignKeys as $foreignKey) {
            $name = $this->serializeIndexName($foreignKey->getName());
            $source .= "\n" . '$table->dropForeign(' . $name . ');';
        }

        return $source;
    }

    public function serializeIndexName($name) {
        if (strtolower($name) === 'primary') {
            $name = 'prim';
        }

        $name = str_replace(['ID'], ['Id'], $name);

        return '\'' . snake_case($name) . '\'';
    }
}