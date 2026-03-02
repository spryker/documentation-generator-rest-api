<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Stub\Plugin;

use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRouteCollectionInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceWithParentPluginInterface;
use SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Stub\RestTestAttributesTransfer;

class TestResourceRouteWithEmptyAnnotationsPlugin implements ResourceRoutePluginInterface, ResourceWithParentPluginInterface
{
    public function configure(ResourceRouteCollectionInterface $resourceRouteCollection): ResourceRouteCollectionInterface
    {
        $resourceRouteCollection->addPost('post', true);

        return $resourceRouteCollection;
    }

    public function getResourceType(): string
    {
        return 'test-resource-with-empty-annotations';
    }

    public function getController(): string
    {
        return 'test-resource-with-empty-annotations';
    }

    public function getResourceAttributesClassName(): string
    {
        return RestTestAttributesTransfer::class;
    }

    public function getParentResourceType(): string
    {
        return 'test-resource-with-get-resource-by-id';
    }

    public function getModuleName(): string
    {
        return 'DocumentationGeneratorRestApi';
    }
}
