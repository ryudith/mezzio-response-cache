<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache\CacheHandler;

use Psr\Container\ContainerInterface;
use Ryudith\MezzioResponseCache\ResponseCacheMiddleware;

/**
 * Factory for ResponseCacheMiddleware class.
 */
class ResponseCacheMiddlewareFactory
{
    /**
     * Prepare and get ResponseCacheMiddleware instance.
     * 
     * @param ContainerInterface $container Framework container instance.
     * @return ResponseCacheMiddleware ResponseCacheMiddleware class instance.
     */
    public function __invoke(ContainerInterface $container) : ResponseCacheMiddleware
    {
        $config = ['cache_handler_class' => CacheHandler\CacheHandler::class,];

        $globalConfig = $container->get('config');
        if (isset($globalConfig['mezzio_response_cache']))
        {
            $config = \array_merge_recursive($config, $globalConfig['mezzio_response_cache']);
        }

        $cacheHandler = $container->get($config['cache_handler_class']);
        
        return new ResponseCacheMiddleware($cacheHandler);
    }
}