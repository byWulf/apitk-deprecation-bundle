<?php

declare(strict_types=1);

namespace Shopping\ApiTKDeprecationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration.
 *
 * @package Shopping\ApiTKDeprecationBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    public const TRIGGER_DEPRECATIONS = 'trigger_deprecations';

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('apitk_deprecation');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode(self::TRIGGER_DEPRECATIONS)->defaultTrue()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
