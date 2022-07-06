<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache\CacheHandler;

use Ryudith\MezzioResponseCache\Storage\StorageInterface;

/**
 * Mediator for cache handler, also check are request allowed to cache.
 */
class CacheHandler implements CacheHandlerInterface
{
    /**
     * Flag/Result request allowed to cache (validate method).
     * 
     * @var bool $isAllowCache
     */
    private bool $isAllowCache = false;

    /**
     * Request IP address.
     * 
     * @var ?string $ip
     */
    private ?string $ip = null;

    /**
     * Request URI path.
     * 
     * @var ?string $path
     */
    private ?string $path = null;

    /**
     * String key cache.
     * 
     * @var ?string $key
     */
    private ?string $key = null;
    
    /**
     * Do nothing.
     * 
     * @param array $config Assoc array mezzio_response_cache configurations.
     * @param StorageInterface $storage Cache storage instance.
     * @return self Class instance.
     */
    public function __construct (
        /**
         * Mezzio specific configuration (mezzio_response_cache).
         * 
         * @var array $config
         */
        private array $config,

        /**
         * Cache storage handler instance.
         * 
         * @var StorageInterface $storage
         */
        private StorageInterface $storage
    ) {
        
    }

    /**
     * {@inheritDoc}
     */
    public function getTtl () : int
    {
        return $this->storage->getTtl();
    }

    /**
     * {@inheritDoc}
     */
    public function setPath (string $path) : self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setIP (string $ip) : self
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setKey (?string $key) : self
    {
        if ($key !== null)
        {
            $this->key = \sha1($key);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function validate () : bool
    {
        if (
            in_array($this->ip, $this->config['exclude_ip_from_cache']) || 
            in_array($this->path, $this->config['exlcude_path_from_cache'])
        ) {
            return $this->isAllowCache;
        }

        $this->isAllowCache = true;
        if ($this->key === null)
        {
            $this->key = \sha1($this->path);
        }
        
        return $this->isAllowCache;
    }

    /**
     * {@inheritDoc}
     */
    public function allowCache () : bool 
    {
        return $this->isAllowCache;
    }

    /**
     * {@inheritDoc}
     */
    public function hasCache (?string $key = null) : bool
    {
        $this->setKey($key);
        return $this->storage->has($this->key);
    }

    /**
     * {@inheritDoc}
     */
    public function getExpireTime (?string $key = null) : ?int 
    {
        $this->setKey($key);

        $storageKey = $this->storage->getCurrentKey();
        $storageMetadata = $this->storage->getCurrentMetadata();
        if ($this->key === $storageKey)
        {
            return $storageMetadata['deleteAt'];
        }
        else if ($this->storage->has($this->key))
        {
            return $storageMetadata['deleteAt'];
        }
        
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getContent (?string $key = null) : ?string
    {
        $this->setKey($key);
        return $this->storage->get($this->key);
    }

    /**
     * {@inheritDoc}
     */
    public function setContent (mixed $content, ?string $key = null, ?int $duration = null) : bool
    {
        $this->setKey($key);
        return $this->storage->set($this->key, $content, $duration);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteCache(string $key): bool
    {
        return $this->storage->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function clearCache(): bool
    {
        return $this->storage->clear();
    }
}