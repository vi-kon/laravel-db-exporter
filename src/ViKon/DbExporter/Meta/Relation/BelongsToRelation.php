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
}