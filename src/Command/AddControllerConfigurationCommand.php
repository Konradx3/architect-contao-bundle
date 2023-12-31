<?php
// src/Command/AddControllerConfigurationCommand.php

namespace Architect\ContaoCommandBundle\Command;

use Architect\ContaoCommandBundle\Helper\FileManager;
use Architect\ContaoCommandBundle\Helper\NamespaceManager;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddControllerConfigurationCommand extends Command
{
    protected static $defaultName = 'architect:make:controller-config-services';

    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        parent::__construct();
    }

    protected function configure() :void
    {
        $this
            ->setDescription('Add controller configuration to services.yaml')
            ->addArgument('name', InputArgument::REQUIRED, 'Controller name')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of controller (FMD or CTE)')
            ->addArgument('directory', InputArgument::OPTIONAL, 'Directory for settings in custom bundle')
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Type custom namespace');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->framework->initialize();

        $name = $input->getArgument('name');
        $type = strtoupper($input->getArgument('type'));
        $template = lcfirst(str_replace('Controller', '', $name));
        $namespace = $input->getOption('namespace') ?: NamespaceManager::getNamespace();

        if ($type === 'CTE')
        {
            $category = 'categoryContentElement';
            $elementType = 'content_element';
            $namespace .= '\Controller\ContentElement';
        }
        elseif ($type === 'FMD')
        {
            $category = 'categoryFrontendModule';
            $elementType = 'frontend_module';
            $namespace .= '\Controller\FrontendModule';
        }
        else
        {
            $category = 'controller';
            $elementType = 'content_element';
            $namespace .= '\Controller';
        }

        $directory = $input->getArgument('directory') ? $input->getArgument('directory') . '/src/Resources/config' : 'App/src/Resources/config';
        $servicesYamlPath = $directory . '/services.yaml';

        if (!FileManager::fileExists($servicesYamlPath))
        {
            $defaultContent = <<<YAML
                services:
                    _defaults:
                        autowire: true
                        autoconfigure: true
                        public: true
                        
                YAML;

            FileManager::createFile($servicesYamlPath, $defaultContent);
            $output->writeln('Created services.yaml with default content.');
        }

        $configuration = $this->generateControllerConfiguration($name, $namespace, $elementType, $category, $template);

        FileManager::appendToFile($servicesYamlPath, $configuration);

        $output->writeln('Controller configuration added to services.yaml');
        return Command::SUCCESS;
    }

    private function generateControllerConfiguration($controllerName, $namespace, $type, $category, $template)
    {
        $configuration = <<<YAML

            $namespace\\$controllerName:
                tags:
                    - name: contao.$type
                      category: $category
                      template: $template
                      renderer: forward
                      type: $template
                      
        YAML;

        return $configuration;
    }

}
