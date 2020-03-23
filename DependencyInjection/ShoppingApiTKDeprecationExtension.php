<?php

declare(strict_types=1);

namespace Shopping\ApiTKDeprecationBundle\DependencyInjection;

use Exception;
use Shopping\ApiTKDeprecationBundle\EventListener\DeprecationListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class ShoppingApiTKDeprecationExtension.
 *
 * @package Shopping\ApiTKDeprecationBundle\DependencyInjection
 */
class ShoppingApiTKDeprecationExtension extends Extension
{
    /**
     * Loads a specific configuration.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $deprecationListener = $container->getDefinition(DeprecationListener::class);
        $deprecationListener->setArgument('$triggerDeprecations', $config[Configuration::TRIGGER_DEPRECATIONS] ?? true);
    }

    public function getAlias(): string
    {
        return 'apitk_deprecation';
    }
}
