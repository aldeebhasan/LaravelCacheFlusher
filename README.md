Laravel Cache Flusher
=====
A lightweight package to invalidate cache entries automatically when the cached data changed.


Why
-----------
The current cache management system provided by laravel is great and satisfiable.
To handle the cache invalidation when data update,
you can do that manually or you may group your cache entries under tags and do invalidation by tags (like Redis).
Even with tagging, it becomes difficult to handle all of tags spread all over the code base when the project get larger.

This package provides an easy way to track the changes over models and do an automatic invalidation in your behave.
Just define the relation between your cache and models and your life will become easier.


Installation
------------

Install using composer:

```bash
composer require aldeebhasan/laravel-cache-flusher
```

After installation run the following code to publish the config:

```
php artisan vendor:publish --tag=cache-flusher
```

Basic Usage
-----------
It is very simple to use this package,
you need only to configure all the required variables
and taste the sweet of this package

### 1) enabled (default : false)

To enable/disable this package in your project ,
you can control its value using the entry `CACHE_FLUSHER_ENABLED`
in your .env file.

### 2) driver (default : your default cache driver)

It will be used to specify the cache driver the package will work with,
it is preferable to use the cache driver similar to the one you used in your project
(to make sure that all the operations done on the same cache driver).
you can control its value using the entry `CACHE_FLUSHER_DRIVER`
in your .env file.

### 3) cool-down (default : null)

Some time you have a bulk of operation done over the model/s within short period of time.
To avoid the high rate cache invalidation for a specific period (cool down period) you can specify a value (`in seconds`) for this config
entry.
you can control its value using the entry `CACHE_FLUSHER_COOL_DOWN`
in your .env file.

### 4) mapping (default : [])

The most important part in our package.
Here you can specify the cache keys along with the models that cause their invalidation.

In the following example,
the `store_info`,`store_info.categories` will be invalidated when Category model changed (create|update|delete)
the `store_info`,`store_info.products` will be invalidated when Product model changed (create|update|delete)

```php
 'mapping' => [
    'store_info'  =>  [ Product::class, Category::class ],
    'store_info.categories'  =>  [  Category::class ],
    'store_info.products'  =>  [  Product::class ],
 ]
```

The keys can also be a regex expression,
and when any change over the models occurred,
all the matched keys will be invalidated

In the following example, all they cache entry started with
`store.` or `mobile.` will be invalidated
if Product or Category changed (create|update|delete)

```php
 'mapping' => [
    '^(store\..+|mobile\..+)$'  =>  [ Product::class, Category::class ]
 ]
```

All they cache entry end with
`.products` or `.categories.` will be invalidated
if Product, Category, or Attribute changed (create|update|delete)

```php
 'mapping' => [
    '^(.*\.products|.*\.categories)$'  =>  [ Product::class, Category::class,Attribute::class ]
 ]
```

## Advanced Usage

Some time you may want to provide some condition when invalidating the cache keys.
One examples: you have a multi tenant project and you want to invalidate the cache entries of a specific company.

The package can help you with that by providing a binding resolving within the defined `mapping` config param.
One last thing, you have to provide a binding function (in your service provider) to extract the related values
from the model that trigger the invalidation operation.

Example:

```php
// define the binding mapping function
class AppServiceProvider extends ServiceProvider
{

    public function boot()
    {
        //.... other boot methods
    
       CacheFlusher::setBindingFunction(
            function (string $bindingKey, Model $model): ?string {
                switch ($bindingKey) {
                    case "company_id":
                        return '1'; //$model->company_id
                    case "user_id":
                        return "2"; // $model->user_id;
                }
                return null;
            });
    }
}
```

The binding function accept two params: `$bindingKey` which represent the matched binding key.
`$model` is the model who triggered the invalidation operation. The defined function will be called
for each matched param in the defined cache entry patterns (`mapping` in config file)

Now, according to the following mapping configuration :

```php
'mapping' => [
    '^companies\.{company_id}\.stores' => [User::class],
    '^companies\.{company_id}\.mobiles\.{user_id}' => [User::class],
]
```

When the `User` model changed, all the entries that start with
`companies.1.stores` and `companies.1.mobiles.2` will be invalidated.

NOTE: {company_id} is fixed to 1 in the binding function, {user_id} is fixed to 2 in the binding function.

## License

Laravel Cache Flusher package is licensed under [The MIT License (MIT)](LICENSE).

## Security contact information

To report a security vulnerability, contact directly to the developer contact email [Here](mailto:aldeeb.91@gmail.com).
