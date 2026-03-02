<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer;

use Generated\Shared\Transfer\SchemaComponentTransfer;
use Generated\Shared\Transfer\SchemaDataTransfer;
use Generated\Shared\Transfer\SchemaItemsComponentTransfer;
use Generated\Shared\Transfer\SchemaItemsTransfer;
use Generated\Shared\Transfer\SchemaPropertyComponentTransfer;
use Generated\Shared\Transfer\SchemaPropertyTransfer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaItemsSpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaPropertySpecificationComponentInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaSpecificationComponentInterface;

class SchemaRenderer implements SchemaRendererInterface
{
    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaSpecificationComponentInterface
     */
    protected $schemaSpecificationComponent;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaPropertySpecificationComponentInterface
     */
    protected $schemaPropertySpecificationComponent;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Renderer\Component\SchemaItemsSpecificationComponentInterface
     */
    protected $schemaItemsSpecificationComponent;

    public function __construct(
        SchemaSpecificationComponentInterface $schemaSpecificationComponent,
        SchemaPropertySpecificationComponentInterface $schemaPropertySpecificationComponent,
        SchemaItemsSpecificationComponentInterface $schemaItemsSpecificationComponent
    ) {
        $this->schemaSpecificationComponent = $schemaSpecificationComponent;
        $this->schemaPropertySpecificationComponent = $schemaPropertySpecificationComponent;
        $this->schemaItemsSpecificationComponent = $schemaItemsSpecificationComponent;
    }

    public function render(SchemaDataTransfer $schemaDataTransfer): array
    {
        $schemaComponentTransfer = new SchemaComponentTransfer();
        $schemaComponentTransfer->setName($schemaDataTransfer->getName());

        foreach ($schemaDataTransfer->getProperties() as $property) {
            $this->addSchemaProperty($schemaComponentTransfer, $property);
        }

        if ($schemaDataTransfer->getItems()) {
            $this->addRelationshipSchemaItems($schemaComponentTransfer, $schemaDataTransfer->getItems());
        }

        if ($schemaDataTransfer->getType()) {
            $schemaComponentTransfer->setType($schemaDataTransfer->getType());
        }

        if ($schemaDataTransfer->getRequired()) {
            $schemaComponentTransfer->setRequired($schemaDataTransfer->getRequired());
        }

        $this->schemaSpecificationComponent->setSchemaComponentTransfer($schemaComponentTransfer);

        return $this->schemaSpecificationComponent->getSpecificationComponentData();
    }

    protected function addSchemaProperty(SchemaComponentTransfer $schemaComponent, SchemaPropertyTransfer $property): void
    {
        $schemaPropertyComponentTransfer = new SchemaPropertyComponentTransfer();
        $schemaPropertyComponentTransfer->setName($property->getName());
        $schemaPropertyComponentTransfer->setIsNullable($property->getIsNullable());

        $schemaPropertyComponentTransfer = $this->addType($schemaPropertyComponentTransfer, $property);
        $schemaPropertyComponentTransfer = $this->addExample($schemaPropertyComponentTransfer, $property);
        $schemaPropertyComponentTransfer = $this->addDescription($schemaPropertyComponentTransfer, $property);
        $schemaPropertyComponentTransfer = $this->addReference($schemaPropertyComponentTransfer, $property);
        $schemaPropertyComponentTransfer = $this->addOneOf($schemaPropertyComponentTransfer, $property);
        $schemaPropertyComponentTransfer = $this->addItemsType($schemaPropertyComponentTransfer, $property);
        $schemaPropertyComponentTransfer = $this->addItemsReference($schemaPropertyComponentTransfer, $property);

        $this->schemaPropertySpecificationComponent->setSchemaPropertyComponentTransfer($schemaPropertyComponentTransfer);
        $schemaPropertySpecificationData = $this->schemaPropertySpecificationComponent->getSpecificationComponentData();

        if ($schemaPropertySpecificationData) {
            $schemaComponent->addProperty($schemaPropertySpecificationData);
        }
    }

    protected function addOneOf(
        SchemaPropertyComponentTransfer $schemaPropertyComponentTransfer,
        SchemaPropertyTransfer $property
    ): SchemaPropertyComponentTransfer {
        if ($property->getOneOf()) {
            $schemaPropertyComponentTransfer->setOneOf($property->getOneOf());
        }

        return $schemaPropertyComponentTransfer;
    }

    protected function addType(
        SchemaPropertyComponentTransfer $schemaPropertyComponentTransfer,
        SchemaPropertyTransfer $property
    ): SchemaPropertyComponentTransfer {
        if ($property->getType()) {
            $schemaPropertyComponentTransfer->setType($property->getType());
        }

        return $schemaPropertyComponentTransfer;
    }

    protected function addExample(
        SchemaPropertyComponentTransfer $schemaPropertyComponentTransfer,
        SchemaPropertyTransfer $property
    ): SchemaPropertyComponentTransfer {
        if ($property->getExample()) {
            $schemaPropertyComponentTransfer->setExample($property->getExample());
        }

        return $schemaPropertyComponentTransfer;
    }

    protected function addDescription(
        SchemaPropertyComponentTransfer $schemaPropertyComponentTransfer,
        SchemaPropertyTransfer $property
    ): SchemaPropertyComponentTransfer {
        if ($property->getDescription()) {
            $schemaPropertyComponentTransfer->setDescription($property->getDescription());
        }

        return $schemaPropertyComponentTransfer;
    }

    protected function addReference(
        SchemaPropertyComponentTransfer $schemaPropertyComponentTransfer,
        SchemaPropertyTransfer $property
    ): SchemaPropertyComponentTransfer {
        if ($property->getReference()) {
            $schemaPropertyComponentTransfer->setSchemaReference($property->getReference());
        }

        return $schemaPropertyComponentTransfer;
    }

    protected function addItemsType(
        SchemaPropertyComponentTransfer $schemaPropertyComponentTransfer,
        SchemaPropertyTransfer $property
    ): SchemaPropertyComponentTransfer {
        if ($property->getItemsType()) {
            $schemaPropertyComponentTransfer->setItemsType($property->getItemsType());
        }

        return $schemaPropertyComponentTransfer;
    }

    protected function addItemsReference(
        SchemaPropertyComponentTransfer $schemaPropertyComponentTransfer,
        SchemaPropertyTransfer $property
    ): SchemaPropertyComponentTransfer {
        if ($property->getItemsReference()) {
            $schemaPropertyComponentTransfer->setItemsSchemaReference($property->getItemsReference());
        }

        return $schemaPropertyComponentTransfer;
    }

    protected function addRelationshipSchemaItems(SchemaComponentTransfer $schemaComponent, SchemaItemsTransfer $items): void
    {
        $schemaPropertyComponentTransfer = new SchemaItemsComponentTransfer();
        if ($items->getOneOf()) {
            $schemaPropertyComponentTransfer->setOneOf($items->getOneOf());
        }

        $this->schemaItemsSpecificationComponent->setSchemaItemsComponentTransfer($schemaPropertyComponentTransfer);
        $schemaItemsSpecificationData = $this->schemaItemsSpecificationComponent->getSpecificationComponentData();

        if ($schemaItemsSpecificationData) {
            $schemaComponent->setItems($schemaItemsSpecificationData);
        }
    }
}
