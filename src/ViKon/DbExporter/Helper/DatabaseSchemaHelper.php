<?php

namespace ViKon\DbExporter\Helper;

/**
 * Class DatabaseSchemaHelper
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Helper
 */
trait DatabaseSchemaHelper {
    /** @var string|null trait default connection name */
    protected $connectionName = null;

    /**
     * Set connection name
     *
     * @param string|null $connectionName default connection name
     *
     * @return $this
     */
    protected function setConnectionName($connectionName) {
        $this->connectionName = $connectionName;

        return $this;
    }

    /**
     * Get pdo instance for database connection
     *
     * @param string|null $connectionName database connection name (if null default connection is used)
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected function getSchema($connectionName = null) {
        $connectionName = $connectionName === null ? $this->connectionName : $connectionName;

        /** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $schema */
        $schema = app('db')->connection($connectionName)->getDoctrineSchemaManager();
        $schema->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

        return $schema;
    }
}