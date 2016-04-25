<?php

namespace ViKon\DbExporter\Helper1;

use Illuminate\Contracts\Container\Container;

/**
 * Trait ContainerHelper
 *
 * @package ViKon\DbExporter\Helper1
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 */
trait ContainerHelper
{
    /** @type \Illuminate\Contracts\Container\Container */
    protected $container;

    /**
     * Set container
     *
     * @param \Illuminate\Contracts\Container\Container $container
     *
     * @return $this
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;

        return $this;
    }
}