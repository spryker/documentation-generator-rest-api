<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Generator;

use cebe\openapi\spec\OpenApi;
use Codeception\Test\Unit;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourcePluginAnalyzerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Contributor\OpenApiContributorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator\DocumentationGenerator;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Merger\OpenApiMergerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Writer\DocumentationWriterInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DocumentationGeneratorRestApi
 * @group Business
 * @group Generator
 * @group DocumentationGeneratorTest
 * Add your own group annotations below this line
 */
class DocumentationGeneratorTest extends Unit
{
    protected ?string $tmpFilePath = null;

    protected function tearDown(): void
    {
        if ($this->tmpFilePath !== null && file_exists($this->tmpFilePath)) {
            unlink($this->tmpFilePath);
        }
        $this->tmpFilePath = null;

        parent::tearDown();
    }

    public function testWhenContributorReturnsNullMergerIsSkippedAndLegacyFileIsLeftToWriter(): void
    {
        $tmpPath = $this->seedLegacyFile('legacy-content');

        $analyzer = $this->createMock(ResourcePluginAnalyzerInterface::class);
        $analyzer->method('createRestApiDocumentationFromPlugins')->willReturn(['paths' => []]);

        $writer = $this->createMock(DocumentationWriterInterface::class);
        $writer->expects($this->once())->method('write');

        $contributor = $this->createMock(OpenApiContributorInterface::class);
        $contributor->method('contribute')->willReturn(null);

        $merger = $this->createMock(OpenApiMergerInterface::class);
        $merger->expects($this->never())->method('mergeYamlFiles');

        $generator = new DocumentationGenerator(
            $analyzer,
            $writer,
            $contributor,
            $merger,
            $this->createConfigMock($tmpPath),
            $this->createMock(LoggerInterface::class),
        );

        $generator->generateDocumentation();

        $this->assertSame('legacy-content', file_get_contents($tmpPath));
    }

    public function testWhenContributorReturnsYamlMergerIsCalledOnceAndMergedYamlIsWrittenToConfiguredPath(): void
    {
        $tmpPath = $this->seedLegacyFile('seed');

        $analyzer = $this->createMock(ResourcePluginAnalyzerInterface::class);
        $analyzer->method('createRestApiDocumentationFromPlugins')->willReturn(['paths' => []]);

        $writer = $this->createMock(DocumentationWriterInterface::class);

        $contributor = $this->createMock(OpenApiContributorInterface::class);
        $contributor->method('contribute')->willReturn('openapi: 3.0.0');

        $mergedOpenApi = new OpenApi([
            'openapi' => '3.0.0',
            'info' => ['title' => 'Merged', 'version' => '1.0.0'],
            'paths' => [
                '/legacy' => ['get' => ['responses' => ['200' => ['description' => 'ok']]]],
                '/api-platform' => ['get' => ['responses' => ['200' => ['description' => 'ok']]]],
            ],
        ]);

        $merger = $this->createMock(OpenApiMergerInterface::class);
        $merger->expects($this->once())
            ->method('mergeYamlFiles')
            ->with($tmpPath, ['openapi: 3.0.0'])
            ->willReturn($mergedOpenApi);

        $generator = new DocumentationGenerator(
            $analyzer,
            $writer,
            $contributor,
            $merger,
            $this->createConfigMock($tmpPath),
            $this->createMock(LoggerInterface::class),
        );

        $generator->generateDocumentation();

        $written = file_get_contents($tmpPath);
        $this->assertStringContainsString('/legacy', $written);
        $this->assertStringContainsString('/api-platform', $written);
    }

    public function testWhenMergerThrowsLegacyFileIsLeftIntactAndWarningIsLogged(): void
    {
        $tmpPath = $this->seedLegacyFile('legacy-content-intact');

        $analyzer = $this->createMock(ResourcePluginAnalyzerInterface::class);
        $analyzer->method('createRestApiDocumentationFromPlugins')->willReturn(['paths' => []]);

        $writer = $this->createMock(DocumentationWriterInterface::class);

        $contributor = $this->createMock(OpenApiContributorInterface::class);
        $contributor->method('contribute')->willReturn('openapi: 3.0.0');

        $merger = $this->createMock(OpenApiMergerInterface::class);
        $merger->method('mergeYamlFiles')->willThrowException(new RuntimeException('boom'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with('Failed to merge API Platform OpenAPI into REST API spec', $this->callback(static function (array $context): bool {
                return isset($context['exception']) && $context['exception'] instanceof RuntimeException;
            }));

        $generator = new DocumentationGenerator(
            $analyzer,
            $writer,
            $contributor,
            $merger,
            $this->createConfigMock($tmpPath),
            $logger,
        );

        $generator->generateDocumentation();

        $this->assertSame('legacy-content-intact', file_get_contents($tmpPath));
    }

    protected function seedLegacyFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'doc-gen-test-');
        file_put_contents($path, $contents);
        $this->tmpFilePath = $path;

        return $path;
    }

    /**
     * @return \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createConfigMock(string $fullFileName): DocumentationGeneratorRestApiConfig
    {
        $config = $this->createMock(DocumentationGeneratorRestApiConfig::class);
        $config->method('getFullFileName')->willReturn($fullFileName);

        return $config;
    }
}
