<?php
// src/Command/AddControllerDCACommand.php

namespace Architect\ContaoCommandBundle\Command;

use Architect\ContaoCommandBundle\Helper\FileManager;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddControllerDCACommand extends Command
{
    protected static $defaultName = 'architect:make:controller-config-dca';

    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add controller configuration to DCA')
            ->addArgument('name', InputArgument::REQUIRED, 'Controller name')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of controller (FMD or CTE)')
            ->addArgument('directory', InputArgument::OPTIONAL, 'Directory for settings in custom bundle');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->framework->initialize();

        $name = $input->getArgument('name');
        $controllerType = lcfirst(str_replace('Controller', '', $name));
        $type = strtoupper($input->getArgument('type'));
        $directory = $input->getArgument('directory') ? $input->getArgument('directory') . '/src/Resources/contao/dca' : 'App/src/Resources/contao/dca';

        if (!is_dir($directory))
        {
            mkdir($directory, 0777, true);
            $output->writeln("Created directory: $directory");
        }

        if ($type === 'CTE')
        {
            $directory .= '/tl_content.php';
        }
        elseif ($type === 'FMD')
        {
            $directory .= '/tl_module.php';
        }
        else
        {
            $output->writeln('Error: Type (FMD or CTE) is required. Did you forget about it?');
            return Command::FAILURE;
        }

        if (!FileManager::fileExists($directory))
        {
            FileManager::createFile($directory, "<?php\n");
            $output->writeln("Created empty file: $directory");
        }

        if (FileManager::canOpenFile($directory) === false)
        {
            $output->writeln('Error: Unable to open the file for reading.');
            return Command::FAILURE;
        }

        $lines = file($directory);

        if ($type === 'CTE')
        {
            $lines[0] = "<?php\n\$GLOBALS['TL_DCA']['tl_content']['palettes']['$controllerType'] = '{type_legend},type,headline;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';\n";
        }
        elseif ($type === 'FMD')
        {
            $lines[0] = "<?php\n\$GLOBALS['TL_DCA']['tl_module']['palettes']['$controllerType'] = '{type_legend},name,type,headline;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},cssID;{invisible_legend:hide},invisible,start,stop';\n";
        }

        $content = implode('', $lines);

        FileManager::createFile($directory, $content);

        $output->writeln('Added config to dca');
        return Command::SUCCESS;

    }
}
