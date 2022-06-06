<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator;

use Generated\Shared\Transfer\AnnotationTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Generated\Shared\Transfer\SchemaComponentTransfer;
use Generated\Shared\Transfer\SchemaDataTransfer;
use Generated\Shared\Transfer\SchemaItemsComponentTransfer;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRelationshipPluginInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceRelationshipsPluginAnnotationAnalyzerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceTransferAnalyzerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Builder\SchemaBuilderInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Exception\InvalidTransferClassException;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\ResourceRelationshipProcessorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\SchemaRendererInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Storage\ResourceTransferClassNameStorageInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig;

class OpenApiSpecificationSchemaGenerator implements SchemaGeneratorInterface
{
    /**
     * @var string
     */
    protected const KEY_IS_TRANSFER = 'is_transfer';

    /**
     * @var string
     */
    protected const KEY_REST_REQUEST_PARAMETER = 'rest_request_parameter';

    /**
     * @var string
     */
    protected const KEY_TYPE = 'type';

    /**
     * @var string
     */
    protected const MESSAGE_INVALID_TRANSFER_CLASS = 'Invalid transfer class provided in plugin %s';

    /**
     * @var string
     */
    protected const PATTERN_SCHEMA_REFERENCE = '#/components/schemas/%s';

    /**
     * @var string
     */
    protected const REST_REQUEST_BODY_PARAMETER_NOT_REQUIRED = 'no';

    /**
     * @var array
     */
    protected $schemas = [];

    /**
     * @var string
     */
    protected $restErrorSchemaReference;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceTransferAnalyzerInterface
     */
    protected $resourceTransferAnalyzer;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Builder\SchemaBuilderInterface
     */
    protected $schemaBuilder;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\SchemaRendererInterface
     */
    protected $schemaRenderer;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\ResourceRelationshipProcessorInterface
     */
    protected $resourceRelationshipProcessor;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceRelationshipsPluginAnnotationAnalyzerInterface
     */
    protected ResourceRelationshipsPluginAnnotationAnalyzerInterface $resourceRelationshipsPluginAnnotationAnalyzer;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Storage\ResourceTransferClassNameStorageInterface
     */
    protected ResourceTransferClassNameStorageInterface $resourceTransferClassNameStorage;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig
     */
    protected DocumentationGeneratorRestApiConfig $documentationGeneratorRestApiConfig;

    /**
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceTransferAnalyzerInterface $resourceTransferAnalyzer
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Business\Builder\SchemaBuilderInterface $schemaBuilder
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\SchemaRendererInterface $schemaRenderer
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor\ResourceRelationshipProcessorInterface $resourceRelationshipProcessor
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceRelationshipsPluginAnnotationAnalyzerInterface $resourceRelationshipsPluginAnnotationAnalyzer
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Business\Storage\ResourceTransferClassNameStorageInterface $resourceTransferClassNameStorage
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig $documentationGeneratorRestApiConfig
     */
    public function __construct(
        ResourceTransferAnalyzerInterface $resourceTransferAnalyzer,
        SchemaBuilderInterface $schemaBuilder,
        SchemaRendererInterface $schemaRenderer,
        ResourceRelationshipProcessorInterface $resourceRelationshipProcessor,
        ResourceRelationshipsPluginAnnotationAnalyzerInterface $resourceRelationshipsPluginAnnotationAnalyzer,
        ResourceTransferClassNameStorageInterface $resourceTransferClassNameStorage,
        DocumentationGeneratorRestApiConfig $documentationGeneratorRestApiConfig
    ) {
        $this->resourceTransferAnalyzer = $resourceTransferAnalyzer;
        $this->schemaBuilder = $schemaBuilder;
        $this->schemaRenderer = $schemaRenderer;
        $this->resourceRelationshipProcessor = $resourceRelationshipProcessor;
        $this->resourceRelationshipsPluginAnnotationAnalyzer = $resourceRelationshipsPluginAnnotationAnalyzer;
        $this->resourceTransferClassNameStorage = $resourceTransferClassNameStorage;
        $this->documentationGeneratorRestApiConfig = $documentationGeneratorRestApiConfig;

        $this->addDefaultSchemas();
    }

    /**
     * @return array
     */
    public function getSchemas(): array
    {
        ksort($this->schemas);

        return $this->schemas;
    }

