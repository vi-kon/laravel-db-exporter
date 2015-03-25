<?php

namespace ViKon\DbExporter\Helper;

use Symfony\Component\Console\Output\OutputInterface;
use ViKon\DbExporter\DbExporterException;

/**
 * Class TemplateHelper
 *
 * @author  Kovács Vince <vincekovacs@hotmail.com>
 *
 * @package ViKon\DbExporter\Helper
 */
trait TemplateHelper {

    /**
     * Get rendered template
     *
     * @param string $templateName template file name
     * @param array  $variables    array of template variables (key value pairs)
     *
     * @return string
     *
     * @throws \ViKon\DbExporter\DbExporterException
     */
    protected function renderTemplate($templateName, array $variables = []) {
        $templatePath = __DIR__ . '/../../../stub/' . $templateName;
        if (!file_exists($templatePath)) {
            throw new DbExporterException('Template ' . $templateName . ' not found');
        }

        $template = file_get_contents($templatePath);

        $search = array_keys($variables);
        $replace = array_values($variables);

        foreach ($search as &$item) {
            $item = '{{' . $item . '}}';
        }

        return str_replace($search, $replace, $template);
    }

    /**
     * Render template content and write to file
     *
     * @param string                                                 $path         output relative file name
     * @param string                                                 $templateName source relative template file name
     * @param \Symfony\Component\Console\Output\OutputInterface|null $output       command line output
     * @param array                                                  $variables    array of template variable key value
     *                                                                             pairs
     * @param bool                                                   $overwrite    if output file exists and this
     *                                                                             option is TRUE overwrite file,
     *                                                                             otherwise skip
     *
     * @throws \ViKon\DbExporter\DbExporterException
     */
    protected function writeToFileFromTemplate($path, $templateName, OutputInterface $output = null, array $variables = [], $overwrite = false) {
        if ($overwrite || !file_exists($path) && !is_dir($path)) {
            $dir = dirname($path);
            if (!file_exists($dir) || !is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            file_put_contents($path, $this->renderTemplate($templateName, $variables));
            $output->writeln('<info>File created:</info> ' . $path);
        } else {
            $output->writeln('<info>File already exists:</info> ' . $path . ' <comment>(Overwrite disabled)</comment>');
        }
    }
}