## Class Design
* You MUST obey SOLID principles at all times.
* You MUST NOT repeat yourself.
* You SHOULD Prefer composition to inheritance.
* You MUST Obey the Rule of Demeter.
* Use MUST use private constants over inline comparisons.
* Pure functions MUST be static.
* If a class can be readonly, it MUST be.

__Don't do this:__
```php
    public function addItemsToBasket(int $qty) {
        if ($qty > 50) {
            throw new TooManItemsAddedToBasketException(
                "You may not add more than fifty items to your basket at a time.";
            );
        }
    }
```

__Do this instead:__
```php

    private const int MAX_ITEMS_ADDABLE = 50;
    
    public function addItemsToBasket(int $qty) {
        if ($qty > self::MAX_ITEMS_ADDABLE) {
            throw new TooManItemsAddedToBasketException(
                "You may not add more than fifty items to your basket at a time.";
            );
        }
    }
```

If you need to refer to the same value for the same purpose (e.g, adding the MAXIMUM_ITEMS_ALLOWABLE to an exception), it is permissible to make that constant public.



