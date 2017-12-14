<?php

namespace Tapegun;

use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractTask
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Env
     */
    protected $env;

    /**
     * @var string
     */
    protected $cwd;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * AbstractTask constructor.
     *
     * @param OutputInterface $output
     * @param Env             $env
     * @param string          $cwd
     */
    function __construct(OutputInterface $output, Env $env, string $cwd)
    {
        $this->output = $output;
        $this->env = $env;
        $this->cwd = $cwd;
    }

    /**
     * Logs a message.
     *
     * @param string $message
     * @param bool   $verbose
     */
    protected function logMessage(string $message, bool $verbose = false)
    {
        $this->output->writeln(
            $message,
            $verbose ? OutputInterface::VERBOSITY_VERBOSE: 0
        );
    }

    /**
     * Logs an error message.
     *
     * @param string $message
     */
    protected function logError(string $message)
    {
        $this->output->writeln('<error>' . $message . '</error>');
    }

    /**
     * Returns the task description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Runs the task.
     *
     * @return bool
     */
    abstract public function run();
}