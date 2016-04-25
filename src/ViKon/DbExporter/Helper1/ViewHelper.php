<?php

namespace ViKon\DbExporter\Helper1;

use Illuminate\Contracts\View\Factory;

/**
 * Trait ViewHelper
 *
 * @package ViKon\DbExporter\Helper1
 *
 * @author  Kovács Vince<vincekovacs@hotmail.com>
 */
trait ViewHelper
{
    /** @type \Illuminate\Contracts\View\Factory */
    protected $viewFactory;

    /**
     * Set view factory
     *
     * @param \Illuminate\Contracts\View\Factory $factory
     *
     * @return $this
     */
    public function setViewFactory(Factory $factory)
    {
        $this->viewFactory = $factory;

        return $this;
    }

    /**
     * Render content
     *
     * @param string $view
     * @param array  $data
     *
     * @return string
     */
    public function render($view, array $data = [])
    {
        return $this->viewFactory->make($view, $data)->render();
    }
}