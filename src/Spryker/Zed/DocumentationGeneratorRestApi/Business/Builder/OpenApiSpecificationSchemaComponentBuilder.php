<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Builder;

use Generated\Shared\Transfer\SchemaDataTransfer;
use Generated\Shared\Transfer\SchemaItemsTransfer;
use Generated\Shared\Transfer\SchemaPropertyTransfer;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceTransferAnalyzerInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Storage\ResourceSchemaNameStorageInterface;

class OpenApiSpecificationSchemaComponentBuilder implements SchemaComponentBuilderInterface
{
    /**
     * @var string
     */
    protected const VALUE_TYPE_ARRAY = 'array';

    /**
     * @var string
     */
    protected const VALUE_TYPE_BOOLEAN = 'boolean';

    /**
     * @var string
     */
    protected const VALUE_TYPE_INTEGER = 'integer';

    /**
     * @var string
     */
    protected const VALUE_TYPE_NUMBER = 'number';

    /**
     * @var string
     */
    protected const VALUE_TYPE_STRING = 'string';

    /**
     * @var array
     */
    protected const DATA_TYPES_MAPPING_LIST = [
        'int' => self::VALUE_TYPE_INTEGER,
        'bool' => self::VALUE_TYPE_BOOLEAN,
        'float' => self::VALUE_TYPE_NUMBER,
        'Spryker\DecimalObject\Decimal' => self::VALUE_TYPE_NUMBER,
    ];

    /**
     * @var string
     */
    protected const KEY_TYPE = 'type';

    /**
     * @var string
     */
    protected const KEY_IS_COLLECTION = 'is_collection';

    /**
     * @var string
     */
    protected const KEY_IS_NULLABLE = 'is_nullable';

    /**
     * @var string
     */
    protected const PATTERN_SCHEMA_REFERENCE = '#/components/schemas/%s';

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Analyzer\ResourceTransferAnalyzerInterface
     */
    protected $resourceTransferAnalyzer;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Business\Storage\ResourceSchemaNameStorageInterface
     */
    protected $resourceSchemaNameStorage;

    public function __construct(
        ResourceTransferAnalyzerInterface $resourceTransferAnalyzer,
        ResourceSchemaNameStorageInterface $resourceSchemaNameStorage
    ) {
        $this->resourceTransferAnalyzer = $resourceTransferAnalyzer;
        $this->resourceSchemaNameStorage = $resourceSchemaNameStorage;
    }

    public function createObjectSchemaTypeTransfer(string $key, string $schemaName, array $objectMetadata): SchemaPropertyTransfer
    {
        if ($objectMetadata[static::KEY_IS_COLLECTION]) {
            return $this->createArrayOfObjectsPropertyTransfer($key, $schemaName, $objectMetadata[static::KEY_IS_NULLABLE]);
        }

        return $this->createReferencePropertyTransfer($key, $schemaName, $objectMetadata[static::KEY_IS_NULLABLE]);
    }

    public function createScalarSchemaTypeTransfer(string $key, string $type, bool $isNullable = false, array $metadata = []): SchemaPropertyTransfer
    {
        if (substr($type, -2) === '[]') {
            return $this->createArrayOfTypesPropertyTransfer($key, $this->mapScalarSchemaType(substr($type, 0, -2)), $isNullable);
        }

        if ($type === static::VALUE_TYPE_ARRAY) {
            return $this->createArrayOfMixedTypesPropertyTransfer($key, $isNullable);
        }

        return $this->createTypePropertyTransfer($key, $this->mapScalarSchemaType($type), $isNullable, $metadata);
    }

    public function createSchemaDataTransfer(string $name): SchemaDataTransfer
    {
        $schemaData = new SchemaDataTransfer();
        $schemaData->setName($name);

        return $schemaData;
    }

    public function createTypePropertyTransfer(string $name, string $type, bool $isNullable = false, array $metadata = []): SchemaPropertyTransfer
    {
        $typeProperty = new SchemaPropertyTransfer();
        $typeProperty->setName($name);
        $typeProperty->setType($type);
        $typeProperty->setIsNullable($isNullable);
        $typeProperty->setExample($metadata['example'] ?? null);
        $typeProperty->setDescription($metadata['description'] ?? null);

        return $typeProperty;
    }

