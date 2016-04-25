<?php

namespace ViKon\DbExporter;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\View\Factory;
use ViKon\DbExporter\Generator\MigrationGenerator;

/**
 * Class Generator
 *
 * @package ViKon\DbExporter
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 */
class Generator
{
    /** @type \Illuminate\Contracts\Container\Container */
    protected $container;

    /**
     * Generator constructor.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get migration generator
     *
     * @return \ViKon\DbExporter\Generator\MigrationGenerator
     */
    public function migration()
    {
        return (new MigrationGenerator())
            ->setContainer($this->container)
            ->setViewFactory($this->container->make(Factory::class));
    }
}