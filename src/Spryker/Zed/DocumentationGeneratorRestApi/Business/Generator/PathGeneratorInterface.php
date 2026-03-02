<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator;

use Generated\Shared\Transfer\PathMethodDataTransfer;
use Generated\Shared\Transfer\PathSchemaDataTransfer;

interface PathGeneratorInterface
{
    public function addGetPath(
        PathMethodDataTransfer $pathMethodDataTransfer,
        PathSchemaDataTransfer $errorSchemaDataTransfer,
        ?PathSchemaDataTransfer $responseSchemaDataTransfer
    ): void;

    public function addPostPath(
        PathMethodDataTransfer $pathMethodDataTransfer,
        PathSchemaDataTransfer $requestSchemaDataTransfer,
        PathSchemaDataTransfer $errorSchemaDataTransfer,
        ?PathSchemaDataTransfer $responseSchemaDataTransfer
    ): void;

    public function addPatchPath(
        PathMethodDataTransfer $pathMethodDataTransfer,
        PathSchemaDataTransfer $requestSchemaDataTransfer,
        PathSchemaDataTransfer $errorSchemaDataTransfer,
        ?PathSchemaDataTransfer $responseSchemaDataTransfer
    ): void;

    public function addDeletePath(
        PathMethodDataTransfer $pathMethodDataTransfer,
        PathSchemaDataTransfer $errorSchemaDataTransfer
    ): void;

    public function getPaths(): array;
}
