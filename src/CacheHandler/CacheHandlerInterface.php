<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache\CacheHandler
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache\CacheHandler;

/**
 * 
 */
interface CacheHandlerInterface
{
    /**
     * Get cache TTL from storage.
     * 
     * @return int Second TTL.
     */
    public function getTtl () : int;

    /**
     * Set path property for class.
     * 
     * @param string $path String request URI path.
     * @return self Class instance.
     */
    public function setPath (string $path) : self;

    /**
     * Set IP property for class.
     * 
     * @param string $ip String request IP address.
     * @return self Class instance.
     */
    public function setIP (string $ip) : self;

    /**
     * Set key property for class, use for cache key.
     * If null then use request URI path value for key.
     * 
     * @param ?string $key String key for cache.
     * @return self Class instance.
     */
    public function setKey (?string $key) : self;

    /**
     * Validate request is allowed to cache or not, 
     * based class properties and 'mezzio_response_cache' configurations.
     * 
     * @return bool Validation result.
     */
    public function validate () : bool;

    /**
     * Get last validate process result.
     * 
     * @return bool Validate process result.
     */
    public function allowCache () : bool;

    /**
     * Check request already has cache or not.
     * If parameter $key is null, then use already existing key.
     * 
     * @param ?string $key String cache key.
     * @return bool Check result.
     */
    public function hasCache (?string $key = null) : bool;

    /**
     * Get cache expire time from cache storage.
     * 
     * @param ?string $key String cache key.
     * @return ?int Second expire time.
     */
    public function getExpireTime (?string $key = null) : ?int;

    /**
     * Get cache content from storage cache.
     * 
     * @param ?string $key String cache key.
     * @return ?string String Cache content or null.
     */
    public function getContent (?string $key = null) : ?string;

    /**
     * Set cache content to storage cache.
     * 
     * @param mixed $content Content to cache.
     * @param ?string $key String cache key.
     * @param ?int $duration Second duration cache.
     * @return bool Set cache result.
     */
    public function setContent (mixed $content, ?string $key = null, ?int $duration = null) : bool;

    /**
     * Delete individual cache based $key parameter.
     * 
     * @param string $key Cache key to delete.
     * @return bool Delete process result.
     */
    public function deleteCache (string $key) : bool;

    /**
     * Clear all available caches.
     * 
     * @return bool Clear process result.
     */
    public function clearCache () : bool;
}