<?php

namespace ViKon\DbExporter;


class ModelTable {
    use DatabaseHelper;

    /** @var string */
    protected $name;

    /** @var string */
    protected $connectionName;

    /** @var mixed[] */
    protected $relations = [];

    /**
     * @param string $name           table name
     * @param string $connectionName connection name
     * @param array  $options        array of options
     */
    public function __construct($name, $connectionName, array $options = []) {
        $this->name = $name;
        $this->connectionName = $connectionName;
        $this->tablePrefix = isset($options['tablePrefix']) ? $options['tablePrefix'] . '__' : '';
    }

    public function addHasOneRelation($class, $method, $foreignKey, $localKey) {
        $this->relations[$localKey] = [
            'type'       => 'hasOne',
            'class'      => $class,
            'method'     => $method,
            'foreignKey' => $foreignKey,
        ];
    }

    public function addBelongsToRelation($class, $method, $localKey, $parentKey) {
        $this->relations[$localKey] = [
            'type'      => 'belongsTo',
            'class'     => $class,
            'method'    => $method,
            'parentKey' => $parentKey,
        ];
    }

    public function addHasManyRelation($class, $method, $foreignKey, $localKey) {
        $this->relations[$localKey] = [
            'type'       => 'hasManyRelation',
            'class'      => $class,
            'method'     => $method,
            'foreignKey' => $foreignKey,
        ];
    }

    public function getRelationsSource() {
        $source = '';
        foreach ($this->relations as $localKey => $relation) {
            $class = $relation['class'];
            $source .= "\n" . 'public function ' . $relation['method'] . '() {';
            switch ($relation['type']) {
                case 'hasOne':
                    $foreignKey = $relation['foreignKey'];
                    $source .= "\n" . '    $this->hasOne(\'' . $class . '\', \'' . $foreignKey . '\', \'' . $localKey . '\');';
                    break;
                case 'belongsTo':
                    $parentKey = $relation['parentKey'];
                    $source .= "\n" . '    $this->belongsTo(\'' . $class . '\', \'' . $localKey . '\', \'' . $parentKey . '\');';
                    break;
                case 'hasManyRelation':
                    $foreignKey = $relation['foreignKey'];
                    $source .= "\n" . '    $this->hasMany(\'' . $class . '\', \'' . $foreignKey . '\', \'' . $localKey . '\');';
                    break;
            }
            $source .= "\n" . '}';
        }

        return $source;
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