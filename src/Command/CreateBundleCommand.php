<?php
// src/Command/CreateBundleCommand.php

namespace Architect\ContaoCommandBundle\Command;

use Architect\ContaoCommandBundle\Helper\FileManager;
use Architect\ContaoCommandBundle\Helper\NamespaceManager;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Dotenv\Dotenv;

class CreateBundleCommand extends Command
{
    protected static $defaultName = 'architect:create:bundle';

    private ContaoFramework $framework;
    private ParameterBagInterface $parameterBag;
    private Dotenv $dotenv;

    public function __construct(ContaoFramework $framework, ParameterBagInterface $parameterBag, Dotenv $dotenv)
    {
        $this->framework = $framework;
        $this->parameterBag = $parameterBag;
        $this->dotenv = $dotenv;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create files to build your custom bundle.')
            ->addArgument('bundleName', InputArgument::OPTIONAL, 'Bundle name. Input: Architect, Output: ArchitectBundle | Default: App', 'App')
            ->addArgument('directory', InputArgument::OPTIONAL, 'Directory for custom bundle. Input: customVendor, Output: customVendor/src/... | Default: App/src', 'App')
            ->addOption('namespace', null, InputOption::VALUE_OPTIONAL, 'Custom namespace. Input: Architect/ContaoCommand, Output:Architect/ContaoCommandBundle | Default: App', 'App\\AppBundle');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();

        $bundleName = $input->getArgument('bundleName');

        if (str_contains($bundleName, 'Bundle'))
        {
            $bundleName = str_replace('Bundle', '', $bundleName);
        }

        $directory = $input->getArgument('directory');
        $namespace = NamespaceManager::getNamespace();

        if ($input->hasOption('namespace') && $input->getOption('namespace'))
        {
            $namespace = $input->getOption('namespace');
            NamespaceManager::setNamespace($namespace);
        }

        $filesToGenerate = [
            'Bundle' => $directory . '/src/' . $bundleName . 'Bundle.php',
            'Dependency' => $directory . '/src/DependencyInjection/' . $bundleName . 'Extension.php',
            'ContaoManager' => $directory . '/src/ContaoManager/Plugin.php',
            'json' => $directory . '/composer.json',
        ];

        foreach ($filesToGenerate as $fileName => $filePath)
        {
            if (FileManager::canOpenFile($filePath))
            {
                $output->writeln("You are trying to create a bundle within an already existing structure. Please provide another path");
                return Command::FAILURE;
            }

            FileManager::createFile($filePath);
            $this->generateFile($bundleName, $fileName, $namespace, $filePath);
        }

        return Command::SUCCESS;
    }

    private function generateFile($bundleName, $fileName, $namespace, $filePath): void
    {
        $content = $this->generateFileContent($bundleName, $fileName, $namespace);

        FileManager::appendToFile($filePath, $content);
    }

    private function generateFileContent($bundleName, $fileName, $namespace): string
    {

        switch ($fileName)
        {
            case 'Bundle':
                $bundleName .= 'Bundle';
                $content = <<<PHP
                <?php
                
                namespace $namespace;
                
                use Symfony\Component\HttpKernel\Bundle\Bundle;
                
                class $bundleName extends Bundle
                {
                }
                
                PHP;
                break;
            case 'Dependency':
                $bundleName .= 'Extension';
                $namespace .= '\DependencyInjection';
                $content = <<<PHP
                <?php

                namespace $namespace;
                
                use Symfony\Component\Config\FileLocator;
                use Symfony\Component\DependencyInjection\ContainerBuilder;
                use Symfony\Component\DependencyInjection\Extension\Extension;
                use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
                
                class $bundleName extends Extension
                {
                    public function load(array \$configs, ContainerBuilder \$container): void
                    {
                        \$loader = new YamlFileLoader(
                            \$container,
                            new FileLocator(__DIR__.'/../Resources/config')
                        );
                
                        \$loader->load('services.yaml');
                    }
                }
                PHP;
                break;
            case 'ContaoManager':
                $bundleName .= 'Bundle';
                $useNamespace = $namespace . '\\' . $bundleName;
                $namespace .= '\ContaoManager';
                $content = <<<PHP
                <?php
                
                namespace $namespace;
                
                use Contao\CoreBundle\ContaoCoreBundle;
                use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
                use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
                use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
                use $useNamespace;
                
                
                class Plugin implements BundlePluginInterface
                {
                  public function getBundles(ParserInterface \$parser): array
                  {
                    return [
                      BundleConfig::create($bundleName::class)
                        ->setLoadAfter([
                            ContaoCoreBundle::class,
                        ])
                    ];
                  }
                }
                PHP;
                break;
            case 'json':

                $jsonContent = [
                    'name' => strtolower($bundleName) . '/' . strtolower(str_replace('\\','-', $bundleName)),
                    'description' => 'Your bundle description',
                    'keywords' => ['contao', 'bundle'],
                    'version' => '1.0.0',
                    'type' => 'contao-bundle',
                    'require' => [
                        'php' => '8.*',
                        'contao/core-bundle' => '^5',
                    ],
                    'autoload' => [
                        'psr-4' => [
                            "$namespace\\" => 'src/',
                        ],
                    ],
                    'extra' => [
                        'contao-manager-plugin' => "$namespace\\ContaoManager\\Plugin",
                    ],
                ];

                $content = json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                break;
        }

        return $content;
    }

}
