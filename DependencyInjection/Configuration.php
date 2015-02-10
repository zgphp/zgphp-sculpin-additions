<?php

namespace Zgphp\Sculpin\Bundle\ZgphpSculpinAdditionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
  public function getConfigTreeBuilder()
  {
    $treeBuilder = new TreeBuilder();

    $rootNode = $treeBuilder->root('sculpin_meetup_next_event');

    return $treeBuilder;
  }
}
