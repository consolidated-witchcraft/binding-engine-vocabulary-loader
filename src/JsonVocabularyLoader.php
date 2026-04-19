<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\VocabularyLoader;

use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidVocabularyException;
use ConundrumCodex\BindingEngine\Vocabulary\Vocabulary;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\AbstractVocabularyLoadingException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\DomainConstructionFailedException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\InvalidJsonException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\MalformedTopLevelStructureException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\UnexpectedValueTypeException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Interfaces\VocabularyLoaderInterface;

final readonly class JsonVocabularyLoader implements VocabularyLoaderInterface
{
    private const string ROOT_PATH = '';

    public function __construct(
        private BindingTypeDefinitionFactory $bindingTypeDefinitionFactory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        ),
    ) {
    }

    /**
     * @throws AbstractVocabularyLoadingException
     * @throws \JsonException
     */
    public function load(string $input): Vocabulary
    {
        $data = self::decodeJson(input: $input);

        $identifier = DecodedJsonValueAccessor::requireString(
            data: $data,
            key: 'identifier',
            path: self::ROOT_PATH,
        );
        $label = DecodedJsonValueAccessor::requireString(
            data: $data,
            key: 'label',
            path: self::ROOT_PATH,
        );
        $version = DecodedJsonValueAccessor::requireString(
            data: $data,
            key: 'version',
            path: self::ROOT_PATH,
        );

        $bindingTypes = DecodedJsonValueAccessor::requireArray(
            data: $data,
            key: 'bindingTypes',
            path: self::ROOT_PATH,
        );

        $bindingTypeDefinitions = [];

        foreach ($bindingTypes as $index => $bindingTypeData) {
            if (!is_array($bindingTypeData)) {
                throw new UnexpectedValueTypeException(
                    expectedType: 'object-like array',
                    path: sprintf('bindingTypes[%d]', $index),
                );
            }

            /** @var array<string, mixed> $bindingTypeData */
            $bindingTypeDefinitions[] = $this->bindingTypeDefinitionFactory->fromArray(
                data: $bindingTypeData,
                path: sprintf('bindingTypes[%d]', $index),
            );
        }

        try {
            return new Vocabulary(
                identifier: $identifier,
                label: $label,
                version: $version,
                bindingTypeDefinitions: $bindingTypeDefinitions,
            );
        } catch (InvalidVocabularyException $exception) {
            throw new DomainConstructionFailedException(
                subject: 'vocabulary',
                detail: $exception->getMessage(),
                previous: $exception,
            );
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws InvalidJsonException
     * @throws MalformedTopLevelStructureException
     * @throws \JsonException
     */
    private static function decodeJson(string $input): array
    {
        try {
            $decoded = json_decode($input, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new InvalidJsonException(
                detail: $exception->getMessage(),
                previous: $exception,
            );
        }

        if (!$decoded instanceof \stdClass) {
            throw new MalformedTopLevelStructureException();
        }

        /** @var array<string, mixed> $decodedArray */
        $decodedArray = json_decode($input, true, flags: JSON_THROW_ON_ERROR);

        return $decodedArray;
    }

}
