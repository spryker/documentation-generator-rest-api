<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business;

use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRouteCollectionInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\GlueAnnotationAnalyzer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\GlueAnnotationAnalyzerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourcePluginAnalyzer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourcePluginAnalyzerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceRelationshipsPluginAnalyzer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceRelationshipsPluginAnalyzerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceRelationshipsPluginAnnotationAnalyzer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceRelationshipsPluginAnnotationAnalyzerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceTransferAnalyzer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceTransferAnalyzerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Builder\OpenApiSpecificationSchemaBuilder;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Builder\OpenApiSpecificationSchemaComponentBuilder;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Builder\SchemaBuilderInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Builder\SchemaComponentBuilderInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Finder\GlueControllerFinder;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Finder\GlueControllerFinderInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\DocumentationGenerator;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\DocumentationGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiSpecificationParameterGenerator;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiSpecificationParameterGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiSpecificationPathGenerator;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiSpecificationSchemaGenerator;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiSpecificationSecuritySchemeGenerator;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiTagGenerator;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiTagGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\PathGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\SchemaGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\SecuritySchemeGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\HttpMethodProcessor;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\HttpMethodProcessorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\ResourceRelationshipProcessor;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\ResourceRelationshipProcessorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\ResourceSchemaNameStorageProcessor;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\ResourceSchemaNameStorageProcessorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\ParameterSpecificationComponent;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\ParameterSpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\PathMethodSpecificationComponent;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\PathMethodSpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\PathParameterSpecificationComponent;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\PathParameterSpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\PathRequestSpecificationComponent;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\PathRequestSpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\PathResponseSpecificationComponent;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\PathResponseSpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaItemsSpecificationComponent;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaItemsSpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaPropertySpecificationComponent;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaPropertySpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaSpecificationComponent;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaSpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SecuritySchemeSpecificationComponent;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SecuritySchemeSpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\ParameterRenderer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\ParameterRendererInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\PathMethodRenderer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\PathMethodRendererInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\SchemaRenderer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\SchemaRendererInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\SecuritySchemeRenderer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\SecuritySchemeRendererInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Storage\ResourceSchemaNameStorage;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Storage\ResourceSchemaNameStorageInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Storage\ResourceTransferClassNameStorage;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Storage\ResourceTransferClassNameStorageInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Writer\DocumentationWriterInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Writer\YamlOpenApiDocumentationWriter;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToFilesystemInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToFinderInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToTextInflectorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToYamlDumperInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\Service\DocumentationGeneratorRestApiToUtilEncodingServiceInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiDependencyProvider;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig getConfig()
 */
class DocumentationGeneratorRestApiBusinessFactory extends AbstractBusinessFactory
{
    public function createDocumentationGenerator(): DocumentationGeneratorInterface
    {
        return new DocumentationGenerator(
            $this->createResourcePluginAnalyzer(),
            $this->createYamlOpenApiSpecificationWriter(),
        );
    }

    public function createOpenApiSpecificationSchemaGenerator(): SchemaGeneratorInterface
    {
        return new OpenApiSpecificationSchemaGenerator(
            $this->createResourceTransferAnalyzer(),
            $this->createOpenApiSpecificationSchemaBuilder(),
            $this->createSchemaRenderer(),
            $this->createResourceRelationshipProcessor(),
            $this->createResourceRelationshipsPluginAnnotationAnalyzer(),
            $this->createResourceTransferClassNameStorage(),
            $this->getConfig(),
        );
    }

    public function createOpenApiSpecificationSecuritySchemeGenerator(): SecuritySchemeGeneratorInterface
    {
        return new OpenApiSpecificationSecuritySchemeGenerator($this->createSecuritySchemeRenderer());
    }

    public function createOpenApiSpecificationParameterSchemeGenerator(): OpenApiSpecificationParameterGeneratorInterface
    {
        return new OpenApiSpecificationParameterGenerator($this->createParameterSchemeRenderer());
    }

    public function createOpenApiSpecificationPathGenerator(): PathGeneratorInterface
    {
        return new OpenApiSpecificationPathGenerator($this->createPathMethodRenderer());
    }

    public function createOpenApiTagGenerator(): OpenApiTagGeneratorInterface
    {
        return new OpenApiTagGenerator();
    }

