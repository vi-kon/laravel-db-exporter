<?php

namespace ViKon\DbExporter\Meta\Relation;

/**
 * Class HasManyRelation
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta\Relation
 */
class HasManyRelation extends AbstractRelation {
    /** @var string template name in stub directory */
    protected $templateName = 'methodHasMany';

    /**
     * @inheritdoc
     */
    public function __construct($foreignClass, $methodName, $foreignColumnName, $localColumnName) {
        parent::__construct($foreignClass, str_plural($methodName), $foreignColumnName, $localColumnName);
    }
}