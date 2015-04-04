<?php

namespace ViKon\DbExporter\Helper;

use ViKon\DbExporter\DbExporterException;

/**
 * Class TableHelper
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Helper
 */
trait TableHelper {

    /** @var string|null default table name */
    protected $tableName = null;

    /**
     * Get index by columns name
     *
     * @param string|string[] $columnNames    columns name to match index columns
     * @param string|null     $tableName      table name
     * @param string|null     $connectionName database connection name (if null default connection is used)
     *
     * @return bool|\Doctrine\DBAL\Schema\Index FALSE if index not found, otherwise Index instance
     */
    public function getTableIndexByColumnsName($columnNames, $tableName = null, $connectionName = null) {
        if (!is_array($columnNames)) {
            $columnNames = [$columnNames];
        }

        $tableName = $this->validateTableName($tableName);


        $indexes = $this->getTableIndexes($tableName, $connectionName);
        foreach ($indexes as $index) {
            $indexColumns = $index->getColumns();
            // Check if columns are matched in index
            if (count($indexColumns) === count($columnNames) && count($indexColumns) === count(array_intersect($indexColumns, $columnNames))) {
                return $index;
            }
        }

        return false;
    }

    /**
     * Get table columns information
     *
     * @param string      $tableName      table name
     * @param string|null $connectionName database connection name (if null default connection is used)
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getTableColumns($tableName = null, $connectionName = null) {
        $tableName = $this->validateTableName($tableName);

        return $this->getSchema($connectionName)->listTableColumns($tableName);
    }

    /**
     * Get table foreign key information
     *
     * @param string      $tableName      table name
     * @param string|null $connectionName database connection name (if null default connection is used)
     *
     * @return \Doctrine\DBAL\Schema\ForeignKeyConstraint[]
     */
    public function getTableForeignKeys($tableName = null, $connectionName = null) {
        $tableName = $this->validateTableName($tableName);

        return $this->getSchema($connectionName)->listTableForeignKeys($tableName);
    }

    /**
     * Get tables foreign table names from foreign keys
     *
     * @param string      $tableName      table name
     * @param string|null $connectionName database connection name (if null default connection is used)
     *
     * @return string[]
     */
    public function getForeignTableNames($tableName = null, $connectionName = null) {
        $foreignKeys = $this->getTableForeignKeys($tableName, $connectionName);
        $tableNames = [];

        foreach ($foreignKeys as $foreignKey) {
            $tableNames[] = $foreignKey->getForeignTableName();
        }

        return array_unique($tableNames);
    }

    /**
     * Get table name
     *
     * @return null|string
     */
    public function getTableName() {
        return $this->tableName;
    }

    /**
     * Set table name
     *
     * @param string $tableName table name
     *
     * @return $this
     */
    protected function setTableName($tableName) {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get table indexes
     *
     * @param string|null $tableName      table name
     * @param string|null $connectionName database connection name (if null default connection is used)
     *
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    protected function getTableIndexes($tableName = null, $connectionName = null) {
        $tableName = $this->validateTableName($tableName);

        return $this->getSchema($connectionName)->listTableIndexes($tableName);
    }

    /**
     * Validate table name in parameter and in class property
     *
     * @param string|null $tableName table name
     *
     * @return null|string
     *
     * @throws \ViKon\DbExporter\DbExporterException
     */
    protected function validateTableName($tableName = null) {
        $tableName = $tableName === null ? $this->tableName : $tableName;

        if ($tableName === null) {
            throw new DbExporterException('Table name is null');
        }

        return $tableName;
    }

    /**
     * If array contains only one element return exported array value otherwise return serialized array of elements
     *
     * All elements will snake cased
     *
     * @param array|string $attributes column name
     * @param bool         $forceArray force array form even if array contains single value
     *
     * @return array|string
     */
    protected function serializeArrayToAttributes($attributes, $forceArray = false) {
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        foreach ($attributes as &$attribute) {
            $attribute = str_replace(['ID'], ['Id'], $attribute);
            $attribute = '\'' . snake_case($attribute) . '\'';
        }

        return $forceArray || count($attributes) > 1 ? '[' . implode(', ', $attributes) . ']' : reset($attributes);
    }

    /**
     * Serialize index name to "pass" as attribute
     *
     * @param string $indexName
     *
     * @return string
     */
    protected function serializeIndexNameToAttribute($indexName) {
        if (strtolower($indexName) === 'primary') {
            $indexName = 'prim';
        }
        $indexName = str_replace(['ID'], ['Id'], $indexName);

        return '\'' . snake_case($indexName) . '\'';
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


}