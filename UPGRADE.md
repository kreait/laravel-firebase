## Upgrading to version 3

When upgrading to `laravel-firebase:^3.0` from an earlier version, you have to update your config file.

All existing keys need to be wrapped in the projects array and a default project needs to be configured:

```php
[
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS'),
        'auto_discovery' => true,
    ],
    // ... other keys
]
```

becomes

```php
[
    'default' => env('FIREBASE_PROJECT', 'app'),

    'projects' => [
        'app' => [
          'credentials' => [
              'file' => env('FIREBASE_CREDENTIALS'),
              'auto_discovery' => true,
          ],
          // ... other keys
        ],
    ],
]
```

### Facades
Existing facades (eg. `Kreait\Firebase\Facades\FirebaseAuth`) are deprecated in favor of the new `Kreait\Firebase\Facades\Firebase` facade in order to support multiple projects. For now, the old facades are included and resolve to the default project for better backward compatibility, but upgrading is advised as they will be removed in the future.