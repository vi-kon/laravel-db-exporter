<?php

namespace ViKon\DbExporter;


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
        $templatePath = __DIR__ . '/../../stub/' . $templateName;
        if (!file_exists($templatePath)) {
            throw new DbExporterException('Template ' . $templateName . ' not found');
        }

        $template = file_get_contents($templatePath);

        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * Render template content and write to file
     *
     * @param string $sourceTemplateName  source relative template file name
     * @param string $destinationFileName output relative file name
     * @param array  $variables           array of template variable key value pairs
     */
    protected function writeToFileFromTemplate($sourceTemplateName, $destinationFileName, array $variables = []) {
        $path = base_path($this->option('path') . '/' . $destinationFileName);

        if ($this->option('overwrite') || !file_exists($path) && !is_dir($path)) {
            file_put_contents($path, $this->renderTemplate($sourceTemplateName, $variables));
            $this->output->writeln('<info>File created:</info> ' . $destinationFileName);
        } else {
            $this->output->writeln('<info>File already exists:</info> ' . $destinationFileName . ' <comment>(Overwrite disabled)</comment>');
        }
    }
}