    /**
     * @return string
     */
    public function getRestErrorSchemaData(): string
    {
        return $this->restErrorSchemaReference;
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     *
     * @return string
     */
    public function addRequestSchemaForPlugin(ResourceRoutePluginInterface $plugin): string
    {
        /** @phpstan-var class-string<\Spryker\Shared\Kernel\Transfer\AbstractTransfer> $transferClassName */
        $transferClassName = $this->resolveTransferClassNameForPlugin($plugin);
        if (!$this->isRequestSchemaRequired($transferClassName)) {
            return '';
        }

        $requestSchemaName = $this->resourceTransferAnalyzer->createRequestSchemaNameFromTransferClassName($transferClassName);
        $requestDataSchemaName = $this->resourceTransferAnalyzer->createRequestDataSchemaNameFromTransferClassName($transferClassName);
        $requestAttributesSchemaName = $this->resourceTransferAnalyzer->createRequestAttributesSchemaNameFromTransferClassName($transferClassName);

        $this->addSchemaData($this->schemaBuilder->createRequestBaseSchema($requestSchemaName, $requestDataSchemaName));
        $this->addSchemaData($this->schemaBuilder->createRequestDataSchema($requestDataSchemaName, $requestAttributesSchemaName));
        $this->addRequestDataAttributesSchemaFromTransfer(new $transferClassName(), $requestAttributesSchemaName);

        return sprintf(static::PATTERN_SCHEMA_REFERENCE, $requestSchemaName);
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param \Generated\Shared\Transfer\AnnotationTransfer|null $annotationTransfer
     *
     * @return string
     */
    public function addResponseResourceSchemaForPlugin(ResourceRoutePluginInterface $plugin, ?AnnotationTransfer $annotationTransfer = null): string
    {
        /** @phpstan-var class-string<\Spryker\Shared\Kernel\Transfer\AbstractTransfer> $transferClassName */
        $transferClassName = $this->resolveTransferClassNameForPlugin($plugin, $annotationTransfer);

        $responseSchemaName = $this->resourceTransferAnalyzer->createResponseResourceSchemaNameFromTransferClassName($transferClassName);
        $responseDataSchemaName = $this->resourceTransferAnalyzer->createResponseResourceDataSchemaNameFromTransferClassName($transferClassName);
        $responseAttributesSchemaName = $this->resourceTransferAnalyzer->createResponseAttributesSchemaNameFromTransferClassName($transferClassName);

        $isIdNullable = $annotationTransfer ? (bool)$annotationTransfer->getIsIdNullable() : false;
        $this->addSchemaData($this->schemaBuilder->createResponseBaseSchema($responseSchemaName, $responseDataSchemaName));
        $this->addSchemaData($this->schemaBuilder->createResponseDataSchema($responseDataSchemaName, $responseAttributesSchemaName, $isIdNullable));
        $this->addResponseDataAttributesSchemaFromTransfer(new $transferClassName(), $responseAttributesSchemaName);

        $this->addAttributesSchemasFromResourceRelationshipAnnotations($plugin);
        $this->addRelationshipSchemas($plugin, $transferClassName, $responseDataSchemaName);
        $this->addIncludeSchemas($plugin, $transferClassName, $responseSchemaName);

        return sprintf(static::PATTERN_SCHEMA_REFERENCE, $responseSchemaName);
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param \Generated\Shared\Transfer\AnnotationTransfer|null $annotationTransfer
     *
     * @return string
     */
    public function addResponseCollectionSchemaForPlugin(ResourceRoutePluginInterface $plugin, ?AnnotationTransfer $annotationTransfer = null): string
    {
        /** @phpstan-var class-string<\Spryker\Shared\Kernel\Transfer\AbstractTransfer> $transferClassName */
        $transferClassName = $this->resolveTransferClassNameForPlugin($plugin, $annotationTransfer);

        $responseSchemaName = $this->resourceTransferAnalyzer->createResponseCollectionSchemaNameFromTransferClassName($transferClassName);
        $responseDataSchemaName = $this->resourceTransferAnalyzer->createResponseCollectionDataSchemaNameFromTransferClassName($transferClassName);
        $responseAttributesSchemaName = $this->resourceTransferAnalyzer->createResponseAttributesSchemaNameFromTransferClassName($transferClassName);

        $isIdNullable = $annotationTransfer ? (bool)$annotationTransfer->getIsIdNullable() : false;
        $this->addSchemaData($this->schemaBuilder->createCollectionResponseBaseSchema($responseSchemaName, $responseDataSchemaName));
        $this->addSchemaData($this->schemaBuilder->createResponseDataSchema($responseDataSchemaName, $responseAttributesSchemaName, $isIdNullable));
        $this->addResponseDataAttributesSchemaFromTransfer(new $transferClassName(), $responseAttributesSchemaName);
        $this->addAttributesSchemasFromResourceRelationshipAnnotations($plugin);
        $this->addRelationshipSchemas($plugin, $transferClassName, $responseDataSchemaName);
        $this->addIncludeSchemas($plugin, $transferClassName, $responseSchemaName);

        return sprintf(static::PATTERN_SCHEMA_REFERENCE, $responseSchemaName);
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     *
     * @return void
     */
    protected function addAttributesSchemasFromResourceRelationshipAnnotations(ResourceRoutePluginInterface $plugin): void
    {
        /** @phpstan-var array<class-string<\Spryker\Shared\Kernel\Transfer\AbstractTransfer>> $resourceAttributesClassNames */
        $resourceAttributesClassNames = $this->resourceRelationshipProcessor->getResourceAttributesClassNamesFromPlugin($plugin);

        if (!$resourceAttributesClassNames) {
            return;
        }

        foreach ($resourceAttributesClassNames as $resourceAttributesClassName) {
            $responseDataSchemaName = $this->resourceTransferAnalyzer->createResponseResourceDataSchemaNameFromTransferClassName($resourceAttributesClassName);
            $responseAttributesSchemaName = $this->resourceTransferAnalyzer->createResponseAttributesSchemaNameFromTransferClassName($resourceAttributesClassName);

            $this->addSchemaData($this->schemaBuilder->createResponseDataSchema($responseDataSchemaName, $responseAttributesSchemaName, false));
            $this->addResponseDataAttributesSchemaFromTransfer(new $resourceAttributesClassName(), $responseAttributesSchemaName);
        }
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $transfer
     * @param string $attributesSchemaName
     *
     * @return void
     */
    protected function addResponseDataAttributesSchemaFromTransfer(AbstractTransfer $transfer, string $attributesSchemaName): void
    {
        if (array_key_exists($attributesSchemaName, $this->schemas)) {
            return;
        }
        $this->schemas[$attributesSchemaName] = [];

        $transferMetadata = $this->resourceTransferAnalyzer->getTransferMetadata($transfer);
        foreach ($transferMetadata as $property) {
            if ($property[static::KEY_IS_TRANSFER]) {
                $this->validateTransfer($property[static::KEY_TYPE]);
                $schemaName = $this->resourceTransferAnalyzer->createResponseAttributesSchemaNameFromTransferClassName($property[static::KEY_TYPE]);
                /** @var \Spryker\Shared\Kernel\Transfer\AbstractTransfer $transfer */
                $transfer = new $property[static::KEY_TYPE]();
                $this->addResponseDataAttributesSchemaFromTransfer($transfer, $schemaName);
            }
        }

        $this->addSchemaData($this->schemaBuilder->createResponseDataAttributesSchema($attributesSchemaName, $transferMetadata));
    }

    /**
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $transfer
     * @param string $attributesSchemaName
     *
     * @return void
     */
    protected function addRequestDataAttributesSchemaFromTransfer(AbstractTransfer $transfer, string $attributesSchemaName): void
    {
        if (array_key_exists($attributesSchemaName, $this->schemas)) {
            return;
        }
        $this->schemas[$attributesSchemaName] = [];

        $transferMetadata = $this->resourceTransferAnalyzer->getTransferMetadata($transfer);
        foreach ($transferMetadata as $property) {
            if ($property[static::KEY_IS_TRANSFER] && $property[static::KEY_REST_REQUEST_PARAMETER] !== static::REST_REQUEST_BODY_PARAMETER_NOT_REQUIRED) {
                $this->validateTransfer($property[static::KEY_TYPE]);
                $schemaName = $this->resourceTransferAnalyzer->createRequestAttributesSchemaNameFromTransferClassName($property[static::KEY_TYPE]);
                /** @var \Spryker\Shared\Kernel\Transfer\AbstractTransfer $transfer */
                $transfer = new $property[static::KEY_TYPE]();
                $this->addRequestDataAttributesSchemaFromTransfer($transfer, $schemaName);
            }
        }

        $this->addSchemaData($this->schemaBuilder->createRequestDataAttributesSchema($attributesSchemaName, $transferMetadata));
    }

    /**
     * @return void
     */
    protected function addDefaultSchemas(): void
    {
        $this->addDefaultErrorMessageSchema();
        $this->addDefaultLinksSchema();
        $this->addDefaultRelationshipsSchema();
    }

    /**
     * @return void
     */
    protected function addDefaultErrorMessageSchema(): void
    {
        $restErrorSchemaName = $this->resourceTransferAnalyzer->createResponseAttributesSchemaNameFromTransferClassName(RestErrorMessageTransfer::class);
        $this->addResponseDataAttributesSchemaFromTransfer(new RestErrorMessageTransfer(), $restErrorSchemaName);

        $this->restErrorSchemaReference = sprintf(static::PATTERN_SCHEMA_REFERENCE, $restErrorSchemaName);
    }

    /**
     * @return void
     */
    protected function addDefaultLinksSchema(): void
    {
        $this->addSchemaData($this->schemaBuilder->createDefaultLinksSchema());
    }

    /**
     * @return void
     */
    protected function addDefaultRelationshipsSchema(): void
    {
        $this->addSchemaData($this->schemaBuilder->createDefaultRelationshipDataAttributesSchema());
        $this->addSchemaData($this->schemaBuilder->createDefaultRelationshipDataCollectionAttributesSchema());
    }

    /**
     * @param \Generated\Shared\Transfer\SchemaDataTransfer $schemaData
     *
     * @return void
     */
    protected function addSchemaData(SchemaDataTransfer $schemaData): void
    {
        $this->schemas = array_replace_recursive($this->schemas, $this->schemaRenderer->render($schemaData));
    }

    /**
     * @param \Generated\Shared\Transfer\SchemaDataTransfer $schemaData
     *
     * @return void
     */
    protected function addIncludeSchemaData(SchemaDataTransfer $schemaData): void
    {
        $renderData = $this->schemaRenderer->render($schemaData);
        foreach ($renderData as $key => $item) {
            if (!isset($this->schemas[$key])) {
                $this->schemas = array_replace_recursive($this->schemas, $renderData);

                continue;
            }
            $oneOfs = array_merge(
                $this->schemas[$key][SchemaComponentTransfer::ITEMS][SchemaItemsComponentTransfer::ONE_OF],
                $item[SchemaComponentTransfer::ITEMS][SchemaItemsComponentTransfer::ONE_OF],
            );
            $this->schemas[$key][SchemaComponentTransfer::ITEMS][SchemaItemsComponentTransfer::ONE_OF] = array_values(array_unique($oneOfs, SORT_REGULAR));
        }
    }

    /**
     * @param string $transferClassName
     *
     * @return bool
     */
    protected function isRequestSchemaRequired(string $transferClassName): bool
    {
        /** @var \Spryker\Shared\Kernel\Transfer\AbstractTransfer $transfer */
        $transfer = new $transferClassName();
        $transferMetadata = $this->resourceTransferAnalyzer->getTransferMetadata($transfer);
        foreach ($transferMetadata as $metadataParameter) {
            if ($metadataParameter[static::KEY_REST_REQUEST_PARAMETER] !== static::REST_REQUEST_BODY_PARAMETER_NOT_REQUIRED) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param \Generated\Shared\Transfer\AnnotationTransfer|null $annotationTransfer
     *
     * @return string
     */
    protected function resolveTransferClassNameForPlugin(ResourceRoutePluginInterface $plugin, ?AnnotationTransfer $annotationTransfer = null): string
    {
        $transferClassName = $annotationTransfer && $annotationTransfer->getResponseAttributesClassName()
            ? $annotationTransfer->getResponseAttributesClassName()
            : $plugin->getResourceAttributesClassName();
        $this->validatePluginTransfer($plugin, $transferClassName);

        return $transferClassName;
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param string $transferClassName
     *
     * @throws \Spryker\Zed\DocumentationGeneratorRestApi\Business\Exception\InvalidTransferClassException
     *
     * @return void
     */
    protected function validatePluginTransfer(ResourceRoutePluginInterface $plugin, string $transferClassName): void
    {
        if (!$this->resourceTransferAnalyzer->isTransferValid($transferClassName)) {
            throw new InvalidTransferClassException(sprintf(static::MESSAGE_INVALID_TRANSFER_CLASS, get_class($plugin)));
        }
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param string $transferClassName
     * @param string $responseDataSchemaName
     *
     * @return void
     */
    protected function addRelationshipSchemas(
        ResourceRoutePluginInterface $plugin,
        string $transferClassName,
        string $responseDataSchemaName
    ): void {
        $relationshipSchemaDataTransfers = $this
            ->resourceRelationshipProcessor
            ->getRelationshipSchemaDataTransfersForPlugin($plugin, $transferClassName, $responseDataSchemaName);

        if (!$relationshipSchemaDataTransfers) {
            return;
        }

        foreach ($relationshipSchemaDataTransfers as $relationshipSchemaDataTransfer) {
            $this->addSchemaData($relationshipSchemaDataTransfer);
        }

        $this->addRelationshipSchemasForNestedRelationships($plugin);
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $resourceRoutePlugin
     *
     * @return void
     */
    protected function addRelationshipSchemasForNestedRelationships(ResourceRoutePluginInterface $resourceRoutePlugin): void
    {
        if (!$this->documentationGeneratorRestApiConfig->isNestedRelationshipsEnabled()) {
            return;
        }

        $resourceRelationshipPlugins = $this
            ->resourceRelationshipProcessor
            ->getResourceRelationshipsForResourceRoutePlugin($resourceRoutePlugin);

        foreach ($resourceRelationshipPlugins as $resourceRelationshipPlugin) {
            $this->addRelationshipSchemasForNestedRelationship($resourceRelationshipPlugin, []);
        }
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRelationshipPluginInterface $resourceRelationshipPlugin
     * @param array<\Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRelationshipPluginInterface> $mappedResourceRelationshipPlugins
     *
     * @return void
     */
    protected function addRelationshipSchemasForNestedRelationship(
        ResourceRelationshipPluginInterface $resourceRelationshipPlugin,
        array $mappedResourceRelationshipPlugins
    ): void {
        if (isset($mappedResourceRelationshipPlugins[$resourceRelationshipPlugin->getRelationshipResourceType()])) {
            return;
        }

        $transferClassName = $this->findTransferClassNameByResourceRelationshipPlugin($resourceRelationshipPlugin);

        if (!$transferClassName) {
            return;
        }

        $responseDataSchemaName = $this->resourceTransferAnalyzer->createResponseResourceDataSchemaNameFromTransferClassName($transferClassName);
        $mappedResourceRelationshipPlugins[$resourceRelationshipPlugin->getRelationshipResourceType()] = $resourceRelationshipPlugin;

        $relationshipSchemaDataTransfers = $this
            ->resourceRelationshipProcessor
            ->getRelationshipSchemaDataTransfersForRelationshipPlugin($resourceRelationshipPlugin, $transferClassName, $responseDataSchemaName);

        foreach ($relationshipSchemaDataTransfers as $relationshipSchemaDataTransfer) {
            $this->addSchemaData($relationshipSchemaDataTransfer);
        }

        $resourceRelationshipPlugins = $this
            ->resourceRelationshipProcessor
            ->getResourceRelationshipsForResourceRelationshipPlugin($resourceRelationshipPlugin);

        foreach ($resourceRelationshipPlugins as $resourceRelationship) {
            $this->addRelationshipSchemasForNestedRelationship($resourceRelationship, $mappedResourceRelationshipPlugins);
        }
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRelationshipPluginInterface $resourceRelationshipPlugin
     *
     * @return string|null
     */
    protected function findTransferClassNameByResourceRelationshipPlugin(ResourceRelationshipPluginInterface $resourceRelationshipPlugin): ?string
    {
        $transferClassName = $this->resourceTransferClassNameStorage->getResourceTransferClassName(
            $resourceRelationshipPlugin->getRelationshipResourceType(),
        );

        if ($transferClassName) {
            return $transferClassName;
        }

        $pluginAnnotationsTransfer = $this
            ->resourceRelationshipsPluginAnnotationAnalyzer
            ->getResourceAttributesFromResourceRelationshipPlugin($resourceRelationshipPlugin);

        return $pluginAnnotationsTransfer->getResourceAttributesClassName();
    }

    /**
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface $plugin
     * @param string $transferClassName
     * @param string $responseSchemaName
     *
     * @return void
     */
    protected function addIncludeSchemas(
        ResourceRoutePluginInterface $plugin,
        string $transferClassName,
        string $responseSchemaName
    ): void {
        $resourceRelationships = $this
            ->resourceRelationshipProcessor
            ->getNestedResourceRelationshipsForResourceRoutePlugin($plugin);

        if (!$resourceRelationships) {
            return;
        }

        $this->addSchemaData(
            $this
                ->resourceRelationshipProcessor
                ->getIncludeBaseSchemaForPlugin($plugin, $transferClassName, $responseSchemaName),
        );

        $this->addIncludeSchemaData(
            $this
                ->resourceRelationshipProcessor
                ->getIncludeDataSchemaForPlugin($plugin, $transferClassName, $resourceRelationships),
        );
    }

    /**
     * @param string $transferClassName
     *
     * @throws \Spryker\Zed\DocumentationGeneratorRestApi\Business\Exception\InvalidTransferClassException
     *
     * @return void
     */
    protected function validateTransfer(string $transferClassName): void
    {
        if (!$this->resourceTransferAnalyzer->isTransferValid($transferClassName)) {
            throw new InvalidTransferClassException(
                sprintf('Invalid transfer %s', $transferClassName),
            );
        }
    }
}
