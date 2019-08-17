# Firebase for Laravel

A Laravel package for the [Firebase PHP Admin SDK](https://github.com/kreait/firebase-php).

[![Current version](https://img.shields.io/packagist/v/kreait/laravel-firebase.svg?logo=composer)](https://packagist.org/packages/kreait/laravel-firebase)
[![Firebase Admin SDK version](https://img.shields.io/badge/Firebase%20Admin%20SDK-%5E4.30.0-blue)](https://packagist.org/packages/kreait/firebase-php)
[![Discord](https://img.shields.io/discord/523866370778333184.svg?color=7289da&logo=discord)](https://discord.gg/nbgVfty)

* [Installation](#installation)
* [Configuration](#configuration)
* [Usage](#usage)
* [Documentation](https://firebase-php.readthedocs.io/)

## Installation

```bash
composer require kreait/laravel-firebase ^1.0
```

If you don't use package auto-discovery, add the following service provider in `config/app.php`

```php
// config/app.php
<?php

return [
    // ...
    'providers' => [
        // ...
        Kreait\Laravel\Firebase\ServiceProvider::class
    ]
    // ...   
];
```

## Configuration

In order to access a Firebase project and its related services using a server SDK, requests must be authenticated.
For server-to-server communication this is done with a Service Account.

The package uses auto discovery to find the credentials needed for authenticating requests to the Firebase APIs
by inspecting certain environment variables and looking into Google's well known path(s).

If you don't already have generated a Service Account, you can do so by following the instructions from the 
official documentation pages at https://firebase.google.com/docs/admin/setup#initialize_the_sdk.

Once you have downloaded the Service Account JSON file, you can use it to configure the package by specifying
the environment variable `FIREBASE_CREDENTIALS` in your `.env` file:

```
FIREBASE_CREDENTIALS=/full/path/to/firebase_credentials.json
# or
FIREBASE_CREDENTIALS=relative/path/to/firebase_credentials.json
```

For further configuration, please see [config/firebase.php](config/firebase.php). You can modify the configuration
by copying it to your local `config` directory with the publish command:

```bash
php artisan vendor:publish --provider="Kreait\Laravel\Firebase\ServiceProvider" --tag=config
```

## Usage

| Component | [Automatic Injection](https://laravel.com/docs/5.8/container#automatic-injection) | [Facades](https://laravel.com/docs/facades) | [`app()`](https://laravel.com/docs/helpers#method-app) |
| --- | --- | --- | --- |
| [Authentication](https://firebase-php.readthedocs.io/en/latest/authentication.html) | `\Kreait\Firebase\Auth` | `FirebaseAuth` | `app('firebase.auth')` |
| [Cloud&nbsp;Messaging&nbsp;(FCM)](https://firebase-php.readthedocs.io/en/latest/cloud-messaging.html) | `\Kreait\Firebase\Messaging` | `FirebaseMessaging` | `app('firebase.messaging')` |
| [Realtime Database](https://firebase-php.readthedocs.io/en/latest/realtime-database.html) | `\Kreait\Firebase\Database` | `FirebaseDatabase` | `app('firebase.database')` |
| [Remote Config](https://firebase-php.readthedocs.io/en/latest/remote-config.html) | `\Kreait\Firebase\RemoteConfig` | `FirebaseRemoteConfig` | `app('firebase.remote_config')` |
| [Storage](https://firebase-php.readthedocs.io/en/latest/storage.html) | `\Kreait\Firebase\Storage` | `FirebaseStorage` | `app('firebase.storage')` |

Once you have retrieved a component, please refer to the [documentation of the Firebase PHP Admin SDK](https://firebase-php.readthedocs.io) 
for further information on how to use it.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
