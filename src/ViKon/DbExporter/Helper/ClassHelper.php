<?php

namespace ViKon\DbExporter\Helper;

/**
 * Class ClassHelper
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Helper
 */
trait ClassHelper
{

    /** @var null|string */
    protected $path = null;

    /** @var null|string */
    protected $namespace = null;

    /** @var null|string */
    protected $class = null;

    /**
     * @return null|string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param null|string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return null|string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param null|string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return null|string
     */
    public function getClass()
    {
        return studly_case($this->class);
    }

    /**
     * @param null|string $class
     */
    public function setClass($class)
    {
        $this->class = snake_case($class);
    }

    /**
     * Get class with namespace
     *
     * @return string
     */
    public function getFullClass()
    {
        return ($this->namespace === null ? '' : $this->namespace . '\\') . $this->getClass();
    }


}