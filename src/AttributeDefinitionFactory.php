<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\VocabularyLoader;

use ConundrumCodex\BindingEngine\Vocabulary\AttributeDefinition;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidAttributeDefinitionException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\VocabularyLoadingException;

final readonly class AttributeDefinitionFactory
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws VocabularyLoadingException
     */
    public function fromArray(array $data, string $path): AttributeDefinition
    {
        $identifier = $this->requireString($data, 'identifier', $path);
        $label = $this->requireString($data, 'label', $path);
        $description = $this->requireString($data, 'description', $path);
        $valueType = $this->requireAttributeValueTypeEnum($data, 'valueType', $path);
        $required = $this->optionalBool($data, 'required', $path, false);
        $repeatable = $this->optionalBool($data, 'repeatable', $path, false);
        $allowedValues = $this->optionalStringList($data, 'allowedValues', $path);

        try {
            return new AttributeDefinition(
                identifier: $identifier,
                label: $label,
                description: $description,
                valueType: $valueType,
                required: $required,
                repeatable: $repeatable,
                allowedValues: $allowedValues,
            );
        } catch (InvalidAttributeDefinitionException $exception) {
            throw new VocabularyLoadingException(
                sprintf('Invalid attribute definition at "%s": %s', $path, $exception->getMessage()),
                0,
                $exception,
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws VocabularyLoadingException
     */
    private function requireString(array $data, string $key, string $path): string
    {
        if (!array_key_exists($key, $data)) {
            throw new VocabularyLoadingException(
                sprintf('Missing required key "%s" at "%s".', $key, $path),
            );
        }

        if (!is_string($data[$key])) {
            throw new VocabularyLoadingException(
                sprintf('Expected string at "%s.%s".', $path, $key),
            );
        }

        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws VocabularyLoadingException
     */
    private function optionalBool(array $data, string $key, string $path, bool $default): bool
    {
        if (!array_key_exists($key, $data)) {
            return $default;
        }

        if (!is_bool($data[$key])) {
            throw new VocabularyLoadingException(
                sprintf('Expected boolean at "%s.%s".', $path, $key),
            );
        }

        return $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     * @return list<string>|null
     *
     * @throws VocabularyLoadingException
     */
    private function optionalStringList(array $data, string $key, string $path): ?array
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }

        if (!is_array($data[$key])) {
            throw new VocabularyLoadingException(
                sprintf('Expected array at "%s.%s".', $path, $key),
            );
        }

        $values = [];

        foreach ($data[$key] as $index => $value) {
            if (!is_string($value)) {
                throw new VocabularyLoadingException(
                    sprintf('Expected string at "%s.%s[%d]".', $path, $key, $index),
                );
            }

            $values[] = $value;
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws VocabularyLoadingException
     */
    private function requireAttributeValueTypeEnum(array $data, string $key, string $path): AttributeValueTypeEnum
    {
        $value = $this->requireString($data, $key, $path);

        try {
            return AttributeValueTypeEnum::from($value);
        } catch (\ValueError $exception) {
            throw new VocabularyLoadingException(
                sprintf('Invalid valueType "%s" at "%s.%s".', $value, $path, $key),
                0,
                $exception,
            );
        }
    }
}
