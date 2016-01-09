<?php

namespace ViKon\DbExporter\Meta;

use Symfony\Component\Console\Output\OutputInterface;
use ViKon\DbExporter\Helper\TemplateHelper;

/**
 * Class Migration
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta
 */
class Migration
{
    use TemplateHelper;

    const STATUS_WAITING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_RECURSIVE_FOREIGN_KEY = 2;
    const STATUS_MIGRATED = 3;

    /** @var string */
    protected $path;

    /** @var int */
    protected $status = self::STATUS_WAITING;

    /** @var \ViKon\DbExporter\Meta\MigrationTable */
    protected $table;

    /**
     * @param string|null $connectionName connection name
     * @param string|null $tableName table name
     */
    public function __construct($connectionName, $tableName)
    {
        $this->table = new MigrationTable($connectionName, $tableName);
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \ViKon\DbExporter\Meta\MigrationTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Render migration create table class and write out to file
     *
     * @param int $index migration file index
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output command line output
     * @param bool $force force overwrite existing models or not
     */
    public function writeTableOut($index, OutputInterface $output = null, $force = false)
    {
        if ($this->status !== self::STATUS_IN_PROGRESS && $this->status !== self::STATUS_RECURSIVE_FOREIGN_KEY) {
            return;
        }

        $class = studly_case('create_' . snake_case($this->table->getTableName()) . '_table');

        $fileName = date('Y_m_d_') . str_pad($index, 6, '0', STR_PAD_LEFT) . '_' . snake_case($class) . '.php';

        $this->writeToFileFromTemplate($this->path . '/' . $fileName, 'migrationCreateTable', $output, [
            'className'   => $class,
            'tableName'   => snake_case($this->table->getTableName()),
            'columns'     => $this->table->renderCreateColumns(),
            'indexes'     => $this->table->renderCreateIndexes(),
            'foreignKeys' => $this->status === self::STATUS_IN_PROGRESS ? $this->table->renderCreateForeignKeys() : '',
        ], $force);
    }

    /**
     * Render migration add foreign keys to table class and write out to file
     *
     * @param int $index migration file index
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output command line output
     * @param bool $force force overwrite existing models or not
     */
    public function writeForeignKeysOut($index, OutputInterface $output = null, $force = false)
    {
        if ($this->status !== self::STATUS_RECURSIVE_FOREIGN_KEY) {
            return;
        }

        $class = studly_case('add_foreign_keys_to_' . snake_case($this->table->getTableName()) . '_table');

        $fileName = date('Y_m_d_') . str_pad($index, 6, '0', STR_PAD_LEFT) . '_' . snake_case($class) . '.php';

        $this->writeToFileFromTemplate($this->path . '/' . $fileName, 'migrationAddForeignKey', $output, [
            'className'         => $class,
            'tableName'         => snake_case($this->table->getTableName()),
            'createForeignKeys' => $this->table->renderCreateForeignKeys(),
            'dropForeignKeys'   => $this->table->renderDropForeignKeys(),
        ], $force);
    }

}