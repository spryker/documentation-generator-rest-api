<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Contributor;

interface OpenApiContributorInterface
{
    /**
     * Specification:
     * - Returns a contributor OpenAPI YAML string to be merged into the legacy REST API spec.
     * - Returns `null` when the contributor has nothing to add (optional integration absent,
     *   subprocess failure, empty output) so the caller can safely skip the merge.
     */
    public function contribute(): ?string;
}
