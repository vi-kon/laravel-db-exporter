<?php

namespace ViKon\DbExporter\Contract\Generator;

/**
 * Interface MigrationGenerator
 *
 * @package ViKon\DbExporter\Contract\Generator
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 */
interface MigrationGenerator
{
    /**
     * Select only specified tables
     *
     * @param array $tableNames
     *
     * @return $this
     */
    public function only(array $tableNames);

    /**
     * Select every table except specified ones
     *
     * @param array $tableNames
     *
     * @return $this
     */
    public function except(array $tableNames);

    /**
     * Generate migration files
     *
     * @param string $path
     * @param bool   $force
     */
    public function generate($path, $force = false);
}