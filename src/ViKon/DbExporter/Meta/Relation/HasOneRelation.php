<?php

namespace ViKon\DbExporter\Meta\Relation;

/**
 * Class HasOneRelation
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta\Relation
 */
class HasOneRelation extends AbstractRelation {
    /** @var string template name in stub directory */
    protected $templateName = 'methodHasOne';

    /**
     * @inheritdoc
     */
    public function __construct($foreignClass, $methodName, $foreignColumnName, $localColumnName) {
        parent::__construct($foreignClass, str_singular($methodName), $foreignColumnName, $localColumnName);
    }
}