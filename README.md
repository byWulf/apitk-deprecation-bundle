# apitk-deprecation-bundle

## Overview
This bundle enables you to mark API endpoints as deprecated and inform the client about that.

## Installation
Install the package via composer:
```
composer install check24/apitk-deprecation-bundle
```

## Usage
### Deprecations
You can mark actions as deprecated so developers can notice that they have to update their 
API call to a newer version or to use a whole other endpoint.
```
use Shopping\ApiTKDeprecationBundle\Annotation\Deprecated;

/**
 * Returns the users in the system.
 *
 * @Rest\Get("/v2/users")
 * @Rest\View()
 *
 * @Deprecated(removedAfter="2018-10-09")
 
 * @param User[] $users
 * @return User[]
 */
 ```
 A notice is displayed inside the swagger documentation and a new response header
 `x-api-deprecated: deprecated` and `x-apitk-deprecated-removed-at: 2018-10-09` (if a date was set)
 will be sent to the client.

If you want to hide a certain endpoint from the docs, use the `hideFromDocs=true` parameter in
the `Deprecated` annotation. The corresponding action then will not be shown.