<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Writer;

use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToFilesystemInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToSymfonyYamlAdapter;
use Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToYamlDumperInterface;
use Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig;

class YamlOpenApiDocumentationWriter implements DocumentationWriterInterface
{
    /**
     * @var string
     */
    protected const GENERATED_FILE_POSTFIX = '.schema.yml';

    /**
     * @var string
     */
    protected const OPENAPI_VERSION = '3.0.0';

    /**
     * @var string
     */
    protected const KEY_OPENAPI = 'openapi';

    /**
     * @var string
     */
    protected const KEY_INFO = 'info';

    /**
     * @var string
     */
    protected const KEY_VERSION = 'version';

    /**
     * @var string
     */
    protected const KEY_CONTACT = 'contact';

    /**
     * @var string
     */
    protected const KEY_CONTACT_NAME = 'name';

    /**
     * @var string
     */
    protected const KEY_CONTACT_URL = 'url';

    /**
     * @var string
     */
    protected const KEY_CONTACT_EMAIL = 'email';

    /**
     * @var string
     */
    protected const KEY_TITLE = 'title';

    /**
     * @var string
     */
    protected const KEY_LICENSE = 'license';

    /**
     * @var string
     */
    protected const KEY_NAME = 'name';

    /**
     * @var string
     */
    protected const KEY_SERVERS = 'servers';

    /**
     * @var string
     */
    protected const KEY_URL = 'url';

    /**
     * @var string
     */
    protected const KEY_PATHS = 'paths';

    /**
     * @var string
     */
    protected const KEY_COMPONENTS = 'components';

    /**
     * @var string
     */
    protected const KEY_SCHEMAS = 'schemas';

    /**
     * @var string
     */
    protected const KEY_SECURITY_SCHEMES = 'securitySchemes';

    /**
     * @var string
     */
    protected const KEY_TAGS = 'tags';

    /**
     * @var string
     */
    protected const KEY_PARAMETERS = 'parameters';

    /**
     * @var int
     */
    protected const YAML_NESTING_LEVEL = 9;

    /**
     * @var int
     */
    protected const YAML_INDENT = 4;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig
     */
    protected $documentationGeneratorRestApiConfig;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToYamlDumperInterface
     */
    protected $yamlDumper;

    /**
     * @var \Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToFilesystemInterface
     */
    protected $filesystem;

    /**
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\DocumentationGeneratorRestApiConfig $documentationGeneratorRestApiConfig
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToYamlDumperInterface $yamlDumper
     * @param \Spryker\Zed\DocumentationGeneratorRestApi\Dependency\External\DocumentationGeneratorRestApiToFilesystemInterface $filesystem
     */
    public function __construct(
        DocumentationGeneratorRestApiConfig $documentationGeneratorRestApiConfig,
        DocumentationGeneratorRestApiToYamlDumperInterface $yamlDumper,
        DocumentationGeneratorRestApiToFilesystemInterface $filesystem
    ) {
        $this->documentationGeneratorRestApiConfig = $documentationGeneratorRestApiConfig;
        $this->yamlDumper = $yamlDumper;
        $this->filesystem = $filesystem;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function write(array $data): void
    {
        $dataStructure = $this->getDefaultDataStructure();
        $dataStructure[static::KEY_PATHS] = $data[static::KEY_PATHS];
        $dataStructure[static::KEY_TAGS] = $data[static::KEY_TAGS];
        $dataStructure[static::KEY_COMPONENTS][static::KEY_SCHEMAS] = $data[static::KEY_SCHEMAS];
        $dataStructure[static::KEY_COMPONENTS][static::KEY_SECURITY_SCHEMES] = $data[static::KEY_SECURITY_SCHEMES];
        $dataStructure[static::KEY_COMPONENTS][static::KEY_PARAMETERS] = $data[static::KEY_PARAMETERS];

        $fileName = str_replace('//', '/', $this->resolveGeneratedFileName());
        $yaml = $this->yamlDumper->dump(
            $dataStructure,
            static::YAML_NESTING_LEVEL,
            static::YAML_INDENT,
            DocumentationGeneratorRestApiToSymfonyYamlAdapter::DUMP_EMPTY_ARRAY_AS_SEQUENCE
            | DocumentationGeneratorRestApiToSymfonyYamlAdapter::DUMP_MULTI_LINE_LITERAL_BLOCK
            | DocumentationGeneratorRestApiToSymfonyYamlAdapter::DUMP_OBJECT_AS_MAP,
        );

        $this->filesystem->dumpFile($fileName, $yaml);
    }

    /**
     * @return array
     */
    protected function getDefaultDataStructure(): array
    {
        return [
            static::KEY_OPENAPI => static::OPENAPI_VERSION,
            static::KEY_INFO => [
                static::KEY_VERSION => $this->documentationGeneratorRestApiConfig->getApiDocumentationVersionInfo(),
                static::KEY_CONTACT => [
                    static::KEY_CONTACT_NAME => $this->documentationGeneratorRestApiConfig->getApiDocumentationContactName(),
                    static::KEY_CONTACT_URL => $this->documentationGeneratorRestApiConfig->getApiDocumentationContactUrl(),
                    static::KEY_CONTACT_EMAIL => $this->documentationGeneratorRestApiConfig->getApiDocumentationContactEmail(),
                ],
                static::KEY_TITLE => $this->documentationGeneratorRestApiConfig->getApiDocumentationTitleInfo(),
                static::KEY_LICENSE => [
                    static::KEY_NAME => $this->documentationGeneratorRestApiConfig->getApiDocumentationLicenceNameInfo(),
                ],
            ],
            static::KEY_TAGS => [],
            static::KEY_SERVERS => [
                [
                    static::KEY_URL => $this->documentationGeneratorRestApiConfig->getRestApplicationDomain(),
                ],
            ],
            static::KEY_PATHS => [],
            static::KEY_COMPONENTS => [
                static::KEY_SECURITY_SCHEMES => [],
                static::KEY_SCHEMAS => [],
                static::KEY_PARAMETERS => [],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function resolveGeneratedFileName(): string
    {
        return $this->documentationGeneratorRestApiConfig->getGeneratedFileOutputDirectory()
            . DIRECTORY_SEPARATOR
            . $this->documentationGeneratorRestApiConfig->getGeneratedFilePrefix()
            . static::GENERATED_FILE_POSTFIX;
    }
}
