<?php

namespace Tapegun\Task;

use Tapegun\AbstractTask;
use Tapegun\Proc;

class ExecuteAsync extends AbstractTask
{
    /**
     * @var array
     */
    private $tasks = [];

    /**
     * Adds a task associated with a target.
     *
     * @param string       $target
     * @param ExecuteShell $task
     */
    public function addTask(string $target, ExecuteShell $task)
    {
        $this->tasks[$target] = $task;
        $this->description = sprintf(
            '%s (%s)',
            $task->getDescription(),
            implode(', ', array_keys($this->tasks))
        );
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $procs = [];
        foreach ($this->tasks as $task) {
            $task->run();
            $procs[] = $task->getProc();
        }

        Proc::wait($procs);

        foreach ($procs as $proc) {
            $this->logMessage($proc->getOutput(), true);

            if ($proc->getStatus() !== 0) {
                return false;
            }
        }

        return true;
    }
}