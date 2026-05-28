<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Merger;

use cebe\openapi\spec\OpenApi;

interface OpenApiMergerInterface
{
    /**
     * Specification:
     * - Reads the legacy OpenAPI YAML from disk and unions every contributor YAML into it.
     * - `paths`, `components.schemas`, `components.securitySchemes`, `components.parameters`,
     *   `tags`, and `servers` are unioned across all sources.
     * - `info` is sourced from the legacy document (canonical title / version / contact).
     * - On schema collision: identical content is deduped; differing content is kept under a
     *   prefixed name and contributor `$ref` strings are rewritten to match.
     * - On path / server / tag collision the legacy entry wins.
     * - Throws `\InvalidArgumentException` when contributor YAML cannot be parsed.
     *
     * @param array<string> $contributorYamls
     *
     * @throws \InvalidArgumentException
     */
    public function mergeYamlFiles(string $legacyYamlPath, array $contributorYamls): OpenApi;
}
