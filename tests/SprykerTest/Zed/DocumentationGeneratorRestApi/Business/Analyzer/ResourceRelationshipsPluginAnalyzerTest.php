<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Analyzer;

use Codeception\Test\Unit;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceRelationshipsPluginAnalyzerInterface;
use SprykerTest\Zed\DocumentationGeneratorRestApi\Business\DocumentationGeneratorRestApiTestFactory;
use SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Stub\Plugin\TestResourceRoutePlugin;
use SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Stub\Plugin\TestResourceRouteRelatedPlugin;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DocumentationGeneratorRestApi
 * @group Business
 * @group Analyzer
 * @group ResourceRelationshipsPluginAnalyzerTest
 * Add your own group annotations below this line
 */
class ResourceRelationshipsPluginAnalyzerTest extends Unit
{
    /**
     * @var string
     */
    protected const RELATIONSHIP_VALUE = 'test-resource-with-relationship';

    /**
     * @var string
     */
    protected const RELATIONSHIP_RESOURCE = 'test-resource';

    /**
     * @return void
     */
    public function testGetResourceRelationshipsWillReturnRelationshipNameForPluginWithRelationships(): void
    {
        $resourceRelationshipsPluginAnalyzer = $this->getResourceRelationshipsPluginAnalyzer();
        $plugin = new TestResourceRoutePlugin();

        $relationships = $resourceRelationshipsPluginAnalyzer->getResourceRelationshipsForResourceRoutePlugin($plugin);

        $this->assertNotEmpty($relationships);
        $this->assertCount(1, $relationships);
        $relationship = array_shift($relationships);
        $this->assertSame(static::RELATIONSHIP_VALUE, $relationship->getRelationshipResourceType());
    }

    /**
     * @return void
     */
    public function testGetResourceRelationshipsWillReturnEmptyArrayForPluginWithoutRelationships(): void
    {
        $resourceRelationshipsPluginAnalyzer = $this->getResourceRelationshipsPluginAnalyzer();
        $plugin = new TestResourceRouteRelatedPlugin();

        $relationships = $resourceRelationshipsPluginAnalyzer->getResourceRelationshipsForResourceRoutePlugin($plugin);

        $this->assertEmpty($relationships);
    }

    /**
     * @return \Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceRelationshipsPluginAnalyzerInterface
     */
    protected function getResourceRelationshipsPluginAnalyzer(): ResourceRelationshipsPluginAnalyzerInterface
    {
        return (new DocumentationGeneratorRestApiTestFactory())->createResourceRelationshipsPluginAnalyzer();
    }
}
