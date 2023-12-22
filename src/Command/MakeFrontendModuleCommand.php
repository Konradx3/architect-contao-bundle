<?php
// src/Command/MakeFrontendModuleCommand.php

namespace Architect\ContaoCommandBundle\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class MakeFrontendModuleCommand extends Command
{
    protected static $defaultName = 'architect:make:frontend-module';

    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create content element files. controller, services.yaml, dca/tl_module.php, twig template')
            ->addArgument('controllerName', InputArgument::REQUIRED, 'Controller name')
            ->addArgument('directory', InputArgument::OPTIONAL, 'Directory for settings in custom bundle')
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Type custom namespace');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->framework->initialize();

        $controllerName = $input->getArgument('controllerName');
        $type = 'FMD';
        $path = $input->getArgument('directory');
        $namespace = $input->getOption('namespace');

        $this->callCommand('architect:make:controller', [
            'name' => $controllerName,
            'type' => $type,
            'directory' => $path,
            '--namespace' => $namespace,
        ], $output);

        $this->callCommand('architect:make:controller-config-services', [
            'name' => $controllerName,
            'type' => $type,
            'directory' => $path,
            '--namespace' => $namespace,
        ], $output);

        $this->callCommand('architect:make:controller-config-dca', [
            'name' => $controllerName,
            'type' => $type,
            'directory' => $path,
        ], $output);

        $this->callCommand('architect:make:controller-template', [
            'name' => $controllerName,
            'type' => $type,
            'directory' => $path,
        ], $output);

        $output->writeln('Controller set generated successfully.');

        $helper = $this->getHelper('question');
        $cacheQuestion = new ConfirmationQuestion('Did you want to run clear cache? [yes, no] (default yes): ', true);

        if ($helper->ask($input, $output, $cacheQuestion)) {
            $this->callCommand('cache:clear', [], $output);
        }

        $migrationQuestion = new ConfirmationQuestion('Did you want to run contao migrate? [yes, no] (default yes): ', true);

        if ($helper->ask($input, $output, $migrationQuestion)) {
            $this->callCommand('contao:migrate', [], $output);
        }

        return Command::SUCCESS;
    }

    private function callCommand($commandName, $arguments, $output)
    {
        $command = $this->getApplication()->find($commandName);

        $input = new ArrayInput(['command' => $commandName] + $arguments);
        $returnCode = $command->run($input, $output);

        return $returnCode;
    }
}
