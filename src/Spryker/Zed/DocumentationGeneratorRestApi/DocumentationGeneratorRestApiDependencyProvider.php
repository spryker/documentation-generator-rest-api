<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi;

use Spryker\Glue\GlueApplication\Rest\Collection\ResourceRouteCollection;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToDoctrineInflectorAdapter;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToSymfonyFilesystemAdapter;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToSymfonyFinderAdapter;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToSymfonyYamlAdapter;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\Service\DocumentationGeneratorRestApiToUtilEncodingServiceBridge;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig getConfig()
 */
class DocumentationGeneratorRestApiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @var string
     */
    public const PLUGIN_RESOURCE_ROUTE_PLUGIN_PROVIDERS = 'PLUGIN_RESOURCE_ROUTE_PLUGIN_PROVIDERS';

    /**
     * @var string
     */
    public const PLUGIN_RESOURCE_RELATIONSHIP_COLLECTION_PROVIDER = 'PLUGIN_RESOURCE_RELATIONSHIP_COLLECTION_PROVIDER';

    /**
     * @var string
     */
    public const COLLECTION_RESOURCE_ROUTE = 'COLLECTION_RESOURCE_ROUTE';

    /**
     * @var string
     */
    public const YAML_DUMPER = 'YAML_DUMPER';

    /**
     * @var string
     */
    public const FILESYSTEM = 'FILESYSTEM';

    /**
     * @var string
     */
    public const FINDER = 'FINDER';

    /**
     * @var string
     */
    public const TEXT_INFLECTOR = 'TEXT_INFLECTOR';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = $this->addUtilEncodingService($container);
        $container = $this->addYamlDumper($container);
        $container = $this->addFilesystem($container);
        $container = $this->addFinder($container);
        $container = $this->addTextInflector($container);
        $container = $this->addResourceRouteCollection($container);
        $container = $this->addResourceRoutePluginProviderPlugins($container);
        $container = $this->addResourceRelationshipCollectionProviderPlugin($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, function (Container $container) {
            return new DocumentationGeneratorRestApiToUtilEncodingServiceBridge(
                $container->getLocator()->utilEncoding()->service(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addYamlDumper(Container $container): Container
    {
        $container->set(static::YAML_DUMPER, function () {
            return new DocumentationGeneratorRestApiToSymfonyYamlAdapter();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addFilesystem(Container $container): Container
    {
        $container->set(static::FILESYSTEM, function () {
            return new DocumentationGeneratorRestApiToSymfonyFilesystemAdapter();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addFinder(Container $container): Container
    {
        $container->set(static::FINDER, function () {
            return new DocumentationGeneratorRestApiToSymfonyFinderAdapter();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addTextInflector(Container $container): Container
    {
        $container->set(static::TEXT_INFLECTOR, function () {
            return new DocumentationGeneratorRestApiToDoctrineInflectorAdapter();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addResourceRoutePluginProviderPlugins(Container $container): Container
    {
        $container->set(static::PLUGIN_RESOURCE_ROUTE_PLUGIN_PROVIDERS, function () {
            return $this->getResourceRoutePluginProviderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addResourceRouteCollection(Container $container): Container
    {
        $container->set(static::COLLECTION_RESOURCE_ROUTE, function () {
            return new ResourceRouteCollection();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Glue\DocumentationGeneratorRestApiExtension\Dependency\Plugin\ResourceRoutePluginsProviderPluginInterface>
     */
    protected function getResourceRoutePluginProviderPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addResourceRelationshipCollectionProviderPlugin(Container $container): Container
    {
        $container->set(static::PLUGIN_RESOURCE_RELATIONSHIP_COLLECTION_PROVIDER, function () {
            return $this->getResourceRelationshipCollectionProviderPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Glue\DocumentationGeneratorRestApiExtension\Dependency\Plugin\ResourceRelationshipCollectionProviderPluginInterface>
     */
    protected function getResourceRelationshipCollectionProviderPlugins(): array
    {
        return [];
    }
}
