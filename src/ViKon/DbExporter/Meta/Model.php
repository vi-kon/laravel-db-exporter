<?php

namespace ViKon\DbExporter\Meta;

use Symfony\Component\Console\Output\OutputInterface;
use ViKon\DbExporter\Helper\TemplateHelper;

/**
 * Class Model
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta
 */
class Model {
    use TemplateHelper;

    /** @var string */
    protected $path;

    /** @var string */
    protected $namespace;

    /** @var string */
    protected $class;

    /** @var \ViKon\DbExporter\Meta\Table */
    protected $table;

    /**
     * @param string|null $connectionName connection name
     * @param string|null $tableName      table name
     */
    public function __construct($connectionName, $tableName) {
        $this->table = new Table($connectionName, $tableName);
        $this->class = snake_case($tableName);
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path) {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getNamespace() {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * Get class name without namespace
     *
     * @return string
     */
    public function getClass() {
        return studly_case($this->class);
    }

    /**
     * Get class name with namespace
     *
     * @return string
     */
    public function getFullClass() {
        return trim($this->namespace, '/') . '\\' . $this->getClass();
    }

    /**
     * @return \ViKon\DbExporter\Meta\Table
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * Render model class and write out to file
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output    command line output
     * @param bool                                                   $overwrite overwrite existing models or not
     */
    public function writeOut(OutputInterface $output = null, $overwrite = false) {
        $this->writeToFileFromTemplate($this->path . '/' . $this->getClass() . '.php', 'model', $output, [
            'namespace'       => $this->namespace,
            'className'       => $this->getClass(),
            'tableName'       => $this->table->getTableName(),
            'relationMethods' => $this->table->renderRelationMethods(),
        ], $overwrite);
    }

    /**
     * Apply custom config map settings if pattern exists
     */
    public function map() {
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
}