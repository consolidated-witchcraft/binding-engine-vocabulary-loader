<?php

declare(strict_types=1);

use ConundrumCodex\BindingEngine\Vocabulary\Enums\AttributeValueTypeEnum;
use ConundrumCodex\BindingEngine\VocabularyLoader\AttributeDefinitionFactory;
use ConundrumCodex\BindingEngine\VocabularyLoader\Exceptions\VocabularyLoadingException;

it(
    'constructs an attribute definition from valid non-enum data',
    function () {
        $factory = new AttributeDefinitionFactory();

        $definition = $factory->fromArray(
            data: [
                'identifier' => 'subject',
                'label' => 'Subject',
                'description' => 'The event subject.',
                'valueType' => 'string',
                'required' => true,
                'repeatable' => false,
            ],
            path: 'bindingTypes[0].attributes[0]',
        );

        expect($definition->getIdentifier())->toBe('subject')
            ->and($definition->getLabel())->toBe('Subject')
            ->and($definition->getDescription())->toBe('The event subject.')
            ->and($definition->getValueType())->toBe(AttributeValueTypeEnum::String)
            ->and($definition->isRequired())->toBeTrue()
            ->and($definition->isRepeatable())->toBeFalse()
            ->and($definition->getAllowedValues())->toBeNull()
            ->and($definition->hasAllowedValues())->toBeFalse();
    }
);

it(
    'constructs an attribute definition from valid enum data',
    function () {
        $factory = new AttributeDefinitionFactory();

        $definition = $factory->fromArray(
            data: [
                'identifier' => 'status',
                'label' => 'Status',
                'description' => 'The event status.',
                'valueType' => 'enum',
                'required' => true,
                'repeatable' => false,
                'allowedValues' => ['draft', 'published'],
            ],
            path: 'bindingTypes[0].attributes[0]',
        );

        expect($definition->getIdentifier())->toBe('status')
            ->and($definition->getLabel())->toBe('Status')
            ->and($definition->getDescription())->toBe('The event status.')
            ->and($definition->getValueType())->toBe(AttributeValueTypeEnum::Enum)
            ->and($definition->isRequired())->toBeTrue()
            ->and($definition->isRepeatable())->toBeFalse()
            ->and($definition->getAllowedValues())->toBe(['draft', 'published'])
            ->and($definition->hasAllowedValues())->toBeTrue();
    }
);

it(
    'defaults optional booleans when omitted',
    function () {
        $factory = new AttributeDefinitionFactory();

        $definition = $factory->fromArray(
            data: [
                'identifier' => 'subject',
                'label' => 'Subject',
                'description' => 'The event subject.',
                'valueType' => 'identifier',
            ],
            path: 'bindingTypes[0].attributes[0]',
        );

        expect($definition->isRequired())->toBeFalse()
            ->and($definition->isRepeatable())->toBeFalse();
    }
);

it(
    'rejects missing required string keys',
    function (string $missingKey) {
        $factory = new AttributeDefinitionFactory();

        $data = [
            'identifier' => 'subject',
            'label' => 'Subject',
            'description' => 'The event subject.',
            'valueType' => 'string',
        ];

        unset($data[$missingKey]);

        expect(
            fn () => $factory->fromArray(
                data: $data,
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            sprintf(
                'Missing required key "%s" at "bindingTypes[0].attributes[0]".',
                $missingKey,
            ),
        );
    }
)->with(function (): iterable {
    yield 'identifier' => 'identifier';
    yield 'label' => 'label';
    yield 'description' => 'description';
    yield 'valueType' => 'valueType';
});

it(
    'rejects non-string required scalar fields',
    function (string $key, mixed $invalidValue) {
        $factory = new AttributeDefinitionFactory();

        $data = [
            'identifier' => 'subject',
            'label' => 'Subject',
            'description' => 'The event subject.',
            'valueType' => 'string',
        ];

        $data[$key] = $invalidValue;

        expect(
            fn () => $factory->fromArray(
                data: $data,
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            sprintf(
                'Expected string at "bindingTypes[0].attributes[0].%s".',
                $key,
            ),
        );
    }
)->with(function (): iterable {
    yield 'identifier as int' => ['identifier', 123];
    yield 'label as bool' => ['label', true];
    yield 'description as array' => ['description', []];
    yield 'valueType as int' => ['valueType', 123];
});

it(
    'rejects invalid value types',
    function () {
        $factory = new AttributeDefinitionFactory();

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'subject',
                    'label' => 'Subject',
                    'description' => 'The event subject.',
                    'valueType' => 'banana',
                ],
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid valueType "banana" at "bindingTypes[0].attributes[0].valueType".',
        );
    }
);

