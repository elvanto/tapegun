<?php

namespace Tapegun;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

abstract class AbstractTask
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var QuestionHelper
     */
    private $helper;

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
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $helper
     * @param Env             $env
     * @param string          $cwd
     */
    function __construct(InputInterface $input, OutputInterface $output, QuestionHelper $helper, Env $env, string $cwd)
    {
        $this->input = $input;
        $this->output = $output;
        $this->helper = $helper;
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
            $verbose ? OutputInterface::VERBOSITY_VERBOSE : 0
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
     * Prompts the user and returns the collected input.
     *
     * @param string $message
     * @return mixed
     */
    protected function prompt(string $message)
    {
        $prompt = new Question($message);
        return $this->helper->ask($this->input, $this->output, $prompt);
    }

    /**
     * Builds an absolute file path based on the current working
     * directory and provided relative or absolute path.
     *
     * @param string $path
     * @return string
     */
    protected function buildPath(string $path)
    {
        if (!empty($path) && $path[0] == '/') {
            return $path;
        }

        return $this->cwd . '/' . $path;
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