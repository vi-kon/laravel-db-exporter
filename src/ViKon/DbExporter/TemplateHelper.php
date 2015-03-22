<?php

namespace ViKon\DbExporter;


trait TemplateHelper {

    /**
     * Render content from template and write content to file
     *
     * @param string $sourceTemplateName  source relative template file name
     * @param string $destinationFileName output relative file name
     * @param array  $variables           array of template variable key value pairs
     */
    protected function writeToFileFromTemplate($sourceTemplateName, $destinationFileName, array $variables = []) {
        $path = base_path($this->option('path') . '/' . $destinationFileName);

        if ($this->option('overwrite') || !file_exists($path) && !is_dir($path)) {
            $template = file_get_contents(__DIR__ . '/../../stub/' . $sourceTemplateName);
            $template = str_replace(array_keys($variables), array_values($variables), $template);

            file_put_contents($path, $template);
            $this->output->writeln('<info>File created:</info> ' . $destinationFileName);
        } else {
            $this->output->writeln('<info>File already exists:</info> ' . $destinationFileName . ' <comment>(Overwrite disabled)</comment>');
        }
    }
}