<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Generator;

use ArrayObject;
use Generated\Shared\Transfer\PathMethodDataTransfer;
use Generated\Shared\Transfer\PathSchemaDataTransfer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\PathMethodRendererInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OpenApiSpecificationPathGenerator implements PathGeneratorInterface
{
    /**
     * @var string
     */
    protected const DESCRIPTION_DEFAULT_REQUEST = 'Expected request body.';

    /**
     * @var string
     */
    protected const DESCRIPTION_DEFAULT_RESPONSE = 'Expected response to a bad request.';

    /**
     * @var string
     */
    protected const DESCRIPTION_SUCCESSFUL_RESPONSE = 'Expected response to a valid request.';

    /**
     * @var string
     */
    protected const KEY_DEFAULT = 'default';

    /**
     * @var array
     */
    protected $paths = [];

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\PathMethodRendererInterface
     */
    protected $pathMethodRenderer;

    public function __construct(PathMethodRendererInterface $pathMethodRenderer)
    {
        $this->pathMethodRenderer = $pathMethodRenderer;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function addGetPath(
        PathMethodDataTransfer $pathMethodDataTransfer,
        PathSchemaDataTransfer $errorSchemaDataTransfer,
        ?PathSchemaDataTransfer $responseSchemaDataTransfer
    ): void {
        if (!$responseSchemaDataTransfer) {
            $responseSchemaDataTransfer = new PathSchemaDataTransfer();
        }

        $responseSchemaDataTransfer = $this->addDefaultSuccessResponseToResponseSchemaDataTransfer(
            $pathMethodDataTransfer,
            $responseSchemaDataTransfer,
            (string)Response::HTTP_OK,
        );
        if ($responseSchemaDataTransfer) {
            $pathMethodDataTransfer->addResponseSchema($responseSchemaDataTransfer);
        }

        $errorSchemaDataTransfer = $this->addDefaultErrorToErrorSchemaDataTransfer($errorSchemaDataTransfer);
        $pathMethodDataTransfer->addResponseSchema($errorSchemaDataTransfer);

        $pathMethodDataTransfer->setMethod(strtolower(Request::METHOD_GET));

        $this->addPath($pathMethodDataTransfer);
    }

    public function addPostPath(
        PathMethodDataTransfer $pathMethodDataTransfer,
        PathSchemaDataTransfer $requestSchemaDataTransfer,
        PathSchemaDataTransfer $errorSchemaDataTransfer,
        ?PathSchemaDataTransfer $responseSchemaDataTransfer
    ): void {
        if (!$responseSchemaDataTransfer) {
            $responseSchemaDataTransfer = new PathSchemaDataTransfer();
        }
        $responseSchemaDataTransfer = $this->addDefaultSuccessResponseToResponseSchemaDataTransfer(
            $pathMethodDataTransfer,
            $responseSchemaDataTransfer,
            (string)Response::HTTP_CREATED,
        );
        if ($responseSchemaDataTransfer) {
            $pathMethodDataTransfer->addResponseSchema($responseSchemaDataTransfer);
        }

        $errorSchemaDataTransfer = $this->addDefaultErrorToErrorSchemaDataTransfer($errorSchemaDataTransfer);
        $pathMethodDataTransfer->addResponseSchema($errorSchemaDataTransfer);

        $pathMethodDataTransfer->setMethod(strtolower(Request::METHOD_POST));

        if ($requestSchemaDataTransfer->getSchemaReference()) {
            $requestSchemaDataTransfer->setDescription(static::DESCRIPTION_DEFAULT_REQUEST);
            $pathMethodDataTransfer->setRequestSchema($requestSchemaDataTransfer);
        }

        $this->addPath($pathMethodDataTransfer);
    }

    public function addPatchPath(
        PathMethodDataTransfer $pathMethodDataTransfer,
        PathSchemaDataTransfer $requestSchemaDataTransfer,
        PathSchemaDataTransfer $errorSchemaDataTransfer,
        ?PathSchemaDataTransfer $responseSchemaDataTransfer
    ): void {
        if (!$responseSchemaDataTransfer) {
            $responseSchemaDataTransfer = new PathSchemaDataTransfer();
        }

        $responseSchemaDataTransfer = $this->addDefaultSuccessResponseToResponseSchemaDataTransfer(
            $pathMethodDataTransfer,
            $responseSchemaDataTransfer,
            (string)Response::HTTP_OK,
        );
        if ($responseSchemaDataTransfer) {
            $pathMethodDataTransfer->addResponseSchema($responseSchemaDataTransfer);
        }

        $errorSchemaDataTransfer = $this->addDefaultErrorToErrorSchemaDataTransfer($errorSchemaDataTransfer);
        $pathMethodDataTransfer->addResponseSchema($errorSchemaDataTransfer);

        $pathMethodDataTransfer->setMethod(strtolower(Request::METHOD_PATCH));

        if ($requestSchemaDataTransfer->getSchemaReference()) {
            $requestSchemaDataTransfer->setDescription(static::DESCRIPTION_DEFAULT_REQUEST);
            $pathMethodDataTransfer->setRequestSchema($requestSchemaDataTransfer);
        }

        $this->addPath($pathMethodDataTransfer);
    }

    public function addDeletePath(
        PathMethodDataTransfer $pathMethodDataTransfer,
        PathSchemaDataTransfer $errorSchemaDataTransfer
    ): void {
        $responseSchemaDataTransfer = $this->addDefaultSuccessResponseToResponseSchemaDataTransfer(
            $pathMethodDataTransfer,
            new PathSchemaDataTransfer(),
            (string)Response::HTTP_NO_CONTENT,
        );
        if ($responseSchemaDataTransfer) {
            $pathMethodDataTransfer->addResponseSchema($responseSchemaDataTransfer);
        }

        $errorSchemaDataTransfer = $this->addDefaultErrorToErrorSchemaDataTransfer($errorSchemaDataTransfer);
        $pathMethodDataTransfer->addResponseSchema($errorSchemaDataTransfer);

        $pathMethodDataTransfer->setMethod(strtolower(Request::METHOD_DELETE));

        $this->addPath($pathMethodDataTransfer);
    }

    protected function addPath(PathMethodDataTransfer $pathMethodDataTransfer): void
    {
        $this->paths = array_replace_recursive($this->paths, $this->pathMethodRenderer->render($pathMethodDataTransfer));
    }

    protected function getResponseStatusCode(PathMethodDataTransfer $pathMethodDataTransfer, string $defaultMethodStatusCode): string
    {
        return $pathMethodDataTransfer->getIsEmptyResponse() ? (string)Response::HTTP_NO_CONTENT : $defaultMethodStatusCode;
    }

    protected function addDefaultErrorToErrorSchemaDataTransfer(PathSchemaDataTransfer $errorSchemaDataTransfer): PathSchemaDataTransfer
    {
        $errorSchemaDataTransfer->setCode(static::KEY_DEFAULT);
        $errorSchemaDataTransfer->setDescription(static::DESCRIPTION_DEFAULT_RESPONSE);

        return $errorSchemaDataTransfer;
    }

    protected function addDefaultSuccessResponseToResponseSchemaDataTransfer(
        PathMethodDataTransfer $pathMethodDataTransfer,
        PathSchemaDataTransfer $responseSchemaDataTransfer,
        string $defaultResponseCode
    ): ?PathSchemaDataTransfer {
        $pathSchemaDataTransfer = $this->getSuccessResponseSchema($pathMethodDataTransfer->getResponseSchemas());

        if ($pathSchemaDataTransfer) {
            /** @phpstan-ignore notIdentical.alwaysTrue */
            if ($pathSchemaDataTransfer->getCode() !== Response::HTTP_NO_CONTENT) {
                $pathSchemaDataTransfer->setSchemaReference($responseSchemaDataTransfer->getSchemaReference());
            }

            return null;
        }

        $responseStatusCode = $this->getResponseStatusCode($pathMethodDataTransfer, $defaultResponseCode);
        $responseSchemaDataTransfer->setCode($responseStatusCode);
        $responseSchemaDataTransfer->setDescription(static::DESCRIPTION_SUCCESSFUL_RESPONSE);

        return $responseSchemaDataTransfer;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\PathSchemaDataTransfer> $responseSchemas
     *
     * @return \Generated\Shared\Transfer\PathSchemaDataTransfer|null
     */
    protected function getSuccessResponseSchema(ArrayObject $responseSchemas): ?PathSchemaDataTransfer
    {
        foreach ($responseSchemas as $responseSchema) {
            $responseSchemaCode = (int)$responseSchema->getCode();
            if ($responseSchemaCode >= 200 && $responseSchemaCode < 300) {
                return $responseSchema;
            }
        }

        return null;
    }
}
