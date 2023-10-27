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
it is preferable to use the cache driver similar to the one you used in your project.
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

## License

Laravel Cache Flusher package is licensed under [The MIT License (MIT)](LICENSE).

## Security contact information

To report a security vulnerability, contact directly to the developer contact email [Here](mailto:aldeeb.91@gmail.com).
