<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Stub\Plugin;

use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRouteCollectionInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface;
use SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Stub\RestTestAttributesTransfer;

class TestResourceRouteRelatedPlugin implements ResourceRoutePluginInterface
{
    public function configure(ResourceRouteCollectionInterface $resourceRouteCollection): ResourceRouteCollectionInterface
    {
        return $resourceRouteCollection;
    }

    public function getResourceType(): string
    {
        return 'test-resource-with-relationship';
    }

    public function getController(): string
    {
        return 'test-resource';
    }

    public function getResourceAttributesClassName(): string
    {
        return RestTestAttributesTransfer::class;
    }

    public function getModuleName(): string
    {
        return 'DocumentationGeneratorRestApi';
    }
}
