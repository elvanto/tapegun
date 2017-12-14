<?php

namespace Tapegun;

use Symfony\Component\Console\Output\OutputInterface;
use Tapegun\Task\ExecuteShell;

class Tapegun
{
    const VERSION = '1.0';

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string[]
     */
    private $targets = [];

    /**
     * Tapegun constructor.
     *
     * @param OutputInterface $output
     */
    function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Runs a build configuration.
     *
     * @param Config   $config
     * @param string[] $targets
     */
    public function build(Config $config, array $targets = [])
    {
        $this->config = $config;
        $this->targets = $targets;

        $this->validateTargets();

        $this->output->writeln(sprintf('Building %s...', $this->config->get('name', '(untitled)')));

        $started = microtime(true);
        foreach ($this->generateTasks() as $task) {
            $this->output->writeln('<comment>======> </comment>' . $task->getDescription());

            if (!$task->run()) {
                return;
            }
        }

        $completed = microtime(true);
        $this->output->writeln(sprintf('<info>Build completed in %.2f seconds without error.</info>', $completed - $started));
    }

    /**
     * Validates selected build targets.
     *
     * @throws \InvalidArgumentException
     */
    public function validateTargets()
    {
        $availableTargets = [];
        foreach ($this->config->get('targets', []) as $target) {
            if (!isset($target['name']) || !is_string($target['name'])) {
                throw new \InvalidArgumentException('All targets require a name attribute.');
            }

            $availableTargets[] = $target['name'];
        }

        if (empty($this->targets)) {
            $this->targets = $availableTargets;
        }

        $unknownTargets = implode(', ', array_diff($this->targets, $availableTargets));

        if (!empty($unknownTargets)) {
            throw new \InvalidArgumentException('Unknown targets: ' . $unknownTargets);
        }
    }

    /**
     * Generates all tasks defined in the configuration.
     *
     * @return AbstractTask[]
     */
    public function generateTasks()
    {
        $tasks = [];

        // Build pre-target tasks
        foreach ($this->config->get('pre', []) as $spec) {
            $tasks[] = $this->generateTask($spec);
        }

        // Build target tasks
        foreach ($this->config->get('targets', []) as $target) {
            if (in_array($target['name'], $this->targets)) {
                foreach ($this->config->get('build', []) as $spec) {
                    $tasks[] = $this->generateTask($spec, $target);
                }
            }
        }

        // Build post-target tasks
        foreach ($this->config->get('post', []) as $spec) {
            $tasks[] = $this->generateTask($spec);
        }

        return $tasks;
    }

    /**
     * Generates a task from a configuration spec.
     *
     * @param array $spec
     * @param array $target
     * @return AbstractTask
     */
    public function generateTask(array $spec, array $target = [])
    {
        $env = (new Env($this->config->get('env', [])))
            ->merge(new Env($target['env'] ?? []))
            ->merge(new Env($spec['env'] ?? []));

        $cwd = $this->config->getCwd();
        if (isset($spec['cwd'])) {
            $cwd = $env->resolve($spec['cwd']);
        }

        if (isset($spec['command'])) {
            $task = new ExecuteShell($this->output, $env, $cwd);
            $task->setCommand($spec['command']);

            if (isset($spec['description'])) {
                $task->setDescription($spec['description']);
            }

            return $task;
        }

        if (isset($spec['class'])) {
            $className = str_replace('.', '\\', $spec['class']);
            $task = new $className($this->output, $env, $cwd);
            return $task;
        }

        throw new \InvalidArgumentException('Invalid task specification.');
    }
}