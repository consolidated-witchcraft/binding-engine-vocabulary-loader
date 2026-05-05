<?php

declare(strict_types=1);

use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConsolidatedWitchcraft\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions\DomainConstructionFailedException;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions\InvalidEnumValueException;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions\InvalidJsonException;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions\MalformedTopLevelStructureException;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions\MissingRequiredKeyException;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\Exceptions\UnexpectedValueTypeException;
use ConsolidatedWitchcraft\BindingEngine\VocabularyLoader\JsonVocabularyLoader;

it(
    'loads a minimal valid vocabulary from json',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'bindingTypes' => [
                [
                    'identifier' => 'person',
                    'label' => 'Person',
                    'description' => 'A person binding.',
                    'allowedPayloadShapes' => ['shorthand'],
                    'attributes' => [],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $vocabulary = $loader->load(input: $json);

        expect($vocabulary->hasBindingTypeDefinition(identifier: 'person'))->toBeTrue();

        $bindingTypeDefinition = $vocabulary->getBindingTypeDefinition(identifier: 'person');

        expect($bindingTypeDefinition)->not->toBeNull()
            ->and($bindingTypeDefinition->getIdentifier())->toBe('person')
            ->and($bindingTypeDefinition->getLabel())->toBe('Person')
            ->and($bindingTypeDefinition->getDescription())->toBe('A person binding.')
            ->and($bindingTypeDefinition->getAllowedPayloadShapes())->toBe([
                BindingPayloadShapeEnum::Shorthand,
            ])
            ->and($bindingTypeDefinition->getAttributeDefinitions())->toBe([]);
    }
);

it(
    'loads a vocabulary with nested attribute definitions from json',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'bindingTypes' => [
                [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => [
                        [
                            'identifier' => 'type',
                            'label' => 'Type',
                            'description' => 'The event type.',
                            'valueType' => 'string',
                            'required' => true,
                            'repeatable' => false,
                        ],
                        [
                            'identifier' => 'status',
                            'label' => 'Status',
                            'description' => 'The event status.',
                            'valueType' => 'enum',
                            'required' => false,
                            'repeatable' => false,
                            'allowedValues' => ['draft', 'published'],
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $vocabulary = $loader->load(input: $json);
        $bindingTypeDefinition = $vocabulary->getBindingTypeDefinition(identifier: 'event');

        expect($bindingTypeDefinition)->not->toBeNull()
            ->and($bindingTypeDefinition->getAllowedPayloadShapes())->toBe([
                BindingPayloadShapeEnum::AttributeList,
            ])
            ->and($bindingTypeDefinition->getAttributeDefinitions())->toHaveCount(2)
            ->and($bindingTypeDefinition->hasAttributeDefinition(identifier: 'type'))->toBeTrue()
            ->and($bindingTypeDefinition->hasAttributeDefinition(identifier: 'status'))->toBeTrue();

        $typeAttribute = $bindingTypeDefinition->getAttributeDefinition(identifier: 'type');
        $statusAttribute = $bindingTypeDefinition->getAttributeDefinition(identifier: 'status');

        expect($typeAttribute)->not->toBeNull()
            ->and($typeAttribute->getValueType())->toBe(AttributeValueTypeEnum::String)
            ->and($typeAttribute->isRequired())->toBeTrue()
            ->and($typeAttribute->isRepeatable())->toBeFalse()
            ->and($statusAttribute)->not->toBeNull()
            ->and($statusAttribute->getValueType())->toBe(AttributeValueTypeEnum::Enum)
            ->and($statusAttribute->getAllowedValues())->toBe(['draft', 'published']);

    }
);

it(
    'rejects invalid json',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        expect(
            fn () => $loader->load(input: '{"bindingTypes": [}')
        )->toThrow(
            exception: InvalidJsonException::class,
            exceptionMessage: 'Invalid JSON:',
        );
    }
);

it(
    'rejects non-object top-level json values',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        expect(
            fn () => $loader->load(input: json_encode(['not', 'an', 'object'], JSON_THROW_ON_ERROR))
        )->toThrow(
            exception: MalformedTopLevelStructureException::class,
        );
    }
);

it(
    'rejects missing top-level bindingTypes key',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'foo' => 'bar',
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: MissingRequiredKeyException::class,
            exceptionMessage: 'Missing required key "bindingTypes".',
        );
    }
);

