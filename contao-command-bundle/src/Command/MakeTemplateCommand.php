<?php
// src/Command/MakeTemplateCommand.php

namespace Architect\ContaoCommandBundle\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeTemplateCommand extends Command
{
    protected static $defaultName = 'architect:make:controller-template';

    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new template in a specified directory')
            ->addArgument('name', InputArgument::REQUIRED, 'Controller name')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of controller (FMD or CTE)')
            ->addArgument('directory', InputArgument::OPTIONAL, 'Directory to create the controller in in custom bundle /src/Controller');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->framework->initialize();

        $name = $input->getArgument('name');
        $directory = $input->getArgument('directory') ? $input->getArgument('directory') . '/src/Resources/contao/templates' : 'App/src/Resources/contao/templates';
        $type = strtoupper($input->getArgument('type'));

        if ($type === 'CTE')
        {
            $directory .= '/content_element';
        }
        elseif ($type === 'FMD')
        {
            $directory .= '/frontend_module';
        }

        $template = lcfirst(str_replace('Controller', '', $name));

        $filePath = $directory . '/' . $template . '.html.twig';

        if (file_exists($filePath))
        {
            $output->writeln("Error: A file with the name '$name.html.twig' already exists in the specified directory.");

            return Command::FAILURE;
        }

        $filePath = $this->generateControllerFile($template, $directory, $type);

        $output->writeln("Twig template file generated successfully: $filePath");

        return Command::SUCCESS;
    }

    private function generateControllerFile($template, $directory, $type)
    {
        $controllerContent = $this->generateControllerContent($template, $type);

        if (!is_dir($directory))
        {
            mkdir($directory, 0777, true);
        }

        $filePath = $directory . '/' . $template . '.html.twig';

        file_put_contents($filePath, $controllerContent);

        return $filePath;
    }

    private function generateControllerContent($template, $type)
    {
        switch ($type)
        {
            default:
            case "CTE":

                $content = <<<HTML
                src/Resources/contao/templates/content_element/$template.html.twig
                HTML;
                break;

            case "FMD":

                $content = <<<HTML
                src/Resources/contao/templates/frontend_module/$template.html.twig
                HTML;
                break;
        }

        return $content;
    }
}
