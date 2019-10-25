# CHANGELOG

## 1.2.0 - 2019-10-26

* Updated `kreait/firebase-php` to `^4.35.0`
* Added Firestore to the Service Provider and as `FirebaseFirestore` facade

## 1.1.0 - 2019-09-19

* Updated `kreait/firebase-php` to `^4.32.0`
* Added Dynamic Links to the Service Provider and as `FirebaseDynamicLinks` facade
* Added `FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN` as environment variable

To update the package, please re-publish its configuration

```bash
php artisan vendor:publish --provider="Kreait\Laravel\Firebase\ServiceProvider" --tag=config
```

or add the following section to `config/firebase.php`:

```php
<?php

return [
    // ...
    'dynamic_links' => [
        'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN')
    ],
    // ...
];
```

## 1.0.1 - 2019-08-19

* Made clear that this package needs Laravel 5.8 or higher.
* Updated `kreait/firebase-php` to `^4.30.1`
* Required `illuminate/contracts` and `illuminate/support`

## 1.0.0 - 2019-08-17

* Initial release
