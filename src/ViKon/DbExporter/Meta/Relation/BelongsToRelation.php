<?php

namespace ViKon\DbExporter\Meta\Relation;

/**
 * Class BelongsToRelation
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta\Relation
 */
class BelongsToRelation extends AbstractRelation {
    /** @var string template name in stub directory */
    protected $templateName = 'methodBelongsTo';

    /**
     * @inheritdoc
     */
    public function __construct($foreignClass, $methodName, $foreignColumnName, $localColumnName) {
        parent::__construct($foreignClass, str_singular($methodName), $foreignColumnName, $localColumnName);
    }
}