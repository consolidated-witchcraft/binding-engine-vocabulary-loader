<?php

declare(strict_types=1);

namespace ConundrumCodex\BindingEngine\VocabularyLoader;

use ConundrumCodex\BindingEngine\Vocabulary\Exceptions\InvalidVocabularyException;
use ConundrumCodex\BindingEngine\Vocabulary\Vocabulary;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\VocabularyLoadingException;
use ConundrumCodex\BindingEngine\VocabularyLoader\Interfaces\VocabularyLoaderInterface;

final readonly class JsonVocabularyLoader implements VocabularyLoaderInterface
{
    public function __construct(
        private BindingTypeDefinitionFactory $bindingTypeDefinitionFactory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        ),
    ) {
    }

    /**
     * @throws VocabularyLoadingException
     */
    public function load(string $input): Vocabulary
    {
        $data = $this->decodeJson($input);

        $identifier = $this->requireString($data, 'identifier', '');
        $label = $this->requireString($data, 'label', '');
        $version = $this->requireString($data, 'version', '');

        if (!array_key_exists('bindingTypes', $data)) {
            throw new VocabularyLoadingException('Missing required key "bindingTypes".');
        }

        if (!is_array($data['bindingTypes'])) {
            throw new VocabularyLoadingException('Expected array at "bindingTypes".');
        }

        $bindingTypeDefinitions = [];

        foreach ($data['bindingTypes'] as $index => $bindingTypeData) {
            if (!is_array($bindingTypeData)) {
                throw new VocabularyLoadingException(
                    sprintf('Expected object-like array at "bindingTypes[%d]".', $index),
                );
            }

            /** @var array<string, mixed> $bindingTypeData */
            $bindingTypeDefinitions[] = $this->bindingTypeDefinitionFactory->fromArray(
                $bindingTypeData,
                sprintf('bindingTypes[%d]', $index),
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
            throw new VocabularyLoadingException(
                sprintf('Invalid vocabulary: %s', $exception->getMessage()),
                0,
                $exception,
            );
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws VocabularyLoadingException
     * @throws \JsonException
     */
    private function decodeJson(string $input): array
    {
        try {
            $decoded = json_decode($input, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new VocabularyLoadingException(
                sprintf('Invalid JSON: %s', $exception->getMessage()),
                0,
                $exception,
            );
        }

        if (!$decoded instanceof \stdClass) {
            throw new VocabularyLoadingException('Expected top-level JSON object.');
        }

        /** @var array<string, mixed> $decodedArray */
        $decodedArray = json_decode($input, true, flags: JSON_THROW_ON_ERROR);

        return $decodedArray;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws VocabularyLoadingException
     */
    private function requireString(array $data, string $key, string $path): string
    {
        if (!array_key_exists($key, $data)) {
            if ($path === '') {
                throw new VocabularyLoadingException(
                    sprintf('Missing required key "%s".', $key),
                );
            }

            throw new VocabularyLoadingException(
                sprintf('Missing required key "%s" at "%s".', $key, $path),
            );
        }

        if (!is_string($data[$key])) {
            if ($path === '') {
                throw new VocabularyLoadingException(
                    sprintf('Expected string at "%s".', $key),
                );
            }

            throw new VocabularyLoadingException(
                sprintf('Expected string at "%s.%s".', $path, $key),
            );
        }

        return $data[$key];
    }
}
