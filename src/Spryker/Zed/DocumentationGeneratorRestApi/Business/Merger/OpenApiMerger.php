<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DocumentationGeneratorRestApi\Business\Merger;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\OpenApi;
use InvalidArgumentException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Throwable;

class OpenApiMerger implements OpenApiMergerInterface
{
    protected const string REF_PREFIX = 'ApiPlatform_';

    protected const string REF_PATTERN_SCHEMA = '#/components/schemas/';

    protected const string KEY_PATHS = 'paths';

    protected const string KEY_COMPONENTS = 'components';

    protected const string KEY_SCHEMAS = 'schemas';

    protected const string KEY_SECURITY_SCHEMES = 'securitySchemes';

    protected const string KEY_PARAMETERS = 'parameters';

    protected const string KEY_TAGS = 'tags';

    protected const string KEY_SERVERS = 'servers';

    protected const string KEY_NAME = 'name';

    protected const string KEY_URL = 'url';

    protected const string REF_KEY = '$ref';

    /**
     * @param array<string> $contributorYamls
     *
     * @throws \InvalidArgumentException
     */
    public function mergeYamlFiles(string $legacyYamlPath, array $contributorYamls): OpenApi
    {
        $merged = $this->parseYamlFile($legacyYamlPath);

        foreach ($contributorYamls as $contributorYaml) {
            $contributor = $this->parseContributorYaml($contributorYaml);
            $contributor = $this->resolveSchemaCollisions($merged, $contributor);

            $merged = $this->mergePaths($merged, $contributor);
            $merged = $this->mergeComponents($merged, $contributor);
            $merged = $this->mergeTags($merged, $contributor);
            $merged = $this->mergeServers($merged, $contributor);
        }

        try {
            return new OpenApi($merged);
        } catch (TypeErrorException $exception) {
            throw new InvalidArgumentException(
                'Merged OpenAPI document is not structurally valid: ' . $exception->getMessage(),
                0,
                $exception,
            );
        }
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array<string, mixed>
     */
    protected function parseYamlFile(string $path): array
    {
        if (!is_readable($path)) {
            throw new InvalidArgumentException(sprintf('Legacy OpenAPI YAML not readable at "%s".', $path));
        }

        try {
            $data = Yaml::parseFile($path);
        } catch (ParseException $exception) {
            throw new InvalidArgumentException(
                sprintf('Legacy OpenAPI YAML at "%s" is not valid YAML.', $path),
                0,
                $exception,
            );
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array<string, mixed>
     */
    protected function parseContributorYaml(string $yaml): array
    {
        try {
            $data = Yaml::parse($yaml);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(
                'Contributor OpenAPI YAML is not valid YAML.',
                0,
                $exception,
            );
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException('Contributor OpenAPI YAML must decode to a mapping.');
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $legacy
     * @param array<string, mixed> $contributor
     *
     * @return array<string, mixed>
     */
    protected function resolveSchemaCollisions(array $legacy, array $contributor): array
    {
        $legacySchemas = $legacy[static::KEY_COMPONENTS][static::KEY_SCHEMAS] ?? [];
        $contributorSchemas = $contributor[static::KEY_COMPONENTS][static::KEY_SCHEMAS] ?? [];

        if ($legacySchemas === [] || $contributorSchemas === []) {
            return $contributor;
        }

        $rename = [];
        $dropAsDuplicate = [];

        foreach ($contributorSchemas as $name => $schema) {
            if (!array_key_exists($name, $legacySchemas)) {
                continue;
            }

            if ($legacySchemas[$name] === $schema) {
                $dropAsDuplicate[] = $name;

                continue;
            }

            $rename[$name] = static::REF_PREFIX . $name;
        }

        foreach ($dropAsDuplicate as $name) {
            unset($contributorSchemas[$name]);
        }

        if ($rename !== []) {
            foreach ($rename as $original => $renamed) {
                $contributorSchemas[$renamed] = $contributorSchemas[$original];
                unset($contributorSchemas[$original]);
            }

            $contributor = $this->rewriteSchemaRefs($contributor, $rename);
        }

        $contributor[static::KEY_COMPONENTS][static::KEY_SCHEMAS] = $contributorSchemas;

        return $contributor;
    }

    /**
     * @param array<string, mixed> $contributor
     * @param array<string, string> $rename
     *
     * @return array<string, mixed>
     */
    protected function rewriteSchemaRefs(array $contributor, array $rename): array
    {
        $walker = function (mixed &$value) use (&$walker, $rename): void {
            if (!is_array($value)) {
                return;
            }

            if (isset($value[static::REF_KEY]) && is_string($value[static::REF_KEY])) {
                foreach ($rename as $original => $renamed) {
                    if ($value[static::REF_KEY] === static::REF_PATTERN_SCHEMA . $original) {
                        $value[static::REF_KEY] = static::REF_PATTERN_SCHEMA . $renamed;
                    }
                }
            }

            foreach ($value as &$child) {
                $walker($child);
            }
            unset($child);
        };

        $walker($contributor);

        return $contributor;
    }

    /**
     * @param array<string, mixed> $legacy
     * @param array<string, mixed> $contributor
     *
     * @return array<string, mixed>
     */
    protected function mergePaths(array $legacy, array $contributor): array
    {
        $paths = ($legacy[static::KEY_PATHS] ?? []) + ($contributor[static::KEY_PATHS] ?? []);
        ksort($paths);
        $legacy[static::KEY_PATHS] = $paths;

        return $legacy;
    }

    /**
     * @param array<string, mixed> $legacy
     * @param array<string, mixed> $contributor
     *
     * @return array<string, mixed>
     */
    protected function mergeComponents(array $legacy, array $contributor): array
    {
        foreach ([static::KEY_SCHEMAS, static::KEY_SECURITY_SCHEMES, static::KEY_PARAMETERS] as $section) {
            $legacy[static::KEY_COMPONENTS][$section] =
                ($legacy[static::KEY_COMPONENTS][$section] ?? [])
                + ($contributor[static::KEY_COMPONENTS][$section] ?? []);
        }

        return $legacy;
    }

    /**
     * @param array<string, mixed> $legacy
     * @param array<string, mixed> $contributor
     *
     * @return array<string, mixed>
     */
    protected function mergeTags(array $legacy, array $contributor): array
    {
        $byName = [];
        foreach (($legacy[static::KEY_TAGS] ?? []) as $tag) {
            if (isset($tag[static::KEY_NAME])) {
                $byName[$tag[static::KEY_NAME]] = $tag;
            }
        }

        foreach (($contributor[static::KEY_TAGS] ?? []) as $tag) {
            if (!isset($tag[static::KEY_NAME]) || isset($byName[$tag[static::KEY_NAME]])) {
                continue;
            }
            $byName[$tag[static::KEY_NAME]] = $tag;
        }

        ksort($byName);
        $legacy[static::KEY_TAGS] = array_values($byName);

        return $legacy;
    }

    /**
     * @param array<string, mixed> $legacy
     * @param array<string, mixed> $contributor
     *
     * @return array<string, mixed>
     */
    protected function mergeServers(array $legacy, array $contributor): array
    {
        $byUrl = [];
        foreach (($legacy[static::KEY_SERVERS] ?? []) as $server) {
            if (isset($server[static::KEY_URL])) {
                $byUrl[$server[static::KEY_URL]] = $server;
            }
        }

        foreach (($contributor[static::KEY_SERVERS] ?? []) as $server) {
            if (!isset($server[static::KEY_URL]) || isset($byUrl[$server[static::KEY_URL]])) {
                continue;
            }
            $url = $server[static::KEY_URL];
            if ($url === '' || str_ends_with($url, '/')) {
                continue;
            }
            $byUrl[$url] = $server;
        }

        $legacy[static::KEY_SERVERS] = array_values($byUrl);

        return $legacy;
    }
}
