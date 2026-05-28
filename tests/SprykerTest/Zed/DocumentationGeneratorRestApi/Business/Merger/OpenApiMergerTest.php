<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DocumentationGeneratorRestApi\Business\Merger;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\Writer;
use Codeception\Test\Unit;
use InvalidArgumentException;
use Spryker\Zed\DocumentationGeneratorRestApi\Business\Merger\OpenApiMerger;
use Symfony\Component\Yaml\Yaml;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DocumentationGeneratorRestApi
 * @group Business
 * @group Merger
 * @group OpenApiMergerTest
 * Add your own group annotations below this line
 */
class OpenApiMergerTest extends Unit
{
    protected const string FIXTURE_DIR = __DIR__ . '/../../../../../_data/SwaggerMerge';

    public function testMergeWithEmptyContributorListReturnsLegacyUnchanged(): void
    {
        $merger = new OpenApiMerger();
        $legacyPath = static::FIXTURE_DIR . '/legacy.yaml';

        $merged = $merger->mergeYamlFiles($legacyPath, []);

        $this->assertInstanceOf(OpenApi::class, $merged);

        $expected = Yaml::parseFile($legacyPath);
        $actual = $merged->getSerializableData();
        // Cebe returns an object for the root; normalise both sides to array for comparison.
        $this->assertEquals($expected, json_decode(json_encode($actual), true));
    }

    public function testMergeUnionsPathsTagsServersAndComponents(): void
    {
        $merger = new OpenApiMerger();
        $legacyPath = static::FIXTURE_DIR . '/legacy.yaml';
        $contributorYaml = file_get_contents(static::FIXTURE_DIR . '/api-platform.yaml');

        $merged = $merger->mergeYamlFiles($legacyPath, [$contributorYaml]);
        $data = json_decode(json_encode($merged->getSerializableData()), true);

        $this->assertArrayHasKey('/legacy-resource', $data['paths']);
        $this->assertArrayHasKey('/api-platform-resource', $data['paths']);

        $tagNames = array_column($data['tags'], 'name');
        $this->assertContains('Legacy', $tagNames);
        $this->assertContains('ApiPlatform', $tagNames);

        $serverUrls = array_column($data['servers'], 'url');
        $this->assertContains('http://legacy.local', $serverUrls);
        $this->assertContains('http://api-platform.local', $serverUrls);

        $this->assertArrayHasKey('LegacyOnly', $data['components']['schemas']);
        $this->assertArrayHasKey('ApiPlatformOnly', $data['components']['schemas']);
        $this->assertArrayHasKey('LegacyAuth', $data['components']['securitySchemes']);
        $this->assertArrayHasKey('ApiPlatformAuth', $data['components']['securitySchemes']);
    }

    public function testIdenticalSchemasAreDedupedAndDifferingSchemasArePrefixedAndRefsRewritten(): void
    {
        $merger = new OpenApiMerger();
        $legacyPath = static::FIXTURE_DIR . '/legacy.yaml';
        $contributorYaml = file_get_contents(static::FIXTURE_DIR . '/api-platform.yaml');

        $merged = $merger->mergeYamlFiles($legacyPath, [$contributorYaml]);
        $data = json_decode(json_encode($merged->getSerializableData()), true);

        // Identical schema is deduped — only the legacy copy remains.
        $this->assertArrayHasKey('SharedIdentical', $data['components']['schemas']);
        $this->assertArrayNotHasKey('ApiPlatform_SharedIdentical', $data['components']['schemas']);

        // Differing schema kept under prefixed name, legacy copy preserved verbatim.
        $this->assertArrayHasKey('SharedDifferent', $data['components']['schemas']);
        $this->assertArrayHasKey('ApiPlatform_SharedDifferent', $data['components']['schemas']);
        $this->assertEquals(
            ['legacy_only_field'],
            array_keys($data['components']['schemas']['SharedDifferent']['properties']),
        );
        $this->assertEquals(
            ['api_platform_only_field'],
            array_keys($data['components']['schemas']['ApiPlatform_SharedDifferent']['properties']),
        );

        // Contributor's $ref to SharedDifferent was rewritten to point at the prefixed name.
        $ref = $data['paths']['/api-platform-resource']['get']['responses']['200']['content']['application/json']['schema']['$ref'];
        $this->assertSame('#/components/schemas/ApiPlatform_SharedDifferent', $ref);
    }

    public function testInfoBlockComesFromLegacy(): void
    {
        $merger = new OpenApiMerger();
        $legacyPath = static::FIXTURE_DIR . '/legacy.yaml';
        $contributorYaml = file_get_contents(static::FIXTURE_DIR . '/api-platform.yaml');

        $merged = $merger->mergeYamlFiles($legacyPath, [$contributorYaml]);
        $data = json_decode(json_encode($merged->getSerializableData()), true);

        $this->assertSame('Legacy API', $data['info']['title']);
        $this->assertSame('1.0.0', $data['info']['version']);
        $this->assertSame('Spryker', $data['info']['contact']['name']);
    }

    public function testInvalidContributorYamlThrowsInvalidArgumentException(): void
    {
        $merger = new OpenApiMerger();
        $legacyPath = static::FIXTURE_DIR . '/legacy.yaml';

        $this->expectException(InvalidArgumentException::class);
        $merger->mergeYamlFiles($legacyPath, ["openapi: 3.0.0\npaths:\n  - this: is: not: valid"]);
    }

    public function testMergedDocumentSerialisesBackToYaml(): void
    {
        $merger = new OpenApiMerger();
        $legacyPath = static::FIXTURE_DIR . '/legacy.yaml';
        $contributorYaml = file_get_contents(static::FIXTURE_DIR . '/api-platform.yaml');

        $merged = $merger->mergeYamlFiles($legacyPath, [$contributorYaml]);
        $yaml = Writer::writeToYaml($merged);

        $this->assertNotEmpty($yaml);
        $this->assertStringContainsString('/legacy-resource', $yaml);
        $this->assertStringContainsString('/api-platform-resource', $yaml);
        $this->assertStringContainsString('ApiPlatform_SharedDifferent', $yaml);
    }
}
