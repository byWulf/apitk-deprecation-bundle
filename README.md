# rfc18-bundle

## Overview
This bundle enables you to mark API endpoints as deprecated and inform the client about that.

## Installation
Add this repository to your `composer.json` until it is available at packagist:
```
{
    "repositories": [{
            "type": "vcs",
            "url": "git@github.com:CHECK24/rfc18-bundle.git"
        }
    ]
}
```

After that, install the package via composer:
```
composer install ofeige/rfc18-bundle:dev-master
```

## Usage
### Deprecations
You can mark actions as deprecated so developers can notice that they have to update their API call to a newer version or to use a whole other endpoint.
```
/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v2/users")
 * @Rest\View()
 *
 * @Api\Deprecated(removedAfter="2018-10-09")
 
 * @param User[] $users
 * @return User[]
 */
 ```
 A notice is displayed inside the swagger documentation and a new response header `x-api-rfc18-deprecated: deprecated` and `x-api-rfc18-deprecated-removed-at: 2018-10-09` (if a date was set) will be sent to the client.

If you want to hide a certain endpoint from the docs, use the `hideFromDocs=true` parameter in the `Deprecated` annotation. The corresponding action then will not be shown.