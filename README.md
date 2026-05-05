# Consolidate Witchcraft BindingEngine — Binding Vocabulary JSON Loader

This library provides a **JSON-based loader for binding vocabularies** in the BindingEngine system.

It allows users to define binding types, attributes, and validation rules in JSON, and converts that configuration into a validated `Vocabulary` object.

---

## Overview

The loader answers the question:

> “Can this JSON configuration be turned into a valid binding vocabulary?”

It operates **before validation**, and produces the domain objects required by the vocabulary layer.

---

## Architectural Context

The BindingEngine is composed of multiple layers:

### 1. Parser
- Parses source text into an AST
- Produces syntax diagnostics

### 2. Vocabulary / Validation
- Defines binding types and attributes
- Validates AST nodes semantically

### 3. JSON Loader (this library)
- Loads vocabulary definitions from JSON
- Validates configuration structure
- Hydrates domain objects

### 4. Inference (future)
- Derives additional meaning

### 5. Projection (future)
- Produces application-facing models

---

## Responsibilities
This library:
- Decodes JSON input
- Validates configuration structure
- Maps JSON to vocabulary domain objects
- Produces clear, deterministic load-time errors

---

## Non-Responsibilities
This library does **not**:
- Parse binding syntax
- Validate AST nodes
- Perform inference
- Perform projection
- Resolve links
- Render output

---

## Installation

```bash
composer require conundrum-codex/binding-vocabulary-loader
```
---
## Usage
```php
use ConundrumCodex\BindingEngine\VocabularyLoader\JsonVocabularyLoader;

$json = file_get_contents('vocabulary.json');

$loader = new JsonVocabularyLoader();

$vocabulary = $loader->load($json);
```

The returned $vocabulary is a fully constructed and validated instance of:
```php
ConundrumCodex\BindingEngine\Vocabulary\Vocabulary
```
---
## Example JSON
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
          "allowedValues": ["draft", "published"]
        }
      ]
    }
  ]
}
```
---
## JSON Structure
### Top-Level
| Field        | Type  | Required | Description                      |
| ------------ | ----- | -------- | -------------------------------- |
| bindingTypes | array | yes      | List of binding type definitions |
### Binding Type
| Field                | Type     | Required | Description                     |
| -------------------- | -------- | -------- | ------------------------------- |
| identifier           | string   | yes      | Unique binding type identifier  |
| label                | string   | yes      | Human-readable label            |
| description          | string   | yes      | Description of the binding type |
| allowedPayloadShapes | string[] | yes      | Allowed payload shapes          |
| attributes           | array    | yes      | Attribute definitions           |
### Attribute
| Field         | Type     | Required            | Description                         |
| ------------- | -------- | ------------------- | ----------------------------------- |
| identifier    | string   | yes                 | Attribute identifier                |
| label         | string   | yes                 | Human-readable label                |
| description   | string   | yes                 | Description                         |
| valueType     | string   | yes                 | Value type (`string`, `enum`, etc.) |
| required      | boolean  | no                  | Defaults to `false`                 |
| repeatable    | boolean  | no                  | Defaults to `false`                 |
| allowedValues | string[] | required for `enum` | Allowed values for enums            |
### Supported Value Types
- string
- identifier
- enum
(Additional types may be added in the future.)

### Supported Payload Shapes
- `shorthand`
- `attribute_list`
---
## Error Handling
Invalid configurations will throw a VocabularyLoadingException (or equivalent), with clear and precise messages.
Examples:

- Missing required keys
- Invalid enum values
- Incorrect data types
- Malformed structure

Where possible, errors include path context, e.g.:
`bindingTypes[0].attributes[1].valueType`
---
## Design Principles
### Separation of Concerns
- JSON handling lives here
- Domain rules live in the vocabulary library

### Fail Fast
Invalid configuration is rejected immediately with clear errors.
### Deterministic
The same JSON input always produces the same vocabulary output.

### Domain Integrity
All objects returned are valid instances of:
- `Vocabulary`
- `BindingTypeDefinition`
- `AttributeDefinition`
---
## Testing
This library uses Pest.

Tests cover:
- Valid configurations
- Invalid JSON
- Missing keys
- Invalid enum values
- Structural errors
- Domain validation failures
---
## Future Extensions
This loader is JSON-specific.
Future loaders may support:
- YAML
- database-backed configuration
- UI-driven configuration
---
## Summary
This library bridges:
User-defined JSON → Validated vocabulary domain objects
It enables flexible, user-defined binding systems without compromising correctness or structure.