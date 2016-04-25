<?php

namespace ViKon\DbExporter\Meta;

use ViKon\DbExporter\Helper\DatabaseSchemaHelper;
use ViKon\DbExporter\Helper\TableHelper;

/**
 * Class SeedTable
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta
 */
class SeedTable
{
    use DatabaseSchemaHelper, TableHelper;

    /**
     * @param string|null $connectionName connection name
     * @param string|null $tableName      table name
     */
    public function __construct($connectionName, $tableName)
    {
        $this->setConnectionName($connectionName);
        $this->setTableName($tableName);
    }

    /**
     * Get database data
     *
     * @return array
     */
    public function getData()
    {
        return \DB::connection($this->connectionName)->select('SELECT * FROM ' . $this->tableName);
    }
}