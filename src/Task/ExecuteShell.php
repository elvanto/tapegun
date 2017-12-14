<?php

namespace Tapegun\Task;

use Tapegun\AbstractTask;

class ExecuteShell extends AbstractTask
{
    /**
     * @var string
     */
    private $command = '';

    /**
     * @var string
     */
    protected $description = 'Executing shell command';

    /**
     * Sets the shell command.
     *
     * @param string $command
     */
    public function setCommand(string $command)
    {
       $this->command = $command;
    }

    /**
     * Sets the task description.
     *
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $command = $this->env->resolve($this->command);
        $proc = proc_open($command, [1=>['pipe','w'],2=>['pipe','w']], $pipes, $this->cwd);

        foreach ($pipes as $pipe) {
            if ($content = trim(stream_get_contents($pipe))) {
                $this->logMessage($content, true);
            }

            fclose($pipe);
        }

        return proc_close($proc) === 0;
    }
}