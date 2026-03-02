<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor;

use Generated\Shared\Transfer\AnnotationTransfer;
use Generated\Shared\Transfer\PathMethodDataTransfer;
use Generated\Shared\Transfer\PathSchemaDataTransfer;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceVersionableInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceWithParentPluginInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiSpecificationParameterGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiTagGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\PathGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\SchemaGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\SecuritySchemeGeneratorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig;

class HttpMethodProcessor implements HttpMethodProcessorInterface
{
    /**
     * @var string
     */
    protected const PATTERN_SUMMARY_GET_RESOURCE = 'Get %s.';

    /**
     * @var string
     */
    protected const PATTERN_SUMMARY_GET_COLLECTION = 'Get collection of %s.';

    /**
     * @var string
     */
    protected const PATTERN_SUMMARY_POST_RESOURCE = 'Create %s.';

    /**
     * @var string
     */
    protected const PATTERN_SUMMARY_PATCH_RESOURCE = 'Update %s.';

    /**
     * @var string
     */
    protected const PATTERN_SUMMARY_DELETE_RESOURCE = 'Delete %s.';

    /**
     * @var string
     */
    protected const PATTERN_OPERATION_ID_GET_RESOURCE = 'get-%s';

    /**
     * @var string
     */
    protected const PATTERN_OPERATION_ID_GET_COLLECTION = 'get-collection-of-%s';

    /**
     * @var string
     */
    protected const PATTERN_OPERATION_ID_POST_RESOURCE = 'create-%s';

    /**
     * @var string
     */
    protected const PATTERN_OPERATION_ID_PATCH_RESOURCE = 'update-%s';

    /**
     * @var string
     */
    protected const PATTERN_OPERATION_ID_DELETE_RESOURCE = 'delete-%s';

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\PathGeneratorInterface
     */
    protected $pathGenerator;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\SchemaGeneratorInterface
     */
    protected $schemaGenerator;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\SecuritySchemeGeneratorInterface
     */
    protected $securitySchemeGenerator;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiSpecificationParameterGeneratorInterface
     */
    protected $parameterGenerator;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\OpenApiTagGeneratorInterface
     */
    protected $tagGenerator;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig
     */
    protected $config;

    public function __construct(
        PathGeneratorInterface $pathGenerator,
        SchemaGeneratorInterface $schemaGenerator,
        SecuritySchemeGeneratorInterface $securitySchemeGenerator,
        OpenApiSpecificationParameterGeneratorInterface $parameterGenerator,
        OpenApiTagGeneratorInterface $tagGenerator,
        DocumentationGeneratorRestApiConfig $config
    ) {
        $this->pathGenerator = $pathGenerator;
        $this->schemaGenerator = $schemaGenerator;
        $this->securitySchemeGenerator = $securitySchemeGenerator;
        $this->parameterGenerator = $parameterGenerator;
        $this->tagGenerator = $tagGenerator;
        $this->config = $config;
    }

    public function getGeneratedPaths(): array
    {
        return $this->pathGenerator->getPaths();
    }

    public function getGeneratedSchemas(): array
    {
        return $this->schemaGenerator->getSchemas();
    }

    public function getGeneratedSecuritySchemes(): array
    {
        return $this->securitySchemeGenerator->getSecuritySchemes();
    }

    public function getGeneratedParameters(): array
    {
        return $this->parameterGenerator->getParameters();
    }

    public function getGeneratedTags(): array
    {
        return $this->tagGenerator->getTags();
    }

