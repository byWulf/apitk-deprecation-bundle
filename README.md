# apitk-deprecation-bundle

## Overview
This bundle enables you to mark API endpoints as deprecated and inform the client about that.

## Installation
Install the package via composer:
```
composer require check24/apitk-deprecation-bundle
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
 * @Deprecated(removedAfter="2018-10-09", since="2018-07-01", description="use /v3/users instead")
 
 * @param User[] $users
 * @return User[]
 */
 ```
 A notice is displayed inside the swagger documentation and a new response header
 `x-apitk-deprecated: use /v3/users instead`,
 `x-apitk-deprecated-removed-at: 2018-10-09` (if a date was set),
 `x-apitk-deprecated-since: 2018-07-01` (if a date was set)
 will be sent to the client.

All annotation arguments are optional, so feel free to use `@Deprecated` only. In this case
all clients will receive a `x-apitk-deprecated: deprecated` response header. The header's 
value may be overridden by providing the `description` argument as shown above.

If you want to hide a certain endpoint from the docs, use the `hideFromDocs=true` parameter in
the `Deprecated` annotation. The corresponding action then will not be shown.

### Class annotations
Since Version 1.0.6, it's possible to put `@Deprecated` annotations on a Controller's class to mark all containing endpoints as deprecated.   
Please keep in mind that method annotations always override class annotations completely. No merging or whatsoever will be performed.
When a controller class has a `@Deprecated` annotation, it's impossible to  mark one or more methods of the controller as non-deprecated.
