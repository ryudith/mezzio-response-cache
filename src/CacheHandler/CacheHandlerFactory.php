<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache\CacheHandler;

use Psr\Container\ContainerInterface;
use Ryudith\MezzioResponseCache\Storage\FileSystemCacheHandler;

/**
 * Factory for CacheHandler class.
 */
class CacheHandlerFactory 
{
    /**
     * Prepare and get CacheHandler instance.
     * 
     * @param ContainerInterface $container Framework container instance.
     * @return CacheHandler CacheHandler class instance.
     */
    public function __invoke (ContainerInterface $container) : CacheHandler
    {
        $config = [
            'cache_storage_handler_class' => FileSystemCacheHandler::class,
            'exclude_ip_from_cache' => [],
            'exlcude_path_from_cache' => [],
        ];

        $globalConfig = $container->get('config');
        if (isset($globalConfig['mezzio_response_cache']))
        {
            $config = \array_merge_recursive($config, $globalConfig['mezzio_response_cache']);
        }

        $storage = $container->get($config['cache_storage_handler_class']);
        
        return new CacheHandler($config, $storage);
    }
}