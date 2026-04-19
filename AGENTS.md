# AGENTS.md — Binding Vocabulary JSON Loader

## Purpose

This repository implements the **JSON loading layer** for the Conundrum Codex binding system.

It is responsible for turning **user-authored JSON configuration** into validated vocabulary domain objects from the vocabulary library.

This library is **not** the vocabulary model itself, and it is **not** the parser.

---

## Architectural Position

This repository is one stage in a larger pipeline:

1. Parser
    - Parses source text into AST + syntax diagnostics

2. Vocabulary / Validation
    - Defines binding types and attributes
    - Validates parsed bindings semantically

3. JSON Loader (this library)
    - Loads user-authored JSON
    - Validates config structure
    - Hydrates vocabulary objects

4. Inference (future)
    - Derives additional meaning

5. Projection (future)
    - Produces application-facing models

This library exists to bridge **external JSON configuration** and the **core vocabulary domain model**.

---

## Core Responsibility

This library answers:

> “Given some JSON, can it be turned into a valid `Vocabulary` object?”

It must:

- decode JSON
- validate JSON structure
- map JSON fields onto vocabulary domain objects
- produce clear load-time failures when config is malformed

It must not:

- parse binding syntax
- validate AST nodes
- perform inference
- perform projection
- resolve links
- mutate vocabulary objects after construction

---

## Core Principles

### 1. Separation of Concerns

- The parser owns syntax parsing
- The vocabulary library owns schema/domain rules
- This library owns JSON decoding and config hydration

Do not reimplement vocabulary semantics here unless required to validate raw input shape before object construction.

---

### 2. Fail Early, Fail Clearly

- Invalid JSON MUST fail immediately
- Invalid config structure MUST fail immediately
- Error messages SHOULD identify the location of the problem
- Prefer path-aware messages such as:
    - `bindingTypes[0].identifier`
    - `bindingTypes[2].attributes[1].valueType`

---

### 3. Domain Objects Are the Source of Truth

This library must not create a parallel vocabulary model.

It should hydrate:

- `Vocabulary`
- `BindingTypeDefinition`
- `AttributeDefinition`

from the core vocabulary library.

---

### 4. Keep JSON-Specific Logic Here

JSON-specific behaviour belongs here, not in the core vocabulary library.

Examples:

- JSON decoding
- type checking decoded arrays
- enum string coercion
- structural validation of raw config arrays

Do not push JSON concerns into the domain objects.

---

### 5. Deterministic Behaviour

Given the same JSON input, the loader MUST produce the same result every time.

No hidden defaults unless they are explicitly documented and stable.

---

## Expected Input Shape

The loader is expected to support a structure broadly like this:

```json
{
  "bindingTypes": [
    {
      "identifier": "event",
      "label": "Event",
      "description": "An event binding.",
      "allowedPayloadShapes": ["attribute_list"],
      "attributes": [
        {
          "identifier": "type",
          "label": "Type",
          "description": "The event type.",
          "valueType": "string",
          "required": true,
          "repeatable": false
        },
        {
          "identifier": "status",
          "label": "Status",
          "description": "The event status.",
          "valueType": "enum",
          "required": false,
          "repeatable": false,
          "allowedValues": ["draft", "published"]
        }
      ]
    }
  ]
}
```

If the shape changes, update tests and README together.

## Recommended Components

This library may include components like:
- VocabularyLoaderInterface
- JsonVocabularyLoader
- VocabularyLoadingException

Optional internal helpers:
- BindingTypeDefinitionLoader
- AttributeDefinitionLoader
Keep helpers small and single-purpose.

## Exceptions
Use dedicated load-time exceptions for:
- invalid JSON
- missing required keys
- wrong value types in decoded arrays
- invalid enum string values
- malformed top-level structure

These exceptions represent configuration loading failures, not domain-construction failures.

If a domain constructor throws, catch and wrap it with path context where useful.

## Error Message Guidelines

Messages should be:

- precise
- stable
- human-readable
- path-aware when possible

Good examples:

- `Missing required key "bindingTypes".`
- `Expected array at "bindingTypes".`
- `Invalid valueType "banana" at "bindingTypes[0].attributes[1].valueType".`
- `Invalid payload shape "banana" at "bindingTypes[0].allowedPayloadShapes[0]".`

Avoid vague messages like:

- `Invalid config`
- `Something went wrong`

## Constraints
### DO
- Decode JSON safely
- Validate raw data shape before hydration
- Reuse the vocabulary library’s domain objects
- Keep mapping code explicit
- Write focused Pest tests
- Include path context in failures where practical
### DO NOT
- Do not parse binding source text
- Do not validate AST nodes
- Do not implement inference
- Do not silently ignore malformed config
- Do not create alternative domain models
- Do not hardcode world-specific binding types

## Testing Guidelines

Use Pest.

Cover at least:
- valid JSON loads successfully
- invalid JSON fails
- missing bindingTypes fails
- non-array bindingTypes fails
- missing required binding type keys fail
- missing required attribute keys fail
- invalid valueType fails
- invalid allowedPayloadShapes fails
- valid enums hydrate correctly
- duplicate binding type identifiers fail via domain construction
- duplicate attribute identifiers fail via domain construction

Where possible, assert on:
- exception class
- message contents
- path fragments

## Extensibility

This loader is for JSON specifically.
Do not design it as though JSON must be the only config source forever.

The broader system may later support:
- YAML
- database-backed config
- admin UI-defined config

Keep the core loader logic format-specific but domain-agnostic.

## Domain Boundary Reminder

This library should stop at:
“Here is a valid Vocabulary object.”

It should not proceed to:
- binding validation
- shorthand interpretation
- inference
- projection

Those belong to downstream layers.
## Summary
This repository converts JSON configuration into validated vocabulary domain objects.

It must remain:
- format-aware
- domain-respectful
- deterministic
- explicit
- easy to debug

## Coding Standards
Coding standards are contained within the `./codingstandards/` subdirectory, and MUST be followed.