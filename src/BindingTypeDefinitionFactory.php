<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\VocabularyLoader;

use ConundrumCodex\BindingEngine\Vocabulary\BindingTypeDefinition;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidBindingTypeDefinitionException;
use ConundrumCodex\BindingEngine\Vocabulary\Interfaces\AttributeDefinitionInterface;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\VocabularyLoadingException;

final readonly class BindingTypeDefinitionFactory
{
    public function __construct(
        private AttributeDefinitionFactory $attributeDefinitionFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws VocabularyLoadingException
     */
    public function fromArray(array $data, string $path): BindingTypeDefinition
    {
        $identifier = $this->requireString($data, 'identifier', $path);
        $label = $this->requireString($data, 'label', $path);
        $description = $this->requireString($data, 'description', $path);
        $allowedPayloadShapes = $this->requirePayloadShapes($data, 'allowedPayloadShapes', $path);
        $attributeDefinitions = $this->requireAttributeDefinitions($data, 'attributes', $path);

        try {
            return new BindingTypeDefinition(
                identifier: $identifier,
                label: $label,
                description: $description,
                allowedPayloadShapes: $allowedPayloadShapes,
                attributeDefinitions: $attributeDefinitions,
            );
        } catch (InvalidBindingTypeDefinitionException $exception) {
            throw new VocabularyLoadingException(
                sprintf('Invalid binding type definition at "%s": %s', $path, $exception->getMessage()),
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
     * @return list<BindingPayloadShapeEnum>
     *
     * @throws VocabularyLoadingException
     */
    private function requirePayloadShapes(array $data, string $key, string $path): array
    {
        if (!array_key_exists($key, $data)) {
            throw new VocabularyLoadingException(
                sprintf('Missing required key "%s" at "%s".', $key, $path),
            );
        }

        if (!is_array($data[$key])) {
            throw new VocabularyLoadingException(
                sprintf('Expected array at "%s.%s".', $path, $key),
            );
        }

        $payloadShapes = [];

        foreach ($data[$key] as $index => $value) {
            if (!is_string($value)) {
                throw new VocabularyLoadingException(
                    sprintf('Expected string at "%s.%s[%d]".', $path, $key, $index),
                );
            }

            try {
                $payloadShapes[] = BindingPayloadShapeEnum::from($value);
            } catch (\ValueError $exception) {
                throw new VocabularyLoadingException(
                    sprintf('Invalid payload shape "%s" at "%s.%s[%d]".', $value, $path, $key, $index),
                    0,
                    $exception,
                );
            }
        }

        return $payloadShapes;
    }

    /**
     * @param array<string, mixed> $data
     * @return list<AttributeDefinitionInterface>
     *
     * @throws VocabularyLoadingException
     */
    private function requireAttributeDefinitions(array $data, string $key, string $path): array
    {
        if (!array_key_exists($key, $data)) {
            throw new VocabularyLoadingException(
                sprintf('Missing required key "%s" at "%s".', $key, $path),
            );
        }

        if (!is_array($data[$key])) {
            throw new VocabularyLoadingException(
                sprintf('Expected array at "%s.%s".', $path, $key),
            );
        }

        $attributeDefinitions = [];

        foreach ($data[$key] as $index => $attributeData) {
            if (!is_array($attributeData)) {
                throw new VocabularyLoadingException(
                    sprintf('Expected object-like array at "%s.%s[%d]".', $path, $key, $index),
                );
            }

            /** @var array<string, mixed> $attributeData */
            $attributeDefinitions[] = $this->attributeDefinitionFactory->fromArray(
                $attributeData,
                sprintf('%s.%s[%d]', $path, $key, $index),
            );
        }

        return $attributeDefinitions;
    }
}
