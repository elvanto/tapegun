<?php

namespace Tapegun\Task;

use Tapegun\AbstractTask;
use Tapegun\Proc;

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

        $proc = new Proc($command, $this->cwd);
        $status = $proc->close();

        if ($content = $proc->getOutput()) {
            $this->logMessage($content, true);
        }

        return $status === 0;
    }
}