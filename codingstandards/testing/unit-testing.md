## Unit Testing Standards
* All code MUST be covered by unit tests, save for code in the `vendor` directory and custom exceptions.
* Unit test MUST be written using Pest
* Unit tests MUST follow the arrange-act-assert pattern.
* Each unit test MUST:
  * Be Current: it must reflect the current state of the system under test.
  * Be Atomic; each test method should test the minimum viable meaningful unit.
  * Be Pessimistic - As well as testing the happy path, it should verify what happens when incorrect data is provided. It is not necessary to test language-level exceptions such as type exceptions.
  * Be Readable: it must be easy for a human to understand, both the purpose of the test and its logic.
  * Be Idempotent: No unit test should rely on another test passing in order to pass itself.