    public function createReferencePropertyTransfer(string $name, string $ref, bool $isNullable = false): SchemaPropertyTransfer
    {
        $referenceProperty = new SchemaPropertyTransfer();
        $referenceProperty->setName($name);
        $referenceProperty->setReference(sprintf(static::PATTERN_SCHEMA_REFERENCE, $ref));
        $referenceProperty->setIsNullable($isNullable);

        return $referenceProperty;
    }

    public function createArrayOfObjectsPropertyTransfer(string $name, string $itemsRef, bool $isNullable = false): SchemaPropertyTransfer
    {
        $arrayProperty = new SchemaPropertyTransfer();
        $arrayProperty->setName($name);
        $arrayProperty->setItemsReference(sprintf(static::PATTERN_SCHEMA_REFERENCE, $itemsRef));
        $arrayProperty->setIsNullable($isNullable);

        return $arrayProperty;
    }

    public function createArrayOfTypesPropertyTransfer(string $name, string $itemsType, bool $isNullable = false): SchemaPropertyTransfer
    {
        $arrayProperty = new SchemaPropertyTransfer();
        $arrayProperty->setName($name);
        $arrayProperty->setType(static::VALUE_TYPE_ARRAY);
        $arrayProperty->setItemsType($itemsType);
        $arrayProperty->setIsNullable($isNullable);

        return $arrayProperty;
    }

    public function createArrayOfMixedTypesPropertyTransfer(string $name, bool $isNullable = false): SchemaPropertyTransfer
    {
        $arrayProperty = new SchemaPropertyTransfer();
        $arrayProperty->setName($name);
        $arrayProperty->setType(static::VALUE_TYPE_ARRAY);
        $arrayProperty->setIsNullable($isNullable);

        return $arrayProperty;
    }

    public function createRequestSchemaPropertyTransfer(string $metadataKey, array $metadata): SchemaPropertyTransfer
    {
        if ($this->isScalarType($metadata[static::KEY_TYPE])) {
            return $this->createScalarSchemaTypeTransfer($metadataKey, $metadata[static::KEY_TYPE], $metadata[static::KEY_IS_NULLABLE], $metadata);
        }

        $schemaName = $this->resourceTransferAnalyzer->createRequestAttributesSchemaNameFromTransferClassName($metadata[static::KEY_TYPE]);

        return $this->createObjectSchemaTypeTransfer($metadataKey, $schemaName, $metadata);
    }

    /**
     * @param array<\Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRelationshipPluginInterface> $resourceRelationships
     *
     * @return \Generated\Shared\Transfer\SchemaItemsTransfer
     */
    public function createRelationshipSchemaItemsTransfer(array $resourceRelationships): SchemaItemsTransfer
    {
        $schema = new SchemaItemsTransfer();

        foreach ($resourceRelationships as $resourceRelationship) {
            $schemaName = $this
                ->resourceSchemaNameStorage
                ->getResourceSchemaNameByResourceType($resourceRelationship->getRelationshipResourceType());

            if (!$schemaName) {
                continue;
            }

            $schema->addOneOf(
                sprintf(
                    static::PATTERN_SCHEMA_REFERENCE,
                    $schemaName,
                ),
            );
        }

        return $schema;
    }

    public function createResponseSchemaPropertyTransfer(string $metadataKey, array $metadata): SchemaPropertyTransfer
    {
        if ($this->isScalarType($metadata[static::KEY_TYPE])) {
            return $this->createScalarSchemaTypeTransfer($metadataKey, $metadata[static::KEY_TYPE], $metadata[static::KEY_IS_NULLABLE], $metadata);
        }
        $schemaName = $this->resourceTransferAnalyzer->createResponseAttributesSchemaNameFromTransferClassName($metadata[static::KEY_TYPE]);

        return $this->createObjectSchemaTypeTransfer($metadataKey, $schemaName, $metadata);
    }

    protected function mapScalarSchemaType(string $type): string
    {
        return static::DATA_TYPES_MAPPING_LIST[$type] ?? $type;
    }

    protected function isScalarType(string $type): bool
    {
        return !(class_exists($type) && is_a($type, AbstractTransfer::class, true));
    }
}
