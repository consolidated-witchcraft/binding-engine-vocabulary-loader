<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\VocabularyLoader;

use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\MissingRequiredKeyException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\UnexpectedValueTypeException;

final class DecodedJsonValueAccessor
{
    private const string ROOT_PATH = '';

    /**
     * @param array<string, mixed> $data
     *
     * @throws MissingRequiredKeyException
     * @throws UnexpectedValueTypeException
     */
    public static function requireString(array $data, string $key, string $path): string
    {
        $value = self::requireValue(
            data: $data,
            key: $key,
            path: $path,
        );

        if (!is_string($value)) {
            throw new UnexpectedValueTypeException(
                expectedType: 'string',
                path: self::formatFieldPath(
                    key: $key,
                    path: $path,
                ),
            );
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<mixed, mixed>
     *
     * @throws MissingRequiredKeyException
     * @throws UnexpectedValueTypeException
     */
    public static function requireArray(array $data, string $key, string $path): array
    {
        $value = self::requireValue(
            data: $data,
            key: $key,
            path: $path,
        );

        if (!is_array($value)) {
            throw new UnexpectedValueTypeException(
                expectedType: 'array',
                path: self::formatFieldPath(
                    key: $key,
                    path: $path,
                ),
            );
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws UnexpectedValueTypeException
     */
    public static function optionalBool(array $data, string $key, string $path, bool $default): bool
    {
        if (!array_key_exists($key, $data)) {
            return $default;
        }

        if (!is_bool($data[$key])) {
            throw new UnexpectedValueTypeException(
                expectedType: 'boolean',
                path: self::formatFieldPath(
                    key: $key,
                    path: $path,
                ),
            );
        }

        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     * @return list<string>|null
     *
     * @throws UnexpectedValueTypeException
     */
    public static function optionalStringList(array $data, string $key, string $path): ?array
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        if (!is_array($data[$key])) {
            throw new UnexpectedValueTypeException(
                expectedType: 'array',
                path: self::formatFieldPath(
                    key: $key,
                    path: $path,
                ),
            );
        }

        $values = [];

        foreach ($data[$key] as $index => $value) {
            if (!is_string($value)) {
                throw new UnexpectedValueTypeException(
                    expectedType: 'string',
                    path: sprintf(
                        '%s[%d]',
                        self::formatFieldPath(
                            key: $key,
                            path: $path,
                        ),
                        $index,
                    ),
                );
            }

            $values[] = $value;
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws MissingRequiredKeyException
     */
    private static function requireValue(array $data, string $key, string $path): mixed
    {
        if (!array_key_exists($key, $data)) {
            throw new MissingRequiredKeyException(
                key: $key,
                path: $path,
            );
        }

        return $data[$key];
    }

    private static function formatFieldPath(string $key, string $path): string
    {
        if ($path === self::ROOT_PATH) {
            return $key;
        }

        return sprintf('%s.%s', $path, $key);
    }
}
