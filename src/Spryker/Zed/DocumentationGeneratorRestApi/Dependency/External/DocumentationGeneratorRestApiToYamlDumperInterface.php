<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External;

interface DocumentationGeneratorRestApiToYamlDumperInterface
{
    /**
     * @var int
     */
    public const YAML_DEFAULT_INLINE = 2;

    /**
     * @var int
     */
    public const YAML_DEFAULT_INDENT = 4;

    /**
     * @var int
     */
    public const YAML_DEFAULT_FLAG = 0;

    /**
     * @param mixed $input
     * @param int-mask-of<\Symfony\Component\Yaml\Yaml::DUMP_*> $flags
     *
     * @return string
     */
    // phpcs:ignore Spryker.Commenting.DocBlockParamAllowDefaultValue.Typehint
    public function dump(
        $input,
        int $inline = self::YAML_DEFAULT_INLINE,
        int $indent = self::YAML_DEFAULT_INDENT,
        int $flags = self::YAML_DEFAULT_FLAG
    ): string;
}
