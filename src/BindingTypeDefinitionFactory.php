<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\VocabularyLoader;

use ConundrumCodex\BindingEngine\Vocabulary\BindingTypeDefinition;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidBindingTypeDefinitionException;
use ConundrumCodex\BindingEngine\Vocabulary\Interfaces\AttributeDefinitionInterface;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\AbstractVocabularyLoadingException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\DomainConstructionFailedException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\InvalidEnumValueException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\UnexpectedValueTypeException;

final readonly class BindingTypeDefinitionFactory
{
    public function __construct(
        private AttributeDefinitionFactory $attributeDefinitionFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws AbstractVocabularyLoadingException
     */
    public function fromArray(array $data, string $path): BindingTypeDefinition
    {
        $identifier = DecodedJsonValueAccessor::requireString(
            data: $data,
            key: 'identifier',
            path: $path,
        );
        $label = DecodedJsonValueAccessor::requireString(
            data: $data,
            key: 'label',
            path: $path,
        );
        $description = DecodedJsonValueAccessor::requireString(
            data: $data,
            key: 'description',
            path: $path,
        );
        $allowedPayloadShapes = self::requirePayloadShapes(
            data: $data,
            key: 'allowedPayloadShapes',
            path: $path,
        );
        $attributeDefinitions = $this->requireAttributeDefinitions(
            data: $data,
            key: 'attributes',
            path: $path,
        );

        try {
            return new BindingTypeDefinition(
                identifier: $identifier,
                label: $label,
                description: $description,
                allowedPayloadShapes: $allowedPayloadShapes,
                attributeDefinitions: $attributeDefinitions,
            );
        } catch (InvalidBindingTypeDefinitionException $exception) {
            throw new DomainConstructionFailedException(
                subject: 'binding type definition',
                detail: $exception->getMessage(),
                path: $path,
                previous: $exception,
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return list<BindingPayloadShapeEnum>
     *
     * @throws InvalidEnumValueException
     * @throws UnexpectedValueTypeException
     */
    private static function requirePayloadShapes(array $data, string $key, string $path): array
    {
        $payloadShapeValues = DecodedJsonValueAccessor::requireArray(
            data: $data,
            key: $key,
            path: $path,
        );

        $payloadShapes = [];

        foreach ($payloadShapeValues as $index => $value) {
            if (!is_string($value)) {
                throw new UnexpectedValueTypeException(
                    expectedType: 'string',
                    path: sprintf('%s.%s[%d]', $path, $key, $index),
                );
            }

            try {
                $payloadShapes[] = BindingPayloadShapeEnum::from(value: $value);
            } catch (\ValueError $exception) {
                throw new InvalidEnumValueException(
                    fieldName: 'payload shape',
                    value: $value,
                    path: sprintf('%s.%s[%d]', $path, $key, $index),
                    previous: $exception,
                );
            }
        }

        return $payloadShapes;
    }

    /**
     * @param array<string, mixed> $data
     * @return list<AttributeDefinitionInterface>
     *
     * @throws UnexpectedValueTypeException
     */
    private function requireAttributeDefinitions(array $data, string $key, string $path): array
    {
        $attributeDataList = DecodedJsonValueAccessor::requireArray(
            data: $data,
            key: $key,
            path: $path,
        );

        $attributeDefinitions = [];

        foreach ($attributeDataList as $index => $attributeData) {
            if (!is_array($attributeData)) {
                throw new UnexpectedValueTypeException(
                    expectedType: 'object-like array',
                    path: sprintf('%s.%s[%d]', $path, $key, $index),
                );
            }

            /** @var array<string, mixed> $attributeData */
            $attributeDefinitions[] = $this->attributeDefinitionFactory->fromArray(
                data: $attributeData,
                path: sprintf('%s.%s[%d]', $path, $key, $index),
            );
        }

        return $attributeDefinitions;
    }
}
