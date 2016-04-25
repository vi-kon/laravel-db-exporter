<?php

namespace ViKon\DbExporter\Meta\Relation;

use ViKon\DbExporter\Helper\TemplateHelper;

/**
 * Class AbstractRelation
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta\Relation
 */
abstract class AbstractRelation
{
    use TemplateHelper;

    /** @var string */
    protected $foreignClass;

    /** @var string */
    protected $methodName;

    /** @var string */
    protected $foreignColumnName;

    /** @var string */
    protected $localColumnName;

    /**
     * @param string $foreignClass      foreign model full class name
     * @param string $methodName        method name
     * @param string $foreignColumnName foreign column name
     * @param string $localColumnName   local column name
     */
    public function __construct($foreignClass, $methodName, $foreignColumnName, $localColumnName)
    {
        $this->foreignClass      = $foreignClass;
        $this->methodName        = $methodName;
        $this->foreignColumnName = $foreignColumnName;
        $this->localColumnName   = $localColumnName;
    }

    /**
     * Render method
     *
     * @return string
     */
    public function renderMethod()
    {
        return $this->renderTemplate($this->templateName, [
            'className'     => $this->foreignClass,
            'methodName'    => camel_case($this->methodName),
            'foreignColumn' => $this->foreignColumnName,
            'localColumn'   => $this->localColumnName,
        ]);
    }

}