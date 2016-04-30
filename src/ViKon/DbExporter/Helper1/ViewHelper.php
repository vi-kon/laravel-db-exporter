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
        $data['_indent'] = function ($text, $indent) {
            return $this->indent($text, $indent);
        };

        return $this->viewFactory->make($view, $data)->render();
    }

    /**
     * Indent text with white spaces
     *
     * @param string $text
     * @param int    $indent
     *
     * @return string
     */
    public function indent($text, $indent)
    {
        $chunks = explode("\n", $text);

        $chunks = array_map(function ($chunk) use ($indent) {
            return str_repeat(' ', $indent) . $chunk;
        }, $chunks);

        return implode("\n", $chunks);
    }
}