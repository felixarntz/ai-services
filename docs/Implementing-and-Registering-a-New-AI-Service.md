[Back to overview](./README.md)

# Implementing and Registering a New AI Service

It is possible to register custom AI services that will then be available alongside the built-in ones, behaving in a similar way.

## Implementing the service

In order to implement a custom AI service, at the very minimum you need to implement two PHP classes, a service class and a model class. Don't worry, even though you define these in PHP, the service will be accessible through the JavaScript API as well.

The two classes must implement the following two interfaces respectively:
* [`Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Service`](../includes/Services/Contracts/Generative_AI_Service.php)
* [`Felix_Arntz\AI_Services\Services\Contracts\Generative_AI_Model`](../includes/Services/Contracts/Generative_AI_Model.php)

Additionally, the model class should implement at least one of the following interfaces to indicate which capabilities it supports:
* [`Felix_Arntz\AI_Services\Services\Contracts\With_Text_Generation`](../includes/Services/Contracts/With_Text_Generation.php)
* [`Felix_Arntz\AI_Services\Services\Contracts\With_Image_Generation`](../includes/Services/Contracts/With_Image_Generation.php)

For a better idea on what such an implementation could look like, please review the classes of the built-in AI services.

## Registering the service

To register the service, you need to use the `Services_API::register_service()` method, available via the `ai_services()` function in the global namespace. In the following example, let's assume we register a service called "demo-service", with the service class having the name `Demo_Service_AI_Service`.

```php
ai_services()->register_service(
  'demo-service',
  static function ( Felix_Arntz\AI_Services\Services\Contracts\Authentication $authentication, HTTP $http ) {
    return new Demo_Service_AI_Service( $authentication, $http );
  },
  array(
    'name' => 'Demo Service',
  )
);
```

Note that the model class does not need to be referenced in the registration process, as it is internally used by the service class.
