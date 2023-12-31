<?php

namespace Architect\ContaoCommandBundle\Command;

use Architect\ContaoCommandBundle\Helper\FileManager;
use Architect\ContaoCommandBundle\Helper\NamespaceManager;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeControllerCommand extends Command
{
    protected static $defaultName = 'architect:make:controller';

    private ContaoFramework $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new controller in a specified directory')
            ->addArgument('name', InputArgument::REQUIRED, 'Controller name')
            ->addArgument('type', InputArgument::OPTIONAL, 'Type of controller (FMD or CTE)')
            ->addArgument('directory', InputArgument::OPTIONAL, 'Directory to create the controller in in custom bundle /src/Controller')
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Type custom namespace');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->framework->initialize();

        $name = $input->getArgument('name');
        $directory = $input->getArgument('directory') ? $input->getArgument('directory') . '/src/Controller' : 'App/src/Controller';
        $type = strtoupper($input->getArgument('type'));
        $namespace = $input->getOption('namespace') ?: NamespaceManager::getNamespace();

        if ($type === 'CTE')
        {
            $directory .= '/ContentElement';
            $namespace .= '\Controller\ContentElement';
        }
        elseif ($type === 'FMD')
        {
            $directory .= '/FrontendModule';
            $namespace .= '\Controller\FrontendModule';
        }
        else
        {
            $namespace .= '\Controller';
        }

        $filePath = $directory . '/' . $name . '.php';

        if (FileManager::fileExists($filePath))
        {
            $output->writeln("Error: A file with the name '$name.php' already exists in the specified directory.");
            return Command::FAILURE;
        }

        FileManager::createFile($filePath, $this->generateControllerContent($name, $type, $namespace));

        $output->writeln("Controller file generated successfully: $filePath");

        return Command::SUCCESS;
    }

    private function generateControllerContent($controllerName, $type, $namespace)
    {
        $constType = lcfirst(str_replace('Controller', '', $controllerName));
        switch ($type)
        {
            default:
            case "CTE":

                $content = <<<PHP
                    <?php
                        
                    namespace $namespace;
                    
                    use Contao\\ContentModel;
                    use Contao\\CoreBundle\\Controller\\ContentElement\\AbstractContentElementController;
                    use Contao\\CoreBundle\\DependencyInjection\\Attribute\\AsContentElement;
                    use Contao\\CoreBundle\\Twig\FragmentTemplate;
                    use Symfony\\Component\\HttpFoundation\\Request;
                    use Symfony\\Component\\HttpFoundation\\Response;
                    
                    #[AsContentElement($controllerName::TYPE, category: 'categoryContentElement')] /* Change category name */
                    class $controllerName extends AbstractContentElementController
                    {
                        public const TYPE = '$constType'; /* Content Element name */
                    
                        protected function getResponse(FragmentTemplate \$template, ContentModel \$model, Request \$request): Response
                        {           
                            return \$template->getResponse();
                        }
                    
                    }
                    PHP;
                break;

            case "FMD":

                $content = <<<PHP
                    <?php
                        
                    namespace $namespace;
                    
                    use Contao\\CoreBundle\\Controller\\FrontendModule\\AbstractFrontendModuleController;
                    use Contao\\CoreBundle\\DependencyInjection\\Attribute\\AsFrontendModule;
                    use Contao\\CoreBundle\\Twig\\FragmentTemplate;
                    use Contao\\ModuleModel;
                    use Symfony\\Component\\HttpFoundation\\Request;
                    use Symfony\\Component\\HttpFoundation\\Response;
                    
                    #[AsFrontendModule($controllerName::TYPE, category: 'categoryFrontendModule')] /* Change category name */
                    class $controllerName extends AbstractFrontendModuleController
                    {
                        public const TYPE = '$constType';  /* Frontend Module name */
                    
                        protected function getResponse(FragmentTemplate \$template, ModuleModel \$model, Request \$request): Response
                        {   
                            return \$template->getResponse();
                        }
                    
                    }
                    PHP;
                break;
        }

        return $content;
    }
}
