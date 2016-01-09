<?php

namespace ViKon\DbExporter\Meta;

use Symfony\Component\Console\Output\OutputInterface;
use ViKon\DbExporter\Helper\ClassHelper;
use ViKon\DbExporter\Helper\TemplateHelper;

/**
 * Class Model
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta
 */
class Model
{
    use TemplateHelper, ClassHelper;

    /** @var \ViKon\DbExporter\Meta\ModelTable */
    protected $table;

    /**
     * @param string|null $connectionName connection name
     * @param string|null $tableName table name
     */
    public function __construct($connectionName, $tableName)
    {
        $this->table = new ModelTable($connectionName, $tableName);
        $this->setClass($tableName);
    }

    /**
     * @return \ViKon\DbExporter\Meta\ModelTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Apply custom config map settings if pattern exists
     */
    public function map()
    {
        $map = config('db-exporter.model.map');

        foreach ($map as $item) {
            // Find matching table name
            $tablePattern = '/' . str_replace('/', '\/', $item['tablePattern']) . '/';
            if (preg_match($tablePattern, $this->table->getTableName()) === 1) {
                $this->path = $item['path'];
                $this->namespace = $item['namespace'];

                if ($item['className'] !== null) {
                    $pattern = '/' . str_replace('/', '\/', $item['className']['pattern']) . '/';
                    $replacement = $item['className']['replacement'];
                    echo $replacement;
                    $this->class = preg_replace($pattern, $replacement, $this->class);
                }
                break;
            }
        }
    }

    /**
     * Render model class and write out to file
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output command line output
     * @param bool $force force overwrite existing models or not
     */
    public function writeOut(OutputInterface $output = null, $force = false)
    {
        $class = str_singular($this->getClass());

        $this->writeToFileFromTemplate($this->path . '/' . $class . '.php', 'model', $output, [
            'namespace'       => $this->namespace,
            'className'       => $class,
            'tableName'       => snake_case($this->table->getTableName()),
            'relationMethods' => $this->table->renderRelationMethods(),
        ], $force);
    }
}