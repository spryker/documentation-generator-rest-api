<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer;

use Generated\Shared\Transfer\AnnotationTransfer;
use Generated\Shared\Transfer\PathAnnotationsTransfer;
use Spryker\Glue\GlueApplication\Rest\Collection\ResourceRouteCollection;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceVersionableInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceWithParentPluginInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\HttpMethodProcessorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\ResourceSchemaNameStorageProcessorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToTextInflectorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig;
use Symfony\Component\HttpFoundation\Request;

class ResourcePluginAnalyzer implements ResourcePluginAnalyzerInterface
{
    /**
     * @var string
     */
    protected const KEY_IS_PROTECTED = 'is_protected';

    /**
     * @var string
     */
    protected const KEY_NAME = 'name';

    /**
     * @var string
     */
    protected const KEY_ID = 'id';

    /**
     * @var string
     */
    protected const KEY_PARENT = 'parent';

    /**
     * @var string
     */
    protected const KEY_PATHS = 'paths';

    /**
     * @var string
     */
    protected const KEY_TAGS = 'tags';

    /**
     * @var string
     */
    protected const KEY_SCHEMAS = 'schemas';

    /**
     * @var string
     */
    protected const KEY_SECURITY_SCHEMES = 'securitySchemes';

    /**
     * @var string
     */
    protected const KEY_PARAMETERS = 'parameters';

    /**
     * @var string
     */
    protected const PATTERN_PATH_WITH_PARENT = '/%s/%s%s';

    /**
     * @var string
     */
    protected const PATTERN_PATH_ID = '{%sId}';

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\HttpMethodProcessorInterface
     */
    protected $httpMethodProcessor;

    /**
     * @var \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRouteCollectionInterface
     */
    protected $resourceRouteCollection;

    /**
     * @var array<\Spryker\Glue\DocumentationGeneratorRestApiExtension\Dependency\Plugin\ResourceRoutePluginsProviderPluginInterface>
     */
    protected $resourceRoutesPluginsProviderPlugins;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\GlueAnnotationAnalyzerInterface
     */
    protected $glueAnnotationsAnalyser;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToTextInflectorInterface
     */
    protected $textInflector;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\ResourceSchemaNameStorageProcessorInterface
     */
    protected $resourceSchemaNameStorageProcessor;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig
     */
    protected $config;

