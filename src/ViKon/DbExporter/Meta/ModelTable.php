<?php

namespace ViKon\DbExporter\Meta;

use ViKon\DbExporter\Helper\DatabaseSchemaHelper;
use ViKon\DbExporter\Helper\TableHelper;
use ViKon\DbExporter\Meta\Relation\BelongsToRelation;
use ViKon\DbExporter\Meta\Relation\HasManyRelation;
use ViKon\DbExporter\Meta\Relation\HasOneRelation;

/**
 * Class ModelTable
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta
 */
class ModelTable
{
    use DatabaseSchemaHelper, TableHelper;

    /** @var \ViKon\DbExporter\Meta\Relation\AbstractRelation[] */
    protected $relations = [];

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
     * @param string $foreignModel      foreign model full class name
     * @param string $methodName        method name
     * @param string $foreignColumnName foreign column name
     * @param string $localColumnName   local column name
     */
    public function addHasOneRelation($foreignModel, $methodName, $foreignColumnName, $localColumnName)
    {
        $this->relations[] = new HasOneRelation($foreignModel, $methodName, $foreignColumnName, $localColumnName);
    }

    /**
     * @param string $foreignModel      foreign model full class name
     * @param string $methodName        method name
     * @param string $foreignColumnName foreign column name
     * @param string $localColumnName   local column name
     */
    public function addHasManyRelation($foreignModel, $methodName, $foreignColumnName, $localColumnName)
    {
        $this->relations[] = new HasManyRelation($foreignModel, $methodName, $foreignColumnName, $localColumnName);
    }

    /**
     * @param string $foreignModel      foreign model full class name
     * @param string $methodName        method name
     * @param string $foreignColumnName foreign column name
     * @param string $localColumnName   local column name
     */
    public function addBelongsToRelation($foreignModel, $methodName, $foreignColumnName, $localColumnName)
    {
        $this->relations[] = new BelongsToRelation($foreignModel, $methodName, $foreignColumnName, $localColumnName);
    }

    /**
     * Render relation methods
     *
     * @return string
     */
    public function renderRelationMethods()
    {
        $output = '';

        foreach ($this->relations as $relation) {
            $output .= $relation->renderMethod();
        }

        return $output;
    }
}