<?php

namespace ViKon\DbExporter\Meta;

use Doctrine\DBAL\Types\Type;
use Symfony\Component\Console\Output\OutputInterface;
use ViKon\DbExporter\Helper\TemplateHelper;

/**
 * Class Seed
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Meta
 */
class Seed
{
    /** @var string */
    protected $path;

    /** @var \ViKon\DbExporter\Meta\SeedTable */
    protected $table;

    use TemplateHelper;

    /**
     * @param string|null $connectionName connection name
     * @param string|null $tableName table name
     */
    public function __construct($connectionName, $tableName)
    {
        $this->table = new SeedTable($connectionName, $tableName);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return \ViKon\DbExporter\Meta\SeedTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get database table data rendered in model create
     *
     * @return array[]
     */
    public function renderData()
    {
        $columns = $this->table->getTableColumns();
        $types = [];
        $structure = [];
        foreach ($columns as $column) {
            $structure[] = snake_case(str_replace('ID', 'Id', $column->getName()));

            $type = $column->getType()->getName();
            switch ($type) {
                case Type::INTEGER:
                case Type::SMALLINT:
                case Type::BIGINT:
                    $types[$column->getName()] = 'int';
                    break;
                case Type::FLOAT:
                    $types[$column->getName()] = 'float';
                    break;
                case Type::DECIMAL:
                    $types[$column->getName()] = 'decimal';
                    break;
                case Type::BOOLEAN:
                    $types[$column->getName()] = 'boolean';
                    break;
                default:
                    $types[$column->getName()] = 'string';
                    break;
            }
        }

        $data = $this->table->getData();

        $tableData = [];
        foreach ($data as $row) {
            $modelData = [];
            foreach ($row as $column => $value) {
                switch ($types[$column]) {
                    case 'int':
                        $value = (int)$value;
                        break;
                    case 'float':
                        $value = (float)$value;
                        break;
                    case 'decimal':
                        $value = (double)$value;
                        break;
                    case 'boolean':
                        // boolean type can be TINYINT(1) but doctrine map TINYINT(1+) as boolean type
                        $value = (int)$value;
                        break;
                    case 'string':
                        $value = (string)$value;
                        break;
                }
                $modelData[] = var_export($value, true);
            }
            $tableData[] = '[' . implode(',', $modelData) . ']';
        }

        return [$structure, '[' . implode(',', $tableData) . ']', count($data)];
    }

    /**
     * Get seeder class
     *
     * @return string
     */
    public function getClass()
    {
        return studly_case(snake_case($this->table->getTableName()) . '_seeder');
    }

    /**
     * Get class model
     *
     * @return string
     */
    public function getModelClass()
    {
        $map = config('db-exporter.model.map');

        $namespace = config('db-exporter.model.namespace');
        $class = snake_case($this->getTable()->getTableName());

        foreach ($map as $item) {
            // Find matching table name
            $tablePattern = '/' . str_replace('/', '\/', $item['tablePattern']) . '/';
            if (preg_match($tablePattern, $this->table->getTableName()) === 1) {

                $namespace = $item['namespace'];

                if ($item['className'] !== null) {
                    $pattern = '/' . str_replace('/', '\/', $item['className']['pattern']) . '/';
                    $replacement = $item['className']['replacement'];
                    echo $replacement;
                    $class = preg_replace($pattern, $replacement, $class);
                }
                break;
            }
        }

        return $namespace . '\\' . studly_case(str_singular($class));
    }

    /**
     * Render seed class and write out to file
     *
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output command line output
     * @param bool $force force overwrite existing models or not
     */
    public function writeSeedOut(OutputInterface $output, $force)
    {
        $class = $this->getClass();
        $modelClass = $this->getModelClass();

        $fileName = $class . '.php';

        list($structure, $data, $count) = $this->renderData();

        if ($count > 0) {
            $this->writeToFileFromTemplate($this->path . '/' . $fileName, 'seeder', $output, [
                'use'       => $modelClass,
                'className' => $this->getClass(),
                'tableName' => snake_case($this->table->getTableName()),
                'model'     => class_basename($modelClass),
                'structure' => var_export($structure, true),
                'count'     => $count,
            ], $force);

            $this->writeToFileFromTemplate($this->path . '/data/' . snake_case($this->table->getTableName()) . '_table_data.php', 'seederData', $output, [
                'data' => $data,
            ], $force);
        }
    }

}