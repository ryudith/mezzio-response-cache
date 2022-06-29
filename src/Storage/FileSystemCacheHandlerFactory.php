<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache\Storage
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache\Storage;

use Psr\Container\ContainerInterface;

/**
 * Factory for FileSystemCacheHandler class.
 */
class FileSystemCacheHandlerFactory
{
    /**
     * Prepare and get CustomLogMiddleware instance.
     * 
     * @param ContainerInterface $container Framework container instance.
     * @return FileSystemCacheHandler FileSystemCacheHandler class instance.
     */
    public function __invoke (ContainerInterface $container) : FileSystemCacheHandler
    {
        $config = [
            'default_ttl' => 3600,
            'cache_metadata_location' => './data/cache/response/metadata',
            'cache_content_location' => './data/cache/response/content',
        ];

        $globalConfig = $container->get('config')['mezzio_response_cache'];
        if (isset($globalConfig['mezzio_response_cache']))
        {
            $config = \array_merge_recursive($config, $globalConfig['mezzio_response_cache']);
        }
        
        return new FileSystemCacheHandler($config);
    }
}