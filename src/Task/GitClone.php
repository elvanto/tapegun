<?php

namespace Tapegun\Task;

use Tapegun\AbstractTask;

class GitClone extends AbstractTask
{
    protected $description = 'Cloning source repository';

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$source = $this->env->resolve('{{git:source}}')) {
            $this->logError('Missing git:source environment variable.');
            return false;
        }

        if (!$target = $this->env->resolve('{{git:target}}')) {
            $this->logError('Missing git:target environment variable.');
            return false;
        }

        $proc = proc_open(
            sprintf('git clone --depth 1 %s %s/', $source, $target),
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $this->cwd
        );

        foreach ($pipes as $pipe) {
            if ($content = trim(stream_get_contents($pipe))) {
                $this->logMessage($content, true);
            }

            fclose($pipe);
        }

        return proc_close($proc) === 0;
    }
}
