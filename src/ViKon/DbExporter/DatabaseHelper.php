<?php

namespace ViKon\DbExporter;

/**
 * Class DatabaseHelper
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter
 */
trait DatabaseHelper {

    /**
     * Get database tables
     *
     * @param string|null $name database connection name (if null default connection is used)
     *
     * @return string[] table names
     */
    public function getTableNames($name = null) {
        return $this->getSchema($name)->listTableNames();
    }

    /**
     * Get table indexes
     *
     * @param string      $table table name
     * @param string|null $name  database connection name (if null default connection is used)
     *
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    public function getTableIndexes($table, $name = null) {
        return $this->getSchema($name)->listTableIndexes($table);
    }

    /**
     * Get table columns information
     *
     * @param string      $table table name
     * @param string|null $name  database connection name (if null default connection is used)
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getTableColumns($table, $name = null) {
        return $this->getSchema($name)->listTableColumns($table);
    }

    /**
     * Get table foreign key information
     *
     * @param string      $table table name
     * @param string|null $name  database connection name (if null default connection is used)
     *
     * @return \Doctrine\DBAL\Schema\ForeignKeyConstraint[]
     */
    public function getTableForeignKeys($table, $name = null) {
        return $this->getSchema($name)->listTableForeignKeys($table);
    }


    /**
     * Get pdo instance for database connection
     *
     * @param string|null $name database connection name (if null default connection is used)
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected function getSchema($name = null) {
        /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $schema */
        $schema = app('db')->connection($name)->getDoctrineSchemaManager();
        $schema->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        return $schema;
    }

    /**
     * If array contains only one element return exported array value otherwise return serialized array of elements
     *
     * @param array|string $columns    column name
     * @param bool         $forceArray force array form even if array contains single value
     *
     * @return array|string
     */
    protected function serializeColumns($columns, $forceArray = false) {
        if (!is_array($columns)) {
            $columns = func_get_args();
        }

        foreach ($columns as &$column) {
            $column = str_replace(['ID'], ['Id'], $column);
            $column = snake_case($column);
            $column = var_export($column, true);
        }

        return $forceArray || count($columns) > 1 ? '[' . implode(', ', $columns) . ']' : reset($columns);
    }
}