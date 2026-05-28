<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Contributor;

use Psr\Log\LoggerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig;
use Throwable;

class ApiPlatformOpenApiContributor implements OpenApiContributorInterface
{
    public function __construct(
        protected readonly DocumentationGeneratorRestApiConfig $config,
        protected readonly SymfonyProcessFactory $processFactory,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function contribute(): ?string
    {
        if (!class_exists($this->config->getApiPlatformDetectionClass())) {
            return null;
        }

        $process = $this->processFactory->createGlueExportProcess(
            $this->config->getGlueConsoleBinPath(),
            $this->config->getApiPlatformExportCommand(),
            APPLICATION_ROOT_DIR,
        );
        $process->setTimeout($this->config->getApiPlatformProcessTimeoutSeconds());

        try {
            $process->run();
        } catch (Throwable $exception) {
            $this->logger->warning('api:openapi:export failed to start', ['exception' => $exception]);

            return null;
        }

        if (!$process->isSuccessful()) {
            $this->logger->warning('api:openapi:export non-zero exit', [
                'exit_code' => $process->getExitCode(),
                'stderr' => $process->getErrorOutput(),
            ]);

            return null;
        }

        $yaml = trim($process->getOutput());

        return $yaml === '' ? null : $yaml;
    }
}
