<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator;

use Generated\Shared\Transfer\AnnotationTransfer;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface;

interface SchemaGeneratorInterface
{
    public function addRequestSchemaForPlugin(ResourceRoutePluginInterface $plugin): string;

    public function addResponseResourceSchemaForPlugin(ResourceRoutePluginInterface $plugin, ?AnnotationTransfer $annotationTransfer = null): string;

    public function addResponseCollectionSchemaForPlugin(ResourceRoutePluginInterface $plugin, ?AnnotationTransfer $annotationTransfer = null): string;

    public function getRestErrorSchemaData(): string;

    public function getSchemas(): array;
}
