<?php

namespace ViKon\DbExporter\Helper1;

/**
 * Trait DatabaseHelper
 *
 * @package ViKon\DbExporter\Helper1
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 */
trait DatabaseHelper
{
    /** @type string */
    protected $connectionName;

    /**
     * Get connection name
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connectionName;
    }

    /**
     * Set connection name
     *
     * @param string $connectionName
     *
     * @return $this
     */
    public function setConnectionName($connectionName)
    {
        $this->connectionName = $connectionName;

        return $this;
    }

    /**
     * Get table names
     *
     * @param string|null $connectionName
     *
     * @return string[]
     */
    public function getTableNames($connectionName = null)
    {
        /** @type \ViKon\DbExporter\Helper1\ContainerHelper|\ViKon\DbExporter\Helper1\DatabaseHelper|\ViKon\DbExporter\Helper1\SchemaHelper $this */

        $connectionName = $connectionName === null ? $connectionName : $this->getConnectionName();

        return $this->getSchema($connectionName)->listTableNames();
    }

    /**
     * Get table columns
     *
     * @param string      $tableName
     * @param string|null $connectionName
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    public function getTableColumns($tableName, $connectionName = null)
    {
        /** @type \ViKon\DbExporter\Helper1\ContainerHelper|\ViKon\DbExporter\Helper1\DatabaseHelper|\ViKon\DbExporter\Helper1\SchemaHelper $this */

        $connectionName = $connectionName === null ? $connectionName : $this->getConnectionName();

        return $this->getSchema($connectionName)->listTableColumns($tableName);
    }

    /**
     * Get table foreign keys
     *
     * @param string      $tableName
     * @param string|null $connectionName
     *
     * @return \Doctrine\DBAL\Schema\ForeignKeyConstraint[]
     */
    public function getTableForeignKeys($tableName, $connectionName = null)
    {
        /** @type \ViKon\DbExporter\Helper1\ContainerHelper|\ViKon\DbExporter\Helper1\DatabaseHelper|\ViKon\DbExporter\Helper1\SchemaHelper $this */

        $connectionName = $connectionName === null ? $connectionName : $this->getConnectionName();

        return $this->getSchema($connectionName)->listTableForeignKeys($tableName);
    }

    /**
     * Get table foreign table names
     *
     * @param string      $tableName
     * @param string|null $connectionName
     *
     * @return array
     */
    public function getForeignTableNames($tableName, $connectionName = null)
    {
        $foreignKeys = $this->getTableForeignKeys($tableName, $connectionName);

        $tableNames = [];

        foreach ($foreignKeys as $foreignKey) {
            $tableNames[] = $foreignKey->getForeignTableName();
        }

        return array_unique($tableNames);
    }

    /**
     * Get table indexes
     *
     * @param string      $tableName
     * @param string|null $connectionName
     *
     * @return \Doctrine\DBAL\Schema\Index[]
     */
    public function getTableIndexes($tableName, $connectionName = null)
    {
        /** @type \ViKon\DbExporter\Helper1\ContainerHelper|\ViKon\DbExporter\Helper1\DatabaseHelper|\ViKon\DbExporter\Helper1\SchemaHelper $this */

        $connectionName = $connectionName === null ? $connectionName : $this->getConnectionName();

        return $this->getSchema($connectionName)->listTableIndexes($tableName);
    }
}