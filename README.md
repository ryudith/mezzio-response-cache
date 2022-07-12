# **mezzio-response-cache**

**`Ryudith\MezzioResponseCache`** is middleware for Mezzio framework to save response produce by request (default use request path as cache key).


## **Installation**

To install run command :
```sh

$ composer require ryudith/mezzio-response-cache

```


## **Usage**

#### Add **`Ryudith\MezzioResponseCache\ConfigProvider`** to **`config/config.php`**
```php

...

$aggregator = new ConfigAggregator([
    ...
    \Laminas\Diactoros\ConfigProvider::class,

    Ryudith\MezzioResponseCache\ConfigProvider::class,  // <= add this line

    // Swoole config to overwrite some services (if installed)
    class_exists(\Mezzio\Swoole\ConfigProvider::class)
        ? \Mezzio\Swoole\ConfigProvider::class
        : function (): array {
            return [];
        },

    // Default App module config
    App\ConfigProvider::class,

    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),

    // Load development config if it exists
    new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
], $cacheConfig['config_cache_path']);

...

```

#### Add **`Ryudith\MezzioResponseCache\ResponseCacheMiddleware`** to **`config/pipeline.php`**

```php

...

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    // The error handler should be the first (most outer) middleware to catch
    // all Exceptions.
    $app->pipe(ErrorHandler::class);
    $app->pipe(ResponseCacheMiddleware::class);  // <= add this line

    ...

};

```

> You can put `ResponseCacheMiddleware::class` before or after `ErrorHandler::class` or anywhere you want depend on you need.
> Basically, middleware do check cache path key (default behavior) if not exists then generate response else it will give cache.
> It also support custom configurations for exclude path or IP from cache for example (See _`custom configurations`_ below).


## **Custom Configurations**

1. **`default_ttl`**  
 Default time to life cache in second, default 3600 (or 1 hour).  
   > ```php
   > ...
   >'mezzio_response_cache' => [
   >    'default_ttl' => 3600,
   >],
   > ...
   >```  

2. **`exclude_ip_from_cache`**  
 IP list to exclude cache (list array), default empty array.
   >```php
   > ...
   >'mezzio_response_cache' => [
   >    'exclude_ip_from_cache' => [
   >        '192.168.0.1', 
   >        '127.0.0.1',
   >    ],
   >],
   > ...
   >```

3. **`exlcude_path_from_cache`**  
 Route path list to exclude cache (list array), default empty array.  
   >```php
   > ...
   >'mezzio_response_cache' => [
   >    'exlcude_path_from_cache' => [
   >        '/about', 
   >        '/api/post',
   >    ],
   >],
   > ...
   >```

4. **`cache_handler_class`**  
 Class to handle cache mechanism. Custom class to handle cache middleware. 
   >```php
   > ...
   >'mezzio_response_cache' => [
   >    'cache_handler_class' => CacheHandler\CacheHandler::class,
   >],
   > ...
   >```

5. **`cache_storage_handler_class`**  
 Class to handle cache storage mechanism. Custom class to handle cache storage for cache handler in middleware, default is file system storage.
   >```php
   >'mezzio_response_cache' => [
   >    'cache_storage_handler_class' => Storage\FileSystemCacheHandler::class,
   >],
   >```

6. **`cache_metadata_location`**  
 Path location for metadata cache. Path directory location where `cache_storage_handler_class` will save cache metadata for default cache storage.
   >```php
   >'mezzio_response_cache' => [
   >    'cache_metadata_location' => './data/cache/response/content',
   >],
   >```

7. **`cache_content_location`**  
 Path location for actual cache content. Path directory location where `cache_storage_handler_class` will save cache content data for default cache storage.
   >```php
   >'mezzio_response_cache' => [
   >    'cache_content_location' => './data/cache/response/content',
   >],
   >```

## Simple Helper

#### **Web**

Add `Ryudith\MezzioResponseCache\Helper\WebHandlerCache` to `factories` configuration, usually inside file `config/dependencies.global.php`.
```php

...
'factories' => [
    ...

    // add this to enable web simple helper
    Ryudith\MezzioResponseCache\Helper\WebHandlerCache::class => Ryudith\MezzioResponseCache\Helper\WebHandlerCacheFactory::class
    ...
]
...

```

Then register helper to route in file `config/route.php`.
```php

...
$app->get('/cacheresponse/helper', Ryudith\MezzioResponseCache\Helper\WebHandlerCache::class);
...

```
> change route path `/cacheresponse/helper` to your own route path.
  

Next you can access simple web helper from `http://localhost:8080/cacheresponse/helper?o=clear` or `http://localhost:8080/cacheresponse/helper?o=delete&p=/about` from browser.

> change address depend your Mezzio app configuration (address above is just example).

**Query parameter used by simple web helper is :**
1. **`o`**  
 The operation, `clear` or `delete`.
2. **`k`**  
 Sha1 cache key, default generate with `sha1($uriPath)`.
3. **`p`**  
 Cache path, if you don't know what sha1 cache key, you can use path cache instead. If you use `k` and `p` at same time, helper will pick `k` value instead and ignore `p` query parameter.

## Documentation

[API Documentation](https://ryudith.github.io/mezzio-response-cache/api/index.html)

[Issues or Questions](https://github.com/ryudith/mezzio-response-cache/issues)