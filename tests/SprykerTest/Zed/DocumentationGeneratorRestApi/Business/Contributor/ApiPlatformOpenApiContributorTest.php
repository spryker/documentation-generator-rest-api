<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Contributor;

use Codeception\Test\Unit;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Contributor\ApiPlatformOpenApiContributor;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Contributor\SymfonyProcessFactory;
use Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig;
use Symfony\Component\Process\Process;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DocumentationGeneratorRestApi
 * @group Business
 * @group Contributor
 * @group ApiPlatformOpenApiContributorTest
 * Add your own group annotations below this line
 */
class ApiPlatformOpenApiContributorTest extends Unit
{
    protected const string NON_EXISTENT_CLASS = 'NonExistent\\ApiPlatform\\Bundle\\NeverLoadedClass';

    public function testReturnsNullWhenDetectionClassIsNotLoaded(): void
    {
        $config = $this->createConfigMock(static::NON_EXISTENT_CLASS);
        $processFactory = $this->createMock(SymfonyProcessFactory::class);
        $processFactory->expects($this->never())->method('createGlueExportProcess');
        $logger = $this->createMock(LoggerInterface::class);

        $contributor = new ApiPlatformOpenApiContributor($config, $processFactory, $logger);

        $this->assertNull($contributor->contribute());
    }

    public function testReturnsTrimmedStdoutWhenProcessExitsSuccessfully(): void
    {
        $expectedYaml = "openapi: 3.0.0\npaths: {}\n";
        $process = $this->createProcessMock(true, $expectedYaml . "\n", 0, '');
        $process->expects($this->once())->method('setTimeout')->with(120);

        $config = $this->createConfigMock(static::class);
        $processFactory = $this->createMock(SymfonyProcessFactory::class);
        $processFactory->method('createGlueExportProcess')->willReturn($process);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');

        $contributor = new ApiPlatformOpenApiContributor($config, $processFactory, $logger);

        $this->assertSame(trim($expectedYaml), $contributor->contribute());
    }

    public function testReturnsNullAndLogsWarningWhenProcessExitsNonZero(): void
    {
        $process = $this->createProcessMock(false, '', 1, 'boom');

        $config = $this->createConfigMock(static::class);
        $processFactory = $this->createMock(SymfonyProcessFactory::class);
        $processFactory->method('createGlueExportProcess')->willReturn($process);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with('api:openapi:export non-zero exit', $this->callback(static function (array $context): bool {
                return ($context['exit_code'] ?? null) === 1 && ($context['stderr'] ?? null) === 'boom';
            }));

        $contributor = new ApiPlatformOpenApiContributor($config, $processFactory, $logger);

        $this->assertNull($contributor->contribute());
    }

    public function testReturnsNullAndLogsWarningWhenProcessRunThrows(): void
    {
        $process = $this->createMock(Process::class);
        $process->method('setTimeout')->willReturnSelf();
        $process->method('run')->willThrowException(new RuntimeException('binary missing'));

        $config = $this->createConfigMock(static::class);
        $processFactory = $this->createMock(SymfonyProcessFactory::class);
        $processFactory->method('createGlueExportProcess')->willReturn($process);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with('api:openapi:export failed to start', $this->callback(static function (array $context): bool {
                return isset($context['exception']) && $context['exception'] instanceof RuntimeException;
            }));

        $contributor = new ApiPlatformOpenApiContributor($config, $processFactory, $logger);

        $this->assertNull($contributor->contribute());
    }

    public function testReturnsNullWhenProcessOutputIsEmpty(): void
    {
        $process = $this->createProcessMock(true, "   \n", 0, '');

        $config = $this->createConfigMock(static::class);
        $processFactory = $this->createMock(SymfonyProcessFactory::class);
        $processFactory->method('createGlueExportProcess')->willReturn($process);
        $logger = $this->createMock(LoggerInterface::class);

        $contributor = new ApiPlatformOpenApiContributor($config, $processFactory, $logger);

        $this->assertNull($contributor->contribute());
    }

    /**
     * @return \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createConfigMock(string $detectionClass): DocumentationGeneratorRestApiConfig
    {
        $config = $this->createMock(DocumentationGeneratorRestApiConfig::class);
        $config->method('getApiPlatformDetectionClass')->willReturn($detectionClass);
        $config->method('getGlueConsoleBinPath')->willReturn('/tmp/glue');
        $config->method('getApiPlatformExportCommand')->willReturn('api:openapi:export -y');
        $config->method('getApiPlatformProcessTimeoutSeconds')->willReturn(120);

        return $config;
    }

    /**
     * @return \Symfony\Component\Process\Process|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProcessMock(bool $successful, string $output, int $exitCode, string $errorOutput): Process
    {
        $process = $this->createMock(Process::class);
        $process->method('run')->willReturn($exitCode);
        $process->method('isSuccessful')->willReturn($successful);
        $process->method('getOutput')->willReturn($output);
        $process->method('getExitCode')->willReturn($exitCode);
        $process->method('getErrorOutput')->willReturn($errorOutput);

        return $process;
    }
}