it(
    'rejects non-boolean optional flags',
    function (string $key, mixed $invalidValue) {
        $factory = new AttributeDefinitionFactory();

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'subject',
                    'label' => 'Subject',
                    'description' => 'The event subject.',
                    'valueType' => 'string',
                    $key => $invalidValue,
                ],
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            sprintf(
                'Expected boolean at "bindingTypes[0].attributes[0].%s".',
                $key,
            ),
        );
    }
)->with(function (): iterable {
    yield 'required as string' => ['required', 'yes'];
    yield 'repeatable as int' => ['repeatable', 1];
});

it(
    'rejects non-array allowed values',
    function () {
        $factory = new AttributeDefinitionFactory();

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'status',
                    'label' => 'Status',
                    'description' => 'The event status.',
                    'valueType' => 'enum',
                    'allowedValues' => 'draft',
                ],
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected array at "bindingTypes[0].attributes[0].allowedValues".',
        );
    }
);

it(
    'rejects non-string allowed values members',
    function () {
        $factory = new AttributeDefinitionFactory();

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'status',
                    'label' => 'Status',
                    'description' => 'The event status.',
                    'valueType' => 'enum',
                    'allowedValues' => ['draft', 123],
                ],
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Expected string at "bindingTypes[0].attributes[0].allowedValues[1]".',
        );
    }
);

it(
    'wraps invalid domain construction errors with path context',
    function () {
        $factory = new AttributeDefinitionFactory();

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'bad_identifier',
                    'label' => 'Label',
                    'description' => 'Description',
                    'valueType' => 'string',
                ],
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid attribute definition at "bindingTypes[0].attributes[0]": Invalid attribute identifier "bad_identifier".',
        );
    }
);

it(
    'wraps enum domain rule failures with path context',
    function () {
        $factory = new AttributeDefinitionFactory();

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'status',
                    'label' => 'Status',
                    'description' => 'Description',
                    'valueType' => 'enum',
                ],
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid attribute definition at "bindingTypes[0].attributes[0]": Enum attributes must define allowed values.',
        );
    }
);

it(
    'wraps non-enum allowed values domain rule failures with path context',
    function () {
        $factory = new AttributeDefinitionFactory();

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'status',
                    'label' => 'Status',
                    'description' => 'Description',
                    'valueType' => 'string',
                    'allowedValues' => ['draft', 'published'],
                ],
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid attribute definition at "bindingTypes[0].attributes[0]": Allowed values may only be defined for enum value types.',
        );
    }
);

it(
    'wraps empty allowed values array domain rule failures with path context',
    function () {
        $factory = new AttributeDefinitionFactory();

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'status',
                    'label' => 'Status',
                    'description' => 'Description',
                    'valueType' => 'enum',
                    'allowedValues' => [],
                ],
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid attribute definition at "bindingTypes[0].attributes[0]": Allowed values must not be an empty array.',
        );
    }
);

it(
    'wraps blank allowed value member domain rule failures with path context',
    function () {
        $factory = new AttributeDefinitionFactory();

        expect(
            fn () => $factory->fromArray(
                data: [
                    'identifier' => 'status',
                    'label' => 'Status',
                    'description' => 'Description',
                    'valueType' => 'enum',
                    'allowedValues' => ['draft', '   '],
                ],
                path: 'bindingTypes[0].attributes[0]',
            )
        )->toThrow(
            VocabularyLoadingException::class,
            'Invalid attribute definition at "bindingTypes[0].attributes[0]": Allowed values must be non-empty strings.',
        );
    }
);