it(
    'rejects non-array bindingTypes values',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'bindingTypes' => 'not-an-array',
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: UnexpectedValueTypeException::class,
            exceptionMessage: 'Expected array at "bindingTypes".',
        );
    }
);

it(
    'rejects non-array binding type members',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'bindingTypes' => ['not-an-array'],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: UnexpectedValueTypeException::class,
            exceptionMessage: 'Expected object-like array at "bindingTypes[0]".',
        );
    }
);

it(
    'wraps missing nested binding type keys with path context',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'bindingTypes' => [
                [
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => [],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: MissingRequiredKeyException::class,
            exceptionMessage: 'Missing required key "identifier" at "bindingTypes[0]".',
        );
    }
);

it(
    'wraps invalid nested payload shape values with path context',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'bindingTypes' => [
                [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['banana'],
                    'attributes' => [],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: InvalidEnumValueException::class,
            exceptionMessage: 'Invalid payload shape "banana" at "bindingTypes[0].allowedPayloadShapes[0]".',
        );
    }
);

it(
    'wraps invalid nested attribute value types with path context',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'bindingTypes' => [
                [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => [
                        [
                            'identifier' => 'status',
                            'label' => 'Status',
                            'description' => 'The event status.',
                            'valueType' => 'banana',
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: InvalidEnumValueException::class,
            exceptionMessage: 'Invalid valueType "banana" at "bindingTypes[0].attributes[0].valueType".',
        );
    }
);

it(
    'wraps duplicate binding type definition failures from the vocabulary layer',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'bindingTypes' => [
                [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => [],
                ],
                [
                    'identifier' => 'event',
                    'label' => 'Event Again',
                    'description' => 'A duplicate event binding.',
                    'allowedPayloadShapes' => ['shorthand'],
                    'attributes' => [],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: DomainConstructionFailedException::class,
            exceptionMessage: 'Invalid vocabulary: Vocabulary contains duplicate binding type definition "event".',
        );
    }
);

it(
    'wraps duplicate attribute definition failures with full nested path context',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'bindingTypes' => [
                [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => [
                        [
                            'identifier' => 'type',
                            'label' => 'Type',
                            'description' => 'The event type.',
                            'valueType' => 'string',
                        ],
                        [
                            'identifier' => 'type',
                            'label' => 'Type Again',
                            'description' => 'Duplicate type.',
                            'valueType' => 'string',
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: DomainConstructionFailedException::class,
            exceptionMessage: 'Invalid binding type definition at "bindingTypes[0]": Binding type definition contains duplicate attribute definition "type".',
        );
    }
);

it(
    'rejects missing top-level identifier',
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'label' => 'Test Label',
            'version' => '0.1.0',
            'bindingTypes' => [],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: MissingRequiredKeyException::class,
            exceptionMessage: 'Missing required key "identifier".',
        );
    }
);

it(
    'rejects missing top-level label',
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 'test-vocabulary',
            'version' => '0.1.0',
            'bindingTypes' => [],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: MissingRequiredKeyException::class,
            exceptionMessage: 'Missing required key "label".',
        );
    }
);

it(
    'rejects missing top-level version',
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 'test-vocabulary',
            'label' => 'Test Label',
            'bindingTypes' => [],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: MissingRequiredKeyException::class,
            exceptionMessage: 'Missing required key "version".',
        );
    }
);

