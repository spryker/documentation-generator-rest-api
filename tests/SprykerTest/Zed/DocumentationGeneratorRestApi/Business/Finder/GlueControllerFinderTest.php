<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Finder;

use Codeception\Test\Unit;
use SplFileInfo;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Finder\GlueControllerFinderInterface;
use SprykerTest\Zed\DocumentationGeneratorRestApi\Business\DocumentationGeneratorRestApiTestFactory;
use SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Stub\Plugin\TestResourceRoutePlugin;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DocumentationGeneratorRestApi
 * @group Business
 * @group Finder
 * @group GlueControllerFinderTest
 * Add your own group annotations below this line
 */
class GlueControllerFinderTest extends Unit
{
    /**
     * @var string
     */
    protected const CONTROLLER_FILE_NAME = 'TestResourceController.php';

    public function testGetGlueControllerFilesFromPluginShouldReturnArrayOfSplFileInfoObjects(): void
    {
        $controllerFinder = $this->getGlueControllerFinder([DocumentationGeneratorRestApiTestFactory::CONTROLLER_SOURCE_DIRECTORY]);

        $files = $controllerFinder->getGlueControllerFilesFromPlugin(new TestResourceRoutePlugin());

        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertInstanceOf(SplFileInfo::class, $file);
        }
    }

    public function testGetGlueControllerFilesFromPluginShouldReturnCorrectControllerFile(): void
    {
        $controllerFinder = $this->getGlueControllerFinder([DocumentationGeneratorRestApiTestFactory::CONTROLLER_SOURCE_DIRECTORY]);

        $files = $controllerFinder->getGlueControllerFilesFromPlugin(new TestResourceRoutePlugin());

        $this->assertNotEmpty($files);
        foreach ($files as $file) {
            $this->assertSame(static::CONTROLLER_FILE_NAME, $file->getFilename());
        }
    }

    public function testGetGlueControllerFilesFromPluginShouldReturnEmptyArrayIfNoExistingDirectoryIsFound(): void
    {
        $controllerFinder = $this->getGlueControllerFinder([]);

        $files = $controllerFinder->getGlueControllerFilesFromPlugin(new TestResourceRoutePlugin());

        $this->assertEmpty($files);
    }

    protected function getGlueControllerFinder(array $sourceDirectories): GlueControllerFinderInterface
    {
        return (new DocumentationGeneratorRestApiTestFactory(''))->createGlueControllerFinder($sourceDirectories);
    }
}
