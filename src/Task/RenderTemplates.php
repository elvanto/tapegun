<?php

namespace Tapegun\Task;

use Tapegun\AbstractTask;

class RenderTemplates extends AbstractTask
{
    protected $description = 'Rendering templates';

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$source = $this->env->resolve('{{templates:source}}')) {
            $this->logError('Missing templates:source environment variable.');
            return false;
        }

        if (!$target = $this->env->resolve('{{templates:target}}')) {
            $this->logError('Missing templates:target environment variable.');
            return false;
        }

        if (!is_array($source)) {
            $source = [$source];
        }

        foreach ($source as $filename) {
            $filename = $this->buildPath($filename);

            if (!is_file($filename)) {
                $this->logError($filename . ' does not exist.');
                return false;
            }

            if ($content = file_get_contents($filename)) {
                $content = $this->env->resolve($content);
                $destination = $this->buildPath($target . '/' . basename($filename));
                file_put_contents($destination, $content);

                $this->logMessage('Rendered ' . $destination, true);
            }
        }

        return true;
    }
}