it(
    'rejects non-string top-level identifier',
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 123,
            'label' => 'Test Label',
            'version' => '0.1.0',
            'bindingTypes' => [],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: UnexpectedValueTypeException::class,
            exceptionMessage: 'Expected string at "identifier".',
        );
    }
);

it(
    'rejects non-string top-level label',
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 'test-vocabulary',
            'label' => true,
            'version' => '0.1.0',
            'bindingTypes' => [],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: UnexpectedValueTypeException::class,
            exceptionMessage: 'Expected string at "label".',
        );
    }
);

it(
    /**
     * @throws JsonException
     */
    'rejects non-string top-level version',
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 'test-vocabulary',
            'label' => 'Test Label',
            'version' => 100,
            'bindingTypes' => [],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: UnexpectedValueTypeException::class,
            exceptionMessage: 'Expected string at "version".',
        );
    }
);
it(
    'rejects non-object top-level json values with a clear message',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        expect(
            fn () => $loader->load(input: json_encode(['not', 'an', 'object'], JSON_THROW_ON_ERROR))
        )->toThrow(
            exception: MalformedTopLevelStructureException::class,
            exceptionMessage: 'Expected top-level JSON object.',
        );
    }
);

it(
    'wraps invalid top-level identifier values from the vocabulary layer',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 'Bad Identifier',
            'label' => 'Test Label',
            'version' => '0.1.0',
            'bindingTypes' => [],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: DomainConstructionFailedException::class,
            exceptionMessage: "Invalid vocabulary: The identifier 'Bad Identifier' is invalid. Identifiers may only contain lowercase letters and hyphens, and may not be empty.",
        );
    }
);

it(
    'wraps invalid top-level label values from the vocabulary layer',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 'test-vocabulary',
            'label' => 'Bad🔥Label',
            'version' => '0.1.0',
            'bindingTypes' => [],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: DomainConstructionFailedException::class,
            exceptionMessage: "Invalid vocabulary: The label 'Bad🔥Label' is invalid. Labels may only contain numbers, letters, spaces, hyphens, apostrophes, ampersands, commas, parentheses, colons and full-stops (periods).",
        );
    }
);

it(
    'wraps invalid top-level version values from the vocabulary layer',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 'test-vocabulary',
            'label' => 'Test Label',
            'version' => '1.2',
            'bindingTypes' => [],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: DomainConstructionFailedException::class,
            exceptionMessage: "Invalid vocabulary: The version '1.2' is invalid. Versions must conform to semantic versioning 2.0 standards.",
        );
    }
);

it(
    'wraps missing nested attribute keys with full path context',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 'test-vocabulary',
            'label' => 'Test Label',
            'version' => '0.1.0',
            'bindingTypes' => [
                [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => [
                        [
                            'label' => 'Type',
                            'description' => 'The event type.',
                            'valueType' => 'string',
                        ],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: MissingRequiredKeyException::class,
            exceptionMessage: 'Missing required key "identifier" at "bindingTypes[0].attributes[0]".',
        );
    }
);

it(
    'wraps non-array nested attributes collections with full path context',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 'test-vocabulary',
            'label' => 'Test Label',
            'version' => '0.1.0',
            'bindingTypes' => [
                [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => 'not-an-array',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: UnexpectedValueTypeException::class,
            exceptionMessage: 'Expected array at "bindingTypes[0].attributes".',
        );
    }
);

it(
    'wraps non-array nested attribute members with full path context',
    /**
     * @throws JsonException
     */
    function () {
        $loader = new JsonVocabularyLoader();

        $json = json_encode([
            'identifier' => 'test-vocabulary',
            'label' => 'Test Label',
            'version' => '0.1.0',
            'bindingTypes' => [
                [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => [
                        'not-an-array',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        expect(
            fn () => $loader->load(input: $json)
        )->toThrow(
            exception: UnexpectedValueTypeException::class,
            exceptionMessage: 'Expected object-like array at "bindingTypes[0].attributes[0]".',
        );
    }
);
