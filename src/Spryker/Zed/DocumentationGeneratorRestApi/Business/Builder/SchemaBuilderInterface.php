<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Builder;

use Generated\Shared\Transfer\SchemaDataTransfer;

interface SchemaBuilderInterface
{
    /**
     * @param string $schemaName
     * @param string $ref
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createRequestBaseSchema(string $schemaName, string $ref): SchemaDataTransfer;

    /**
     * @param string $schemaName
     * @param string $ref
     * @param string|null $type
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createRequestDataSchema(string $schemaName, string $ref, ?string $type = null): SchemaDataTransfer;

    /**
     * @param string $schemaName
     * @param array $transferMetadata
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createRequestDataAttributesSchema(string $schemaName, array $transferMetadata): SchemaDataTransfer;

    /**
     * @param string $schemaName
     * @param string $ref
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createResponseBaseSchema(string $schemaName, string $ref): SchemaDataTransfer;

    /**
     * @param string $schemaName
     * @param string $ref
     * @param string|null $type
     * @param bool $isIdNullable
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createResponseDataSchema(string $schemaName, string $ref, ?string $type = null, bool $isIdNullable = false): SchemaDataTransfer;

    /**
     * @param string $schemaName
     * @param array $transferMetadata
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createResponseDataAttributesSchema(string $schemaName, array $transferMetadata): SchemaDataTransfer;

    /**
     * @param string $schemaName
     * @param string $ref
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createCollectionResponseBaseSchema(string $schemaName, string $ref): SchemaDataTransfer;

    /**
     * @param string $schemaName
     * @param string $ref
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createRelationshipBaseSchema(string $schemaName, string $ref): SchemaDataTransfer;

    /**
     * @param string $schemaName
     * @param string $ref
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createIncludedBaseSchema(string $schemaName, string $ref): SchemaDataTransfer;

    /**
     * @param string $schemaName
     * @param array $resourceRelationships
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createRelationshipDataSchema(string $schemaName, array $resourceRelationships): SchemaDataTransfer;

    /**
     * @param string $schemaName
     * @param array $resourceRelationships
     *
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createIncludedDataSchema(string $schemaName, array $resourceRelationships): SchemaDataTransfer;

    /**
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createDefaultRelationshipDataAttributesSchema(): SchemaDataTransfer;

    /**
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createDefaultRelationshipDataCollectionAttributesSchema(): SchemaDataTransfer;

    /**
     * @return \Generated\Shared\Transfer\SchemaDataTransfer
     */
    public function createDefaultLinksSchema(): SchemaDataTransfer;
}