    public function addGetResourceByIdPath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        bool $isProtected,
        string $idResource,
        ?AnnotationTransfer $annotationTransfer
    ): void {
        $errorSchema = $this->createPathSchemaDataTransfer($this->schemaGenerator->getRestErrorSchemaData());
        $pathDataTransfer = $this->createPathDataTransfer(
            $plugin->getResourceType(),
            $resourcePath,
            $isProtected,
            $errorSchema,
            $annotationTransfer,
            $this->getOperationId(
                static::PATTERN_OPERATION_ID_GET_RESOURCE,
                $this->getFullResource($plugin),
            ),
        );

        $this->addGetResourceById($plugin, $pathDataTransfer, $idResource, $annotationTransfer);
    }

    public function addGetResourceCollectionPath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        bool $isProtected,
        string $idResource,
        ?AnnotationTransfer $annotationTransfer
    ): void {
        $errorSchema = $this->createPathSchemaDataTransfer($this->schemaGenerator->getRestErrorSchemaData());
        $pathDataTransfer = $this->createPathDataTransfer(
            $plugin->getResourceType(),
            $resourcePath,
            $isProtected,
            $errorSchema,
            $annotationTransfer,
            $this->getOperationId(
                static::PATTERN_OPERATION_ID_GET_COLLECTION,
                $this->getFullResource($plugin),
            ),
        );

        $this->tagGenerator->addTag($pathDataTransfer);
        $this->addGetCollectionPath($plugin, $pathDataTransfer, $annotationTransfer);
    }

    public function addPostResourcePath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        bool $isProtected,
        ?AnnotationTransfer $annotationTransfer
    ): void {
        $errorSchema = $this->createPathSchemaDataTransfer($this->schemaGenerator->getRestErrorSchemaData());
        $responseSchema = $this->findResponseResourceSchema($plugin, $annotationTransfer);
        $requestSchema = $this->createPathSchemaDataTransfer($this->schemaGenerator->addRequestSchemaForPlugin($plugin));

        $pathDataTransfer = $this->createPathDataTransfer(
            $plugin->getResourceType(),
            $resourcePath,
            $isProtected,
            $errorSchema,
            $annotationTransfer,
            $this->getOperationId(
                static::PATTERN_OPERATION_ID_POST_RESOURCE,
                $this->getFullResource($plugin),
            ),
        );

        if (!$pathDataTransfer->getSummary()) {
            $pathDataTransfer->setSummary(
                $this->getDefaultMethodSummary(static::PATTERN_SUMMARY_POST_RESOURCE, $plugin->getResourceType()),
            );
        }

        $this->tagGenerator->addTag($pathDataTransfer);
        $this->pathGenerator->addPostPath($pathDataTransfer, $requestSchema, $errorSchema, $responseSchema);
    }

    public function addPatchResourcePath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        bool $isProtected,
        ?AnnotationTransfer $annotationTransfer
    ): void {
        $errorSchema = $this->createPathSchemaDataTransfer($this->schemaGenerator->getRestErrorSchemaData());
        $responseSchema = $this->findResponseResourceSchema($plugin, $annotationTransfer);
        $requestSchema = $this->createPathSchemaDataTransfer($this->schemaGenerator->addRequestSchemaForPlugin($plugin));

        $pathDataTransfer = $this->createPathDataTransfer(
            $plugin->getResourceType(),
            $resourcePath,
            $isProtected,
            $errorSchema,
            $annotationTransfer,
            $this->getOperationId(
                static::PATTERN_OPERATION_ID_PATCH_RESOURCE,
                $this->getFullResource($plugin),
            ),
        );

        if (!$pathDataTransfer->getSummary()) {
            $pathDataTransfer->setSummary(
                $this->getDefaultMethodSummary(static::PATTERN_SUMMARY_PATCH_RESOURCE, $plugin->getResourceType()),
            );
        }

        $this->tagGenerator->addTag($pathDataTransfer);
        $this->pathGenerator->addPatchPath($pathDataTransfer, $requestSchema, $errorSchema, $responseSchema);
    }

    public function addDeleteResourcePath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        bool $isProtected,
        ?AnnotationTransfer $annotationTransfer
    ): void {
        $errorSchema = $this->createPathSchemaDataTransfer($this->schemaGenerator->getRestErrorSchemaData());

        $pathDataTransfer = $this->createPathDataTransfer(
            $plugin->getResourceType(),
            $resourcePath,
            $isProtected,
            $errorSchema,
            $annotationTransfer,
            $this->getOperationId(
                static::PATTERN_OPERATION_ID_DELETE_RESOURCE,
                $this->getFullResource($plugin),
            ),
        );

        if (!$pathDataTransfer->getSummary()) {
            $pathDataTransfer->setSummary(
                $this->getDefaultMethodSummary(static::PATTERN_SUMMARY_DELETE_RESOURCE, $plugin->getResourceType()),
            );
        }

        $this->tagGenerator->addTag($pathDataTransfer);
        $this->pathGenerator->addDeletePath($pathDataTransfer, $errorSchema);
    }

    protected function addGetCollectionPath(
        ResourceRoutePluginInterface $plugin,
        PathMethodDataTransfer $pathMethodDataTransfer,
        ?AnnotationTransfer $annotationTransfer
    ): void {
        $errorSchema = $this->createPathSchemaDataTransfer($this->schemaGenerator->getRestErrorSchemaData());
        $responseSchema = $this->findResponseCollectionSchema($plugin, $annotationTransfer);

        if (!$pathMethodDataTransfer->getSummary()) {
            $pathMethodDataTransfer->setSummary(
                $this->getDefaultMethodSummary(static::PATTERN_SUMMARY_GET_COLLECTION, $pathMethodDataTransfer->getResource()),
            );
        }

        $this->tagGenerator->addTag($pathMethodDataTransfer);
        $this->pathGenerator->addGetPath($pathMethodDataTransfer, $errorSchema, $responseSchema);
    }

    protected function addGetResourceById(
        ResourceRoutePluginInterface $plugin,
        PathMethodDataTransfer $pathMethodDataTransfer,
        string $idResource,
        ?AnnotationTransfer $annotationTransfer
    ): void {
        $errorSchema = $this->createPathSchemaDataTransfer($this->schemaGenerator->getRestErrorSchemaData());
        $responseSchema = $this->findResponseResourceSchema($plugin, $annotationTransfer);

        if (!$pathMethodDataTransfer->getSummary()) {
            $pathMethodDataTransfer->setSummary(
                $this->getDefaultMethodSummary(static::PATTERN_SUMMARY_GET_RESOURCE, $pathMethodDataTransfer->getResource()),
            );
        }

        $pathMethodDataTransfer->setPath($pathMethodDataTransfer->getPath() . '/' . $idResource);

        $this->tagGenerator->addTag($pathMethodDataTransfer);
        $this->pathGenerator->addGetPath($pathMethodDataTransfer, $errorSchema, $responseSchema);
    }

    protected function createPathDataTransfer(
        string $resource,
        string $path,
        bool $isProtected,
        PathSchemaDataTransfer $errorSchema,
        ?AnnotationTransfer $annotationTransfer,
        string $operationId
    ): PathMethodDataTransfer {
        $pathDataTransfer = new PathMethodDataTransfer();
        $pathDataTransfer->setResource($resource);
        $pathDataTransfer->setPath($path);
        $pathDataTransfer->setIsProtected($isProtected);
        $pathDataTransfer->setOperationId($operationId);

        if ($annotationTransfer) {
            $pathDataTransfer->setDeprecated($annotationTransfer->getDeprecated());
            $pathDataTransfer->fromArray($annotationTransfer->modifiedToArray(), true);
            $this->addResponsesToPathData($pathDataTransfer, $errorSchema, $annotationTransfer->getResponses());
        }

        return $pathDataTransfer;
    }

    protected function createPathSchemaDataTransfer(string $schemaRef): PathSchemaDataTransfer
    {
        $schemaDataTransfer = new PathSchemaDataTransfer();
        $schemaDataTransfer->setSchemaReference($schemaRef);

        return $schemaDataTransfer;
    }

    /**
     * @param string $pattern
     * @param string $resourceType
     *
     * @return array<string>
     */
    protected function getDefaultMethodSummary(string $pattern, string $resourceType): array
    {
        return [sprintf($pattern, str_replace('-', ' ', $resourceType))];
    }

    protected function findResponseResourceSchema(
        ResourceRoutePluginInterface $plugin,
        ?AnnotationTransfer $annotationTransfer
    ): ?PathSchemaDataTransfer {
        if (!$annotationTransfer) {
            return $this->createPathSchemaDataTransfer($this->schemaGenerator->addResponseResourceSchemaForPlugin($plugin));
        }

        if ($annotationTransfer->getIsEmptyResponse()) {
            return null;
        }

        return $this->createPathSchemaDataTransfer(
            $this->schemaGenerator->addResponseResourceSchemaForPlugin($plugin, $annotationTransfer),
        );
    }

    protected function findResponseCollectionSchema(
        ResourceRoutePluginInterface $plugin,
        ?AnnotationTransfer $annotationTransfer
    ): ?PathSchemaDataTransfer {
        if (!$annotationTransfer) {
            return $this->createPathSchemaDataTransfer($this->schemaGenerator->addResponseCollectionSchemaForPlugin($plugin));
        }

        if ($annotationTransfer->getIsEmptyResponse()) {
            return null;
        }

        return $this->createPathSchemaDataTransfer(
            $this->schemaGenerator->addResponseCollectionSchemaForPlugin($plugin, $annotationTransfer),
        );
    }

    protected function addResponsesToPathData(
        PathMethodDataTransfer $pathMethodDataTransfer,
        PathSchemaDataTransfer $errorSchemaDataTransfer,
        array $responses
    ): void {
        foreach ($responses as $code => $description) {
            $responseSchemaDataTransfer = $this->isResponseCodeSuccessful($code)
                ? new PathSchemaDataTransfer()
                : clone $errorSchemaDataTransfer;

            $responseSchemaDataTransfer->setCode($code);
            $responseSchemaDataTransfer->setDescription($description);

            $pathMethodDataTransfer->addResponseSchema($responseSchemaDataTransfer);
        }
    }

    protected function isResponseCodeSuccessful(int $responseCode): bool
    {
        return $responseCode >= 200 && $responseCode < 300;
    }

    protected function getOperationId(string $pattern, string $resourceType): string
    {
        return sprintf($pattern, $resourceType);
    }

    protected function getParentResourceType(ResourceRoutePluginInterface $plugin): string
    {
        return $plugin instanceof ResourceWithParentPluginInterface ? $plugin->getParentResourceType() : '';
    }

    protected function getFullResource(ResourceRoutePluginInterface $plugin): string
    {
        $parentResourceType = $this->getParentResourceType($plugin);
        $fullResource = ($parentResourceType ? $parentResourceType . '-' : '') . $plugin->getResourceType();

        if ($plugin instanceof ResourceVersionableInterface && $this->config->getPathVersionResolving()) {
            $versionTransfer = $plugin->getVersion();
            if ($versionTransfer->getMajor()) {
                $fullResource .= '-';
                if ($this->config->getPathVersionPrefix()) {
                    $fullResource .= $this->config->getPathVersionPrefix();
                }
                $fullResource .= $versionTransfer->getMajor();

                if ($versionTransfer->getMinor()) {
                    $fullResource .= '.' . $versionTransfer->getMinor();
                }
            }
        }

        return $fullResource;
    }
}
