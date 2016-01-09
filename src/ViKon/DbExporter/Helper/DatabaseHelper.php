<?php

namespace ViKon\DbExporter\Helper;

/**
 * Class DatabaseHelper
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Helper
 */
trait DatabaseHelper
{
    /**
     * Get database table names
     *
     * @param string|null $connectionName database connection name (if null default connection is used)
     *
     * @return string[] table names
     */
    public function getDatabaseTableNames($connectionName = null)
    {
        return $this->getSchema($connectionName)->listTableNames();
    }
}