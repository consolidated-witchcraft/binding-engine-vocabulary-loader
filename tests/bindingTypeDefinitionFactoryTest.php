<?php

declare(strict_types=1);

use ConundrumCodex\BindingEngine\Vocabulary\Enums\BindingPayloadShapeEnum;
use ConundrumCodex\BindingEngine\VocabularyLoader\AttributeDefinitionFactory;
use ConundrumCodex\BindingEngine\VocabularyLoader\BindingTypeDefinitionFactory;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\VocabularyLoadingException;

it(
    'constructs a binding type definition from valid data',
    function () {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        $definition = $factory->fromArray(
            data: [
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
                        'identifier' => 'subject',
                        'label' => 'Subject',
                        'description' => 'The event subject.',
                        'valueType' => 'identifier',
                        'required' => true,
                        'repeatable' => false,
                    ],
                ],
            ],
            path: 'bindingTypes[0]',
        );

        expect($definition->getIdentifier())->toBe('event')
            ->and($definition->getLabel())->toBe('Event')
            ->and($definition->getDescription())->toBe('An event binding.')
            ->and($definition->getAllowedPayloadShapes())->toBe([BindingPayloadShapeEnum::AttributeList])
            ->and($definition->getAttributeDefinitions())->toHaveCount(2)
            ->and($definition->hasAttributeDefinition('type'))->toBeTrue()
            ->and($definition->hasAttributeDefinition('subject'))->toBeTrue()
            ->and($definition->hasAttributeDefinition('missing'))->toBeFalse();
    }
);

it(
    'constructs a binding type definition with multiple payload shapes',
    function () {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        $definition = $factory->fromArray(
            data: [
                'identifier' => 'person',
                'label' => 'Person',
                'description' => 'A person binding.',
                'allowedPayloadShapes' => ['shorthand', 'attribute_list'],
                'attributes' => [],
            ],
            path: 'bindingTypes[0]',
        );

        expect($definition->allowsPayloadShape(BindingPayloadShapeEnum::Shorthand))->toBeTrue()
            ->and($definition->allowsPayloadShape(BindingPayloadShapeEnum::AttributeList))->toBeTrue()
            ->and($definition->getAttributeDefinitions())->toBe([]);
    }
);

it(
    'rejects missing required keys',
    function (string $missingKey) {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        $data = [
            'identifier' => 'event',
            'label' => 'Event',
            'description' => 'An event binding.',
            'allowedPayloadShapes' => ['attribute_list'],
            'attributes' => [],
        ];

        unset($data[$missingKey]);

        expect(
            fn () => $factory->fromArray(
                data: $data,
                path: 'bindingTypes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            sprintf(
                'Missing required key "%s" at "bindingTypes[0]".',
                $missingKey,
            ),
        );
    }
)->with(function (): iterable {
    yield 'identifier' => 'identifier';
    yield 'label' => 'label';
    yield 'description' => 'description';
    yield 'allowedPayloadShapes' => 'allowedPayloadShapes';
    yield 'attributes' => 'attributes';
});

it(
    'rejects non-string scalar fields',
    function (string $key, mixed $invalidValue) {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        $data = [
            'identifier' => 'event',
            'label' => 'Event',
            'description' => 'An event binding.',
            'allowedPayloadShapes' => ['attribute_list'],
            'attributes' => [],
        ];

        $data[$key] = $invalidValue;

        expect(
            fn () => $factory->fromArray(
                data: $data,
                path: 'bindingTypes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            sprintf(
                'Expected string at "bindingTypes[0].%s".',
                $key,
            ),
        );
    }
)->with(function (): iterable {
    yield 'identifier as int' => ['identifier', 123];
    yield 'label as bool' => ['label', true];
    yield 'description as array' => ['description', []];
});

it(
    'rejects non-array allowed payload shapes',
    function () {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => 'attribute_list',
                    'attributes' => [],
                ],
                path: 'bindingTypes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected array at "bindingTypes[0].allowedPayloadShapes".',
        );
    }
);

it(
    'rejects non-string allowed payload shape members',
    function () {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list', 123],
                    'attributes' => [],
                ],
                path: 'bindingTypes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected string at "bindingTypes[0].allowedPayloadShapes[1]".',
        );
    }
);

it(
    'rejects invalid payload shape values',
    function () {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['banana'],
                    'attributes' => [],
                ],
                path: 'bindingTypes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid payload shape "banana" at "bindingTypes[0].allowedPayloadShapes[0]".',
        );
    }
);

it(
    'rejects non-array attributes collections',
    function () {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => 'not-an-array',
                ],
                path: 'bindingTypes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected array at "bindingTypes[0].attributes".',
        );
    }
);

it(
    'rejects non-array attribute members',
    function () {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => ['not-an-array'],
                ],
                path: 'bindingTypes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected object-like array at "bindingTypes[0].attributes[0]".',
        );
    }
);

it(
    'wraps nested attribute factory failures with nested path context',
    function () {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'event',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => [
                        [
                            'identifier' => 'bad_identifier',
                            'label' => 'Type',
                            'description' => 'The event type.',
                            'valueType' => 'string',
                        ],
                    ],
                ],
                path: 'bindingTypes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid attribute definition at "bindingTypes[0].attributes[0]": Invalid attribute identifier "bad_identifier".',
        );
    }
);

it(
    'wraps invalid domain construction errors with path context',
    function () {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'bad_identifier',
                    'label' => 'Event',
                    'description' => 'An event binding.',
                    'allowedPayloadShapes' => ['attribute_list'],
                    'attributes' => [],
                ],
                path: 'bindingTypes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid binding type definition at "bindingTypes[0]": Invalid binding type identifier "bad_identifier".',
        );
    }
);

it(
    'wraps duplicate attribute definition failures with path context',
    function () {
        $factory = new BindingTypeDefinitionFactory(
            new AttributeDefinitionFactory(),
        );

        expect(
            fn () => $factory->fromArray(
                data: [
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
                            'description' => 'Duplicate.',
                            'valueType' => 'string',
                        ],
                    ],
                ],
                path: 'bindingTypes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid binding type definition at "bindingTypes[0]": Binding type definition contains duplicate attribute definition "type".',
        );
    }
);
