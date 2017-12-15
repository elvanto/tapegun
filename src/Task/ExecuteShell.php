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
     * @var bool
     */
    private $async = false;

    /**
     * @var Proc
     */
    private $proc;

    /**
     * Returns the process.
     *
     * @return Proc
     */
    public function getProc()
    {
        return $this->proc;
    }

    /**
     * Configures the command.
     *
     * @param string $command
     * @param string $description
     * @param bool $async
     */
    public function configure(
        string $command,
        string $description = null,
        bool $async = false
    ) {
        $this->command = $command;
        $this->async = $async;

        if ($description) {
            $this->description = $description;
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $command = $this->env->resolve($this->command);

        $this->proc = new Proc($command, $this->cwd);

        // Prevent task from blocking
        if ($this->async) {
            return true;
        }

        $status = $this->proc->close();

        if ($content = $this->proc->getOutput()) {
            $this->logMessage($content, true);
        }

        return $status === 0;
    }
}