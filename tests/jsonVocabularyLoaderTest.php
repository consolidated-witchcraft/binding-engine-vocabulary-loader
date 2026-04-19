<?php

declare(strict_types=1);

use ConundrumCodex\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConundrumCodex\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\VocabularyLoadingException;
use ConundrumCodex\BindingEngine\VocabularyLoader\JsonVocabularyLoader;

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

        $vocabulary = $loader->load($json);

        expect($vocabulary->hasBindingTypeDefinition('person'))->toBeTrue();

        $bindingTypeDefinition = $vocabulary->getBindingTypeDefinition('person');

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

        $vocabulary = $loader->load($json);
        $bindingTypeDefinition = $vocabulary->getBindingTypeDefinition('event');

        expect($bindingTypeDefinition)->not->toBeNull()
            ->and($bindingTypeDefinition->getAllowedPayloadShapes())->toBe([
                BindingPayloadShapeEnum::AttributeList,
            ])
            ->and($bindingTypeDefinition->getAttributeDefinitions())->toHaveCount(2)
            ->and($bindingTypeDefinition->hasAttributeDefinition('type'))->toBeTrue()
            ->and($bindingTypeDefinition->hasAttributeDefinition('status'))->toBeTrue();

        $typeAttribute = $bindingTypeDefinition->getAttributeDefinition('type');
        $statusAttribute = $bindingTypeDefinition->getAttributeDefinition('status');

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
            fn () => $loader->load('{"bindingTypes": [}')
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid JSON:',
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
            fn () => $loader->load(json_encode(['not', 'an', 'object'], JSON_THROW_ON_ERROR))
        )->toThrow(
            VocabularyLoadingException::class
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Missing required key "bindingTypes".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected array at "bindingTypes".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected object-like array at "bindingTypes[0]".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Missing required key "identifier" at "bindingTypes[0]".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid payload shape "banana" at "bindingTypes[0].allowedPayloadShapes[0]".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid valueType "banana" at "bindingTypes[0].attributes[0].valueType".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid vocabulary: Vocabulary contains duplicate binding type definition "event".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid binding type definition at "bindingTypes[0]": Binding type definition contains duplicate attribute definition "type".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Missing required key "identifier".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Missing required key "label".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Missing required key "version".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected string at "identifier".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected string at "label".',
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
            fn () => $loader->load($json)
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected string at "version".',
        );
    }
);
