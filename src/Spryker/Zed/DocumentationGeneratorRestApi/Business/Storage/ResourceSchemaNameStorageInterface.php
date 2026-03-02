<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Storage;

interface ResourceSchemaNameStorageInterface
{
    public function addResourceSchemaName(string $resourceType, string $responseAttributesSchemaName): void;

    public function getResourceSchemaNameByResourceType(string $resourceType): string;
}
