<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator;

use Generated\Shared\Transfer\PathMethodDataTransfer;

interface OpenApiTagGeneratorInterface
{
    public function addTag(
        PathMethodDataTransfer $pathMethodDataTransfer
    ): void;

    public function getTags(): array;
}
