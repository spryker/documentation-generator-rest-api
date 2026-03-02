<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Processor;

use Generated\Shared\Transfer\AnnotationTransfer;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface;

interface HttpMethodProcessorInterface
{
    public function getGeneratedPaths(): array;

    public function getGeneratedSchemas(): array;

    public function getGeneratedSecuritySchemes(): array;

    public function getGeneratedParameters(): array;

    public function getGeneratedTags(): array;

    public function addGetResourceByIdPath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        bool $isProtected,
        string $idResource,
        ?AnnotationTransfer $annotationTransfer
    ): void;

    public function addGetResourceCollectionPath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        bool $isProtected,
        string $idResource,
        ?AnnotationTransfer $annotationTransfer
    ): void;

    public function addPostResourcePath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        bool $isProtected,
        ?AnnotationTransfer $annotationTransfer
    ): void;

    public function addPatchResourcePath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        bool $isProtected,
        ?AnnotationTransfer $annotationTransfer
    ): void;

    public function addDeleteResourcePath(
        ResourceRoutePluginInterface $plugin,
        string $resourcePath,
        bool $isProtected,
        ?AnnotationTransfer $annotationTransfer
    ): void;
}
