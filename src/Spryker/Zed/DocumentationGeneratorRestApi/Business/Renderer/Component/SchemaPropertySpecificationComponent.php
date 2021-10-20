<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component;

use Generated\Shared\Transfer\SchemaPropertyComponentTransfer;
use stdClass;

/**
 * Specification:
 *  - This component describes a single Schema Object Property.
 *  - This component partly covers Schema Object Properties in OpenAPI specification format (see https://swagger.io/specification/#schemaObject).
 */
class SchemaPropertySpecificationComponent implements SchemaPropertySpecificationComponentInterface
{
    /**
     * @var string
     */
    protected const KEY_REF = '$ref';

    /**
     * @var string
     */
    protected const KEY_ITEMS = 'items';

    /**
     * @var string
     */
    protected const KEY_ONEOF = 'oneOf';

    /**
     * @var string
     */
    protected const KEY_TYPE = 'type';

    /**
     * @var string
     */
    protected const KEY_NULLABLE = 'nullable';

    /**
     * @var string
     */
    protected const VALUE_TYPE_ARRAY = 'array';

    /**
     * @var \Generated\Shared\Transfer\SchemaPropertyComponentTransfer|null
     */
    protected $schemaPropertyComponentTransfer;

    /**
     * @param \Generated\Shared\Transfer\SchemaPropertyComponentTransfer $schemaPropertyComponentTransfer
     *
     * @return void
     */
    public function setSchemaPropertyComponentTransfer(SchemaPropertyComponentTransfer $schemaPropertyComponentTransfer): void
    {
        $this->schemaPropertyComponentTransfer = $schemaPropertyComponentTransfer;
    }

    /**
     * @return array
     */
    public function getSpecificationComponentData(): array
    {
        $property = [];
        if (!$this->validateSchemaPropertyComponentTransfer()) {
            return [];
        }

        $property = $this->addBasicPropertyData($property);
        $property = $this->addItemPropertyData($property);

        return [$this->schemaPropertyComponentTransfer->getName() => $property];
    }

    /**
     * @param array $schemaProperty
     *
     * @return array
     */
    protected function addBasicPropertyData(array $schemaProperty): array
    {
        if ($this->schemaPropertyComponentTransfer->getType()) {
            $schemaProperty[SchemaPropertyComponentTransfer::TYPE] = $this->schemaPropertyComponentTransfer->getType();
        }
        if ($this->schemaPropertyComponentTransfer->getSchemaReference()) {
            $schemaProperty[static::KEY_REF] = $this->schemaPropertyComponentTransfer->getSchemaReference();
        }

        return $schemaProperty;
    }

    /**
     * @param array $schemaProperty
     *
     * @return array
     */
    protected function addItemPropertyData(array $schemaProperty): array
    {
        if ($this->schemaPropertyComponentTransfer->getItemsSchemaReference()) {
            $schemaProperty[SchemaPropertyComponentTransfer::TYPE] = static::VALUE_TYPE_ARRAY;
            $schemaProperty[static::KEY_ITEMS][static::KEY_REF] = $this->schemaPropertyComponentTransfer->getItemsSchemaReference();
        }
        if ($this->schemaPropertyComponentTransfer->getItemsType()) {
            $schemaProperty[static::KEY_ITEMS][static::KEY_TYPE] = $this->schemaPropertyComponentTransfer->getItemsType();
        }
        if ($this->schemaPropertyComponentTransfer->getType() === static::VALUE_TYPE_ARRAY && $this->schemaPropertyComponentTransfer->getOneOf()) {
            foreach ($this->schemaPropertyComponentTransfer->getOneOf() as $oneOfItem) {
                $schemaProperty[static::KEY_ONEOF][] = [static::KEY_REF => $oneOfItem];
            }
        }
        if ($this->schemaPropertyComponentTransfer->getType() === static::VALUE_TYPE_ARRAY && !$this->schemaPropertyComponentTransfer->getItemsType() && !$this->schemaPropertyComponentTransfer->getOneOf()) {
            $schemaProperty[static::KEY_ITEMS] = new stdClass();
        }
        if ($this->schemaPropertyComponentTransfer->getIsNullable()) {
            $schemaProperty[static::KEY_NULLABLE] = true;
        }

        return $schemaProperty;
    }

    /**
     * @return bool
     */
    protected function validateSchemaPropertyComponentTransfer(): bool
    {
        if (!$this->schemaPropertyComponentTransfer) {
            return false;
        }

        $this->schemaPropertyComponentTransfer->requireName();

        return true;
    }
}
