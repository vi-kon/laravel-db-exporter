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
}