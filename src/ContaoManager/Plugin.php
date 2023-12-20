<?php

namespace Architect\ContaoCommandBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Architect\ContaoCommandBundle\ContaoCommandBundle;


class Plugin implements BundlePluginInterface
{
  public function getBundles(ParserInterface $parser): array
  {
    return [
      BundleConfig::create(ContaoCommandBundle::class)
        ->setLoadAfter([
            ContaoCoreBundle::class,
        ])
    ];
  }
}
