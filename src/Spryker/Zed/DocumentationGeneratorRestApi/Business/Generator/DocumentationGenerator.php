<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator;

use cebe\openapi\Writer;
use Psr\Log\LoggerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourcePluginAnalyzerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Contributor\OpenApiContributorInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Merger\OpenApiMergerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Writer\DocumentationWriterInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig;
use Throwable;

class DocumentationGenerator implements DocumentationGeneratorInterface
{
    public function __construct(
        protected readonly ResourcePluginAnalyzerInterface $resourcePluginAnalyzer,
        protected readonly DocumentationWriterInterface $documentationWriter,
        protected readonly OpenApiContributorInterface $apiPlatformContributor,
        protected readonly OpenApiMergerInterface $openApiMerger,
        protected readonly DocumentationGeneratorRestApiConfig $config,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function generateDocumentation(): void
    {
        $this->documentationWriter->write(
            $this->resourcePluginAnalyzer->createRestApiDocumentationFromPlugins(),
        );

        $contribution = $this->apiPlatformContributor->contribute();
        if ($contribution === null) {
            return;
        }

        try {
            $merged = $this->openApiMerger->mergeYamlFiles(
                $this->config->getFullFileName(),
                [$contribution],
            );

            file_put_contents(
                $this->config->getFullFileName(),
                Writer::writeToYaml($merged),
            );
        } catch (Throwable $exception) {
            $this->logger->warning(
                'Failed to merge API Platform OpenAPI into REST API spec',
                ['exception' => $exception],
            );
        }
    }
}
