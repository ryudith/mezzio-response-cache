<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache;


/**
 * Config provider class.
 * 
 * Library specific configuration description (mezzio_response_cache) :
 * 1. default_ttl                 : Default time to life cache in second, default 3600 (or 1 hour).
 * 2. exclude_ip_from_cache       : IP list to exclude cache (list array).
 * 3. exlcude_path_from_cache     : Route path list to exclude cache (list array).
 * 4. cache_handler_class         : Class to handle cache mechanism.
 * 5. cache_storage_handler_class : Class to handle cache storage mechanism.
 * 6. cache_metadata_location     : Path location for metadata cache 
 * 7. cache_content_location      : Path location for actual cache content.
 */
class ConfigProvider
{
    public function __invoke() : Array
    {
        return [
            'dependencies' => [
                'factories' => [
                    Storage\FileSystemCacheHandler::class => Storage\FileSystemCacheHandlerFactory::class,
                    CacheHandler\CacheHandler::class => CacheHandler\CacheHandlerFactory::class,
                    ResponseCacheMiddleware::class => ResponseCacheMiddlewareFactory::class,
                ],
            ],
            'mezzio_response_cache' => [
                'default_ttl' => 3600,
            ],
        ];
    }
}