    public function createYamlOpenApiSpecificationWriter(): DocumentationWriterInterface
    {
        return new YamlOpenApiDocumentationWriter(
            $this->getConfig(),
            $this->getYamlDumper(),
            $this->getFilesystem(),
        );
    }

    public function createGlueAnnotationAnalyzer(): GlueAnnotationAnalyzerInterface
    {
        return new GlueAnnotationAnalyzer(
            $this->createGlueControllerFinder(),
            $this->getUtilEncodingService(),
        );
    }

    public function createResourceRelationshipsPluginAnnotationAnalyzer(): ResourceRelationshipsPluginAnnotationAnalyzerInterface
    {
        return new ResourceRelationshipsPluginAnnotationAnalyzer(
            $this->getUtilEncodingService(),
        );
    }

    public function createResourcePluginAnalyzer(): ResourcePluginAnalyzerInterface
    {
        return new ResourcePluginAnalyzer(
            $this->createRestApiMethodProcessor(),
            $this->getResourceRoutesPluginProviderPlugins(),
            $this->createGlueAnnotationAnalyzer(),
            $this->getTextInflector(),
            $this->createResourceSchemaNameStorageProcessor(),
            $this->getConfig(),
        );
    }

    public function createResourceRelationshipsPluginAnalyzer(): ResourceRelationshipsPluginAnalyzerInterface
    {
        return new ResourceRelationshipsPluginAnalyzer(
            $this->getResourceRelationshipCollectionProviderPlugin(),
            $this->getConfig(),
        );
    }

    public function createResourceTransferAnalyzer(): ResourceTransferAnalyzerInterface
    {
        return new ResourceTransferAnalyzer();
    }

    public function createRestApiMethodProcessor(): HttpMethodProcessorInterface
    {
        return new HttpMethodProcessor(
            $this->createOpenApiSpecificationPathGenerator(),
            $this->createOpenApiSpecificationSchemaGenerator(),
            $this->createOpenApiSpecificationSecuritySchemeGenerator(),
            $this->createOpenApiSpecificationParameterSchemeGenerator(),
            $this->createOpenApiTagGenerator(),
            $this->getConfig(),
        );
    }

    public function createPathMethodRenderer(): PathMethodRendererInterface
    {
        return new PathMethodRenderer(
            $this->createPathMethodSpecificationComponent(),
            $this->createPathResponseSpecificationComponent(),
            $this->createPathRequestSpecificationComponent(),
            $this->createPathParameterSpecificationComponent(),
        );
    }

    public function createSchemaRenderer(): SchemaRendererInterface
    {
        return new SchemaRenderer(
            $this->createSchemaSpecificationComponent(),
            $this->createSchemaPropertySpecificationComponent(),
            $this->createSchemaItemsSpecificationComponent(),
        );
    }

    public function createSecuritySchemeRenderer(): SecuritySchemeRendererInterface
    {
        return new SecuritySchemeRenderer($this->createSecuritySchemeSpecificationComponent());
    }

    public function createParameterSchemeRenderer(): ParameterRendererInterface
    {
        return new ParameterRenderer($this->createParameterSpecificationComponent());
    }

    public function createPathMethodSpecificationComponent(): PathMethodSpecificationComponentInterface
    {
        return new PathMethodSpecificationComponent();
    }

    public function createPathParameterSpecificationComponent(): PathParameterSpecificationComponentInterface
    {
        return new PathParameterSpecificationComponent();
    }

    public function createPathRequestSpecificationComponent(): PathRequestSpecificationComponentInterface
    {
        return new PathRequestSpecificationComponent();
    }

    public function createPathResponseSpecificationComponent(): PathResponseSpecificationComponentInterface
    {
        return new PathResponseSpecificationComponent();
    }

    public function createSchemaPropertySpecificationComponent(): SchemaPropertySpecificationComponentInterface
    {
        return new SchemaPropertySpecificationComponent();
    }

    public function createSchemaItemsSpecificationComponent(): SchemaItemsSpecificationComponentInterface
    {
        return new SchemaItemsSpecificationComponent();
    }

    public function createSchemaSpecificationComponent(): SchemaSpecificationComponentInterface
    {
        return new SchemaSpecificationComponent();
    }

    public function createParameterSpecificationComponent(): ParameterSpecificationComponentInterface
    {
        return new ParameterSpecificationComponent();
    }

    public function createSecuritySchemeSpecificationComponent(): SecuritySchemeSpecificationComponentInterface
    {
        return new SecuritySchemeSpecificationComponent();
    }

    public function createGlueControllerFinder(): GlueControllerFinderInterface
    {
        return new GlueControllerFinder(
            $this->getFinder(),
            $this->getTextInflector(),
            $this->getConfig()->getAnnotationSourceDirectories(),
        );
    }

    public function createResourceRelationshipProcessor(): ResourceRelationshipProcessorInterface
    {
        return new ResourceRelationshipProcessor(
            $this->createResourceRelationshipsPluginAnalyzer(),
            $this->createResourceTransferAnalyzer(),
            $this->createOpenApiSpecificationSchemaBuilder(),
            $this->createResourceRelationshipsPluginAnnotationAnalyzer(),
        );
    }

    public function createResourceSchemaNameStorage(): ResourceSchemaNameStorageInterface
    {
        return new ResourceSchemaNameStorage();
    }

    public function createOpenApiSpecificationSchemaBuilder(): SchemaBuilderInterface
    {
        return new OpenApiSpecificationSchemaBuilder($this->createOpenApiSpecificationSchemaComponentBuilder());
    }

    public function createOpenApiSpecificationSchemaComponentBuilder(): SchemaComponentBuilderInterface
    {
        return new OpenApiSpecificationSchemaComponentBuilder(
            $this->createResourceTransferAnalyzer(),
            $this->createResourceSchemaNameStorage(),
        );
    }

    public function createResourceSchemaNameStorageProcessor(): ResourceSchemaNameStorageProcessorInterface
    {
        return new ResourceSchemaNameStorageProcessor(
            $this->createResourceSchemaNameStorage(),
            $this->createResourceTransferAnalyzer(),
            $this->createResourceRelationshipsPluginAnalyzer(),
            $this->createGlueAnnotationAnalyzer(),
            $this->createResourceRelationshipsPluginAnnotationAnalyzer(),
            $this->createResourceTransferClassNameStorage(),
        );
    }

    public function createResourceTransferClassNameStorage(): ResourceTransferClassNameStorageInterface
    {
        return new ResourceTransferClassNameStorage();
    }

    public function getUtilEncodingService(): DocumentationGeneratorRestApiToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(DocumentationGeneratorRestApiDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    public function getYamlDumper(): DocumentationGeneratorRestApiToYamlDumperInterface
    {
        return $this->getProvidedDependency(DocumentationGeneratorRestApiDependencyProvider::YAML_DUMPER);
    }

    public function getFilesystem(): DocumentationGeneratorRestApiToFilesystemInterface
    {
        return $this->getProvidedDependency(DocumentationGeneratorRestApiDependencyProvider::FILESYSTEM);
    }

    public function getFinder(): DocumentationGeneratorRestApiToFinderInterface
    {
        return $this->getProvidedDependency(DocumentationGeneratorRestApiDependencyProvider::FINDER);
    }

    public function getTextInflector(): DocumentationGeneratorRestApiToTextInflectorInterface
    {
        return $this->getProvidedDependency(DocumentationGeneratorRestApiDependencyProvider::TEXT_INFLECTOR);
    }

    public function getResourceRouteCollection(): ResourceRouteCollectionInterface
    {
        return $this->getProvidedDependency(DocumentationGeneratorRestApiDependencyProvider::COLLECTION_RESOURCE_ROUTE);
    }

    /**
     * @return array<\Spryker\Glue\DocumentationGeneratorRestApiExtension\Dependency\Plugin\ResourceRoutePluginsProviderPluginInterface>
     */
    public function getResourceRoutesPluginProviderPlugins(): array
    {
        return $this->getProvidedDependency(DocumentationGeneratorRestApiDependencyProvider::PLUGIN_RESOURCE_ROUTE_PLUGIN_PROVIDERS);
    }

    /**
     * @return array<\Spryker\Glue\DocumentationGeneratorRestApiExtension\Dependency\Plugin\ResourceRelationshipCollectionProviderPluginInterface>
     */
    public function getResourceRelationshipCollectionProviderPlugin(): array
    {
        return $this->getProvidedDependency(DocumentationGeneratorRestApiDependencyProvider::PLUGIN_RESOURCE_RELATIONSHIP_COLLECTION_PROVIDER);
    }
}
