<?php

namespace Tapegun\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tapegun\Config;
use Tapegun\Tapegun;

class Build extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Run all build tasks')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Specify a path to the build configuration file', 'build.json')
            ->addOption('target', 't', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Specify target(s) to build');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $manager = new Tapegun($input, $output, $this->getHelper('question'));
            $manager->build(Config::fromFilePath($input->getOption('config')), $input->getOption('target'));
            return 0;
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }
    }
}