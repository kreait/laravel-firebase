# CHANGELOG

## 2.1.0 - 2020-05-27

* Add config option to debug HTTP requests made directly from the SDK. It is disabled by
  default and can be enabled with the `FIREBASE_ENABLE_DEBUG=true` environment variable
  or by adding `'debug' => true` to `config/firebase.php`.

## 2.0.0 - 2020-04-01

* Update `kreait/firebase` to `^5.0`

## 1.5.0 - 2020-02-29

* Updated `kreait/firebase-php` to `^4.40.1`
* Added support for Laravel/Lumen `^7.0`

## 1.4.0 - 2020-02-22

* Updated `kreait/firebase-php` to `^4.40.0`
* A relative path to a credentials file is now resolved with `base_path()` to address issues on Windows systems [#7](https://github.com/kreait/laravel-firebase/issues/7) 

## 1.3.0 - 2020-01-15

* Added a notice about not using the factory pattern described in the SDK documentation when using this package. 
  (Although not a code change, adding it in the changelog to enhance visibility)
* Added support for [Lumen](https://lumen.laravel.com/)
* Updated `kreait/firebase-php` to `^4.38.1`

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
