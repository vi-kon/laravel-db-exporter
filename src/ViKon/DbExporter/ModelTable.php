<?php

namespace ViKon\DbExporter;


class ModelTable {
    use DatabaseHelper;

    /** @var string */
    protected $name;

    /** @var string */
    protected $connectionName;

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