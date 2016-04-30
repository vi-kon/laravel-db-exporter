<?php

namespace ViKon\DbExporter\Helper1;

/**
 * Trait SchemaHelper
 *
 * @package ViKon\DbExporter\Helper1
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 *
 * @uses    \ViKon\DbExporter\Helper1\DatabaseHelper
 */
trait SchemaHelper
{
    /**
     * Get database schema for given connection
     *
     * @param string|null $connectionName
     *
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    public function getSchema($connectionName = null)
    {
        /** @var \ViKon\DbExporter\Helper1\ContainerHelper|\ViKon\DbExporter\Helper1\DatabaseHelper $this */

        $connectionName = $connectionName !== null ? $connectionName : $this->getConnectionName();

        $schema = $this->container->make('db')->connection($connectionName)->getDoctrineSchemaManager();

        // For MySQL
        $schema->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        // For Sqlite
        $schema->getDatabasePlatform()->registerDoctrineTypeMapping('long', 'integer');
        $schema->getDatabasePlatform()->registerDoctrineTypeMapping('bit', 'boolean');

        return $schema;
    }
}