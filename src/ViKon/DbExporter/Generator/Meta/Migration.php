<?php

namespace ViKon\DbExporter\Generator\Meta;

/**
 * Class Migration
 *
 * @package ViKon\DbExporter\Generator\Meta
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 */
class Migration
{
    const STATUS_WAITING               = 0;
    const STATUS_IN_PROGRESS           = 1;
    const STATUS_RECURSIVE_FOREIGN_KEY = 2;
    const STATUS_MIGRATED              = 3;

    /** @type string */
    protected $tableName;

    /** @type int */
    protected $status = self::STATUS_WAITING;

    /**
     * Migration constructor.
     *
     * @param string $tableName
     */
    public function __construct($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get status
     *
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param int $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}