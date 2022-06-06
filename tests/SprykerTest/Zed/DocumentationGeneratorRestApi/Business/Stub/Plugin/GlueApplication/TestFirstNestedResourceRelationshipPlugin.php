<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Stub\Plugin\GlueApplication;

use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRelationshipPluginInterface;

/**
 * @Glue({
 *     "resourceAttributesClassName": "SprykerTest\\Zed\\DocumentationGeneratorRestApi\\Business\\Stub\\RestTestFirstNestedResourceRelationshipAttributesTransfer"
 * })
 */
class TestFirstNestedResourceRelationshipPlugin implements ResourceRelationshipPluginInterface
{
    /**
     * @param array<\Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface> $resources
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return void
     */
    public function addResourceRelationships(array $resources, RestRequestInterface $restRequest): void
    {
    }

    /**
     * @return string
     */
    public function getRelationshipResourceType(): string
    {
        return 'test-first-nested-resource';
    }
}
