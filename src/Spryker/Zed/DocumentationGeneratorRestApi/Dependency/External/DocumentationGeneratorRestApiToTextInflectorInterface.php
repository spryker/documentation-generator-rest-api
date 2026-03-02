<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External;

interface DocumentationGeneratorRestApiToTextInflectorInterface
{
    public function classify(string $word): string;

    public function singularize(string $word): string;
}
