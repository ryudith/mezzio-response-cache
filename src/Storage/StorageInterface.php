<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache\Storage
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache\Storage;

/**
 * Contract or guide for implement CacheHandler class.
 */
interface StorageInterface
{
    /**
     * Get cache content or return $default if cache not exists.
     * 
     * @param string $key Cache key.
     * @param mixed $default Default value if no cache.
     * @return mixed Return cache content or $default.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Save cache metadata and content to storage.
     * 
     * @param string $key Cache key (will be file name).
     * @param mixed $value Cache content.
     * @param null|int|\DateInterval $ttl Seconds interval (int).
     * @return bool Process result save cache.
     */
    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool;

    /**
     * Check if cache exists and not expired, 
     * true if exists and not expired else false.
     * 
     * @param string $key Cache key.
     * @return bool True if cache exists else false.
     */
    public function has(string $key): bool;

    /**
     * Get cache TTL, default is 'default_ttl' value.
     * 
     * @return int Second TTL value.
     */
    function getTtl () : int;

    /**
     * Get current cache key.
     * 
     * @return ?string Current cache key.
     */
    public function getCurrentKey () : ?string;

    /**
     * Get current cache metadata assoc array.
     * 
     * @return ?array Assoc array current cache metadata.
     */
    public function getCurrentMetadata () : ?array;

    /**
     * Delete cache metadata and content file.
     * 
     * @param string $key Cache key (will be file name).
     * @return bool Process result delete cache.
     */
    public function delete(string $key): bool;

    /**
     * Clear all caches by delete metadata and content files.
     * 
     * @return bool Process result clear cache.
     */
    public function clear(): bool;
}