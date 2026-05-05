<?php

declare(strict_types=1);

namespace ConsolidatedWitchcraft\BindingEngine\VocabularyLoader;

use ConsolidatedWitchcraft\BindingEngine\Vocabulary\AttributeDefinition;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Exceptions\InvalidAttributeDefinitionException;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions\AbstractVocabularyLoadingException;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions\DomainConstructionFailedException;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions\InvalidEnumValueException;

final readonly class AttributeDefinitionFactory
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws AbstractVocabularyLoadingException
     */
    public function fromArray(array $data, string $path): AttributeDefinition
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
        $valueType = self::requireAttributeValueTypeEnum(
            data: $data,
            path: $path,
        );
        $required = DecodedJsonValueAccessor::optionalBool(
            data: $data,
            key: 'required',
            path: $path,
            default: false,
        );
        $repeatable = DecodedJsonValueAccessor::optionalBool(
            data: $data,
            key: 'repeatable',
            path: $path,
            default: false,
        );
        $allowedValues = DecodedJsonValueAccessor::optionalStringList(
            data: $data,
            key: 'allowedValues',
            path: $path,
        );

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
            throw new DomainConstructionFailedException(
                subject: 'attribute definition',
                detail: $exception->getMessage(),
                path: $path,
                previous: $exception,
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws InvalidEnumValueException
     */
    private static function requireAttributeValueTypeEnum(array $data, string $path): AttributeValueTypeEnum
    {
        $value = DecodedJsonValueAccessor::requireString(
            data: $data,
            key: 'valueType',
            path: $path,
        );

        try {
            return AttributeValueTypeEnum::from(value: $value);
        } catch (\ValueError $exception) {
            throw new InvalidEnumValueException(
                fieldName: 'valueType',
                value: $value,
                path: sprintf('%s.%s', $path, 'valueType'),
                previous: $exception,
            );
        }
    }
}
