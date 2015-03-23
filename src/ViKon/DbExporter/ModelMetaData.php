<?php

namespace ViKon\DbExporter;


class ModelMetaData {
    use DatabaseHelper, TemplateHelper;

    /** @var string */
    protected $name;

    /** @var string */
    protected $connectionName;

    /** @var string */
    protected $namespace;

    /** @var mixed[] */
    protected $relations = [];

    /**
     * @param string $name           table name (class name)
     * @param string $connectionName connection name
     * @param string $namespace      class namespace
     * @param string $tablePrefix    table prefix
     */
    public function __construct($name, $connectionName, $namespace, $tablePrefix = '') {
        $this->name = $name;
        $this->connectionName = $connectionName;
        $this->namespace = $namespace;
        $this->tablePrefix = $tablePrefix === '' ? $tablePrefix . '__' : '';
    }

    /**
     * Add hasOne relation
     *
     * @param string $class      foreign class name
     * @param string $method     class method name
     * @param string $foreignKey foreign column name
     * @param string $localKey   local column name
     */
    public function addHasOneRelation($class, $method, $foreignKey, $localKey) {
        $this->relations[$localKey] = [
            'type'       => 'hasOne',
            'class'      => $class,
            'method'     => str_singular($method),
            'foreignKey' => $foreignKey,
        ];
    }

    /**
     * Add belongsTo relation
     *
     * @param string $class     foreign class name
     * @param string $method    class method name
     * @param string $parentKey foreign column name
     * @param string $localKey  local column name
     */
    public function addBelongsToRelation($class, $method, $localKey, $parentKey) {
        $this->relations[$localKey] = [
            'type'      => 'belongsTo',
            'class'     => $class,
            'method'    => str_singular($method),
            'parentKey' => $parentKey,
        ];
    }

    /**
     * Add hasMany relation
     *
     * @param string $class      foreign class name
     * @param string $method     class method name
     * @param string $foreignKey foreign column name
     * @param string $localKey   local column name
     */
    public function addHasManyRelation($class, $method, $foreignKey, $localKey) {
        $this->relations[$localKey] = [
            'type'       => 'hasManyRelation',
            'class'      => $class,
            'method'     => str_plural($method),
            'foreignKey' => $foreignKey,
        ];
    }

    /**
     * Get relations source
     *
     * @return string
     */
    public function getRelationsSource() {
        $source = '';
        foreach ($this->relations as $localColumn => $relation) {
            $class = $relation['class'];
            $method = camel_case($relation['method']);
            switch ($relation['type']) {
                case 'hasOne':
                    $foreignColumn = $relation['foreignKey'];
                    $source .= $this->renderTemplate('methodHasOne', [
                        '{{className}}'     => $class,
                        '{{methodName}}'    => $method,
                        '{{foreignColumn}}' => $foreignColumn,
                        '{{localColumn}}'   => $localColumn,
                    ]);
                    break;
                case 'belongsTo':
                    $parentColumn = $relation['parentKey'];
                    $source .= $this->renderTemplate('methodBelongsTo', [
                        '{{className}}'    => $class,
                        '{{methodName}}'   => $method,
                        '{{parentColumn}}' => $parentColumn,
                        '{{localColumn}}'  => $localColumn,

                    ]);
                    break;
                case 'hasManyRelation':
                    $foreignColumn = $relation['foreignKey'];
                    $source .= $this->renderTemplate('methodHasMany', [
                        '{{className}}'     => $class,
                        '{{methodName}}'    => $method,
                        '{{foreignColumn}}' => $foreignColumn,
                        '{{localColumn}}'   => $localColumn,
                    ]);
                    break;
            }
        }

        return $source;
    }

    /**
     * Get table class
     *
     * @param bool $namespace if TRUE prepend namespace, otherwise return class name
     *
     * @return string
     */
    public function getClass($namespace = true) {
        return ($namespace ? $this->namespace . '\\' : '') . str_singular(studly_case($this->getName(true)));
    }

    /**
     * Get table name
     *
     * @param bool $withPrefix table name with prefix or not
     *
     * @return string
     */
    public function getName($withPrefix = false) {
        return ($withPrefix ? $this->tablePrefix : '') . $this->name;
    }

    /**
     * Get index by columns name
     *
     * @param string|string[] $columnsName columns name to match index columns
     *
     * @return bool|\Doctrine\DBAL\Schema\Index
     */
    public function getIndexByColumn($columnsName) {
        return $this->getTableIndexByColumn($columnsName, $this->name, $this->connectionName);
    }

    /**
     * Get table foreign key information
     *
     * @return \Doctrine\DBAL\Schema\ForeignKeyConstraint[]
     */
    public function getForeignKeys() {
        return $this->getTableForeignKeys($this->name, $this->connectionName);
    }

    /**
     * Get all foreign keys foreign table names
     *
     * @return array
     */
    public function getForeignTableNames() {
        $foreignTableNames = [];
        $foreignKeys = $this->getForeignKeys();

        foreach ($foreignKeys as $foreignKey) {
            $foreignTableNames[] = $foreignKey->getForeignTableName();
        }

        return array_unique($foreignTableNames);
    }
}