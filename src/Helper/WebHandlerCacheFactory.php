<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache\Helper;

use Psr\Container\ContainerInterface;
use Ryudith\MezzioResponseCache\CacheHandler\CacheHandler;

/**
 * Factory for WebHandlerCache class.
 */
class WebHandlerCacheFactory
{
    /**
     * Prepare and get WebHandlerCache instance.
     * 
     * @param ContainerInterface $container Framework container instance.
     * @return ResponseCacheMiddleware ResponseCacheMiddleware class instance.
     */
    public function __invoke (ContainerInterface $container) : WebHandlerCache
    {
        $cacheHandler = $container->get(CacheHandler::class);
        return new WebHandlerCache($cacheHandler);
    }
}