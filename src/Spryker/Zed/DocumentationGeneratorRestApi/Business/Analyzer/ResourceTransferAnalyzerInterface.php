<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer;

use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

interface ResourceTransferAnalyzerInterface
{
    public function isTransferValid(string $transferClassName): bool;

    public function getTransferMetadata(AbstractTransfer $transfer): array;

    public function createRequestSchemaNameFromTransferClassName(string $transferClassName): string;

    public function createRequestDataSchemaNameFromTransferClassName(string $transferClassName): string;

    public function createRequestAttributesSchemaNameFromTransferClassName(string $transferClassName): string;

    public function createResponseResourceSchemaNameFromTransferClassName(string $transferClassName): string;

    public function createResponseResourceDataSchemaNameFromTransferClassName(string $transferClassName): string;

    public function createResponseCollectionSchemaNameFromTransferClassName(string $transferClassName): string;

    public function createResponseCollectionDataSchemaNameFromTransferClassName(string $transferClassName): string;

    public function createResponseAttributesSchemaNameFromTransferClassName(string $transferClassName): string;

    public function createResourceRelationshipSchemaNameFromTransferClassName(string $transferClassName): string;

    public function createIncludedSchemaNameFromTransferClassName(string $transferClassName): string;
}