    /**
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\HttpMethodProcessorInterface $httpMethodProcessor
     * @param array<\Spryker\Glue\DocumentationGeneratorRestApiExtension\Dependency\Plugin\ResourceRoutePluginsProviderPluginInterface> $resourceRoutesPluginsProviderPlugins
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\GlueAnnotationAnalyzerInterface $glueAnnotationsAnalyser
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToTextInflectorInterface $textInflector
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\ResourceSchemaNameStorageProcessorInterface $resourceSchemaNameStorageProcessor
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig $config
     */
    public function __construct(
        HttpMethodProcessorInterface $httpMethodProcessor,
        array $resourceRoutesPluginsProviderPlugins,
        GlueAnnotationAnalyzerInterface $glueAnnotationsAnalyser,
        DocumentationGeneratorRestApiToTextInflectorInterface $textInflector,
        ResourceSchemaNameStorageProcessorInterface $resourceSchemaNameStorageProcessor,
        DocumentationGeneratorRestApiConfig $config
    ) {
        $this->httpMethodProcessor = $httpMethodProcessor;
        $this->resourceRoutesPluginsProviderPlugins = $resourceRoutesPluginsProviderPlugins;
        $this->glueAnnotationsAnalyser = $glueAnnotationsAnalyser;
        $this->textInflector = $textInflector;
        $this->resourceSchemaNameStorageProcessor = $resourceSchemaNameStorageProcessor;
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function createRestApiDocumentationFromPlugins(): array
    {
        $this->addAllPluginResourceTypesToStorage();

        foreach ($this->resourceRoutesPluginsProviderPlugins as $resourceRoutesPluginsProviderPlugin) {
            foreach ($resourceRoutesPluginsProviderPlugin->getResourceRoutePlugins() as $plugin) {
                $pathAnnotationsTransfer = $this->glueAnnotationsAnalyser->getResourceParametersFromPlugin($plugin);
                $this->resourceRouteCollection = new ResourceRouteCollection();
                $this->resourceRouteCollection = $plugin->configure($this->resourceRouteCollection);

                $this->processMethods(
                    $plugin,
                    $pathAnnotationsTransfer,
                    $this->getParentResource($plugin, $resourceRoutesPluginsProviderPlugin->getResourceRoutePlugins()),
                );
            }
        }

        return [
            static::KEY_PATHS => $this->httpMethodProcessor->getGeneratedPaths(),
            static::KEY_SCHEMAS => $this->httpMethodProcessor->getGeneratedSchemas(),
            static::KEY_SECURITY_SCHEMES => $this->httpMethodProcessor->getGeneratedSecuritySchemes(),
            static::KEY_PARAMETERS => $this->httpMethodProcessor->getGeneratedParameters(),
            static::KEY_TAGS => $this->httpMethodProcessor->getGeneratedTags(),
        ];
    }

    /**
     * @return void
     */
    protected function addAllPluginResourceTypesToStorage(): void
    {
        foreach ($this->resourceRoutesPluginsProviderPlugins as $resourceRoutesPluginsProviderPlugin) {
            foreach ($resourceRoutesPluginsProviderPlugin->getResourceRoutePlugins() as $plugin) {
                $this->resourceSchemaNameStorageProcessor->addResourceSchemaNamesToStorage($plugin);
            }
        }
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param \Generated\Shared\Transfer\PathAnnotationsTransfer $pathAnnotationsTransfer
     * @param array|null $parentResource
     *
     * @return void
     */
    protected function processMethods(ResourceRoutePluginInterface $plugin, PathAnnotationsTransfer $pathAnnotationsTransfer, ?array $parentResource): void
    {
        $version = '';
        if ($plugin instanceof ResourceVersionableInterface && $this->config->getPathVersionResolving()) {
            $versionTransfer = $plugin->getVersion();
            if ($versionTransfer->getMajor()) {
                if ($this->config->getPathVersionPrefix()) {
                    $version .= $this->config->getPathVersionPrefix();
                }

                $version .= $versionTransfer->getMajor();

                if ($versionTransfer->getMinor()) {
                    $version .= '.' . $versionTransfer->getMinor();
                }

                $version .= '/';
            }
        }

        $resourcePath = sprintf(
            '/%s%s',
            $version,
            $plugin->getResourceType(),
        );

        $resourcePathWithParent = sprintf(
            '/%s%s',
            $version,
            ltrim($this->parseParentToPath('/' . $plugin->getResourceType(), $parentResource), '/'),
        );

        $this->processGetResourceByIdPath($plugin, $resourcePathWithParent, $pathAnnotationsTransfer->getGetResourceById());
        $this->processGetResourceCollectionPath($plugin, $resourcePathWithParent, $pathAnnotationsTransfer->getGetCollection());
        $this->processPostResourcePath($plugin, $resourcePathWithParent, $pathAnnotationsTransfer->getPost());
        $this->processPatchResourcePath($plugin, $resourcePath, $pathAnnotationsTransfer->getPatch());
        $this->processDeleteResourcePath($plugin, $resourcePath, $pathAnnotationsTransfer->getDelete());
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param string $resourcePath
     * @param \Generated\Shared\Transfer\AnnotationTransfer|null $annotationTransfer
     *
     * @return void
     */
    protected function processGetResourceByIdPath(ResourceRoutePluginInterface $plugin, string $resourcePath, ?AnnotationTransfer $annotationTransfer): void
    {
        if (!$annotationTransfer || !$this->resourceRouteCollection->has(Request::METHOD_GET)) {
            return;
        }

        $this->httpMethodProcessor->addGetResourceByIdPath(
            $plugin,
            $resourcePath,
            $this->resourceRouteCollection->get(Request::METHOD_GET)[static::KEY_IS_PROTECTED],
            $this->getResourceIdFromResourceType($plugin->getResourceType()),
            $annotationTransfer,
        );
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param string $resourcePath
     * @param \Generated\Shared\Transfer\AnnotationTransfer|null $annotationTransfer
     *
     * @return void
     */
    protected function processGetResourceCollectionPath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        ?AnnotationTransfer $annotationTransfer
    ): void {
        if (!$annotationTransfer || !$this->resourceRouteCollection->has(Request::METHOD_GET)) {
            return;
        }

        $this->httpMethodProcessor->addGetResourceCollectionPath(
            $plugin,
            $resourcePath,
            $this->resourceRouteCollection->get(Request::METHOD_GET)[static::KEY_IS_PROTECTED],
            $this->getResourceIdFromResourceType($plugin->getResourceType()),
            $annotationTransfer,
        );
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param string $resourcePath
     * @param \Generated\Shared\Transfer\AnnotationTransfer|null $annotationTransfer
     *
     * @return void
     */
    protected function processPostResourcePath(ResourceRoutePluginInterface $plugin, string $resourcePath, ?AnnotationTransfer $annotationTransfer): void
    {
        if (!$this->resourceRouteCollection->has(Request::METHOD_POST)) {
            return;
        }

        $this->httpMethodProcessor->addPostResourcePath(
            $plugin,
            $resourcePath,
            $this->resourceRouteCollection->get(Request::METHOD_POST)[static::KEY_IS_PROTECTED],
            $annotationTransfer,
        );
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param string $resourcePath
     * @param \Generated\Shared\Transfer\AnnotationTransfer|null $annotationTransfer
     *
     * @return void
     */
    protected function processPatchResourcePath(ResourceRoutePluginInterface $plugin, string $resourcePath, ?AnnotationTransfer $annotationTransfer): void
    {
        if (!$this->resourceRouteCollection->has(Request::METHOD_PATCH)) {
            return;
        }

        $this->httpMethodProcessor->addPatchResourcePath(
            $plugin,
            $resourcePath . '/' . $this->getResourceIdFromResourceType($plugin->getResourceType()),
            $this->resourceRouteCollection->get(Request::METHOD_PATCH)[static::KEY_IS_PROTECTED],
            $annotationTransfer,
        );
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param string $resourcePath
     * @param \Generated\Shared\Transfer\AnnotationTransfer|null $annotationTransfer
     *
     * @return void
     */
    protected function processDeleteResourcePath(ResourceRoutePluginInterface $plugin, string $resourcePath, ?AnnotationTransfer $annotationTransfer): void
    {
        if (!$this->resourceRouteCollection->has(Request::METHOD_DELETE)) {
            return;
        }

        $this->httpMethodProcessor->addDeleteResourcePath(
            $plugin,
            $resourcePath . '/' . $this->getResourceIdFromResourceType($plugin->getResourceType()),
            $this->resourceRouteCollection->get(Request::METHOD_DELETE)[static::KEY_IS_PROTECTED],
            $annotationTransfer,
        );
    }

    /**
     * @param string $path
     * @param array|null $parent
     *
     * @return string
     */
    protected function parseParentToPath(string $path, ?array $parent): string
    {
        if (!$parent) {
            return $path;
        }

        return $this->parseParentToPath(
            sprintf(static::PATTERN_PATH_WITH_PARENT, $parent[static::KEY_NAME], $parent[static::KEY_ID], $path),
            $parent[static::KEY_PARENT],
        );
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param array $pluginsStack
     *
     * @return array|null
     */
    protected function getParentResource(ResourceRoutePluginInterface $plugin, array $pluginsStack): ?array
    {
        if ($plugin instanceof ResourceWithParentPluginInterface) {
            $parent = [];
            foreach ($pluginsStack as $parentPlugin) {
                if ($plugin->getParentResourceType() === $parentPlugin->getResourceType()) {
                    $parent = [
                        static::KEY_NAME => $parentPlugin->getResourceType(),
                        static::KEY_ID => $this->getResourceIdFromResourceType($parentPlugin->getResourceType()),
                        static::KEY_PARENT => $this->getParentResource($parentPlugin, $pluginsStack),
                    ];

                    break;
                }
            }

            return $parent;
        }

        return null;
    }

    /**
     * @param string $resourceType
     *
     * @return string
     */
    protected function getResourceIdFromResourceType(string $resourceType): string
    {
        $resourceTypeExploded = explode('-', $resourceType);
        $resourceTypeCamelCased = array_map(function ($value) {
            return ucfirst($this->textInflector->singularize($value));
        }, $resourceTypeExploded);

        return sprintf(static::PATTERN_PATH_ID, lcfirst(implode('', $resourceTypeCamelCased)));
    }
}
