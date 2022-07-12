<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache\Storage
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache\Storage;

use DirectoryIterator;
use Psr\SimpleCache\CacheInterface;

/**
 * Real cache handler (File System).
 * Initially use CacheInterface (psr 16) v3, but some other library stick to v1.0.1 
 * so library need to adjust version.
 * 
 * Cache metadata key :
 * 1. key      : Cache key.
 * 2. createAt : Created time (unixtime).
 * 3. ttl      : Seconds time to live.
 * 4. deleteAt : Time cache will delete (unixtime).
 */
class FileSystemCacheHandler implements CacheInterface, StorageInterface
{
    /**
     * Data separator for metadata cache.
     * 
     * @var string SEPARATOR_DATA
     */
    public const SEPARATOR_DATA = '||';

    /**
     * Default cache file name, to prevent on accident delete other file.
     * 
     * @var string CACHE_FILE_EXT
     */
    public const CACHE_FILE_EXT = '.cache';

    /**
     * Current cache key, so no need read metadata again if key still same.
     * 
     * @var ?string $currentKey
     */
    private ?string $currentKey = null;

    /**
     * Current cache metadata, for next use if key not change.
     * 
     * @var ?array $currentMetadata
     */
    private ?array $currentMetadata = null;

    /**
     * Init class properties.
     * 
     * @param array $config Assoc array application configuration.
     * @return self Class instance.
     */
    public function __construct (
        /**
         * Assoc array configuration.
         * 
         * @var array $config
         */
        private array $config
    ) {
        $this->config['cache_metadata_location'] = \rtrim($this->config['cache_metadata_location'], '/').'/';
        $this->config['cache_content_location'] = \rtrim($this->config['cache_content_location'], '/').'/';
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null): mixed
    {
        if (! $this->has($key)) 
        {
            return $default;
        }

        return \file_get_contents($this->config['cache_content_location'].$key.self::CACHE_FILE_EXT);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null): bool
    {
        if ($ttl == null) 
        {
            $ttl = $this->config['default_ttl'];
        }
        else if ($ttl instanceof \DateInterval)
        {
            $ttl = $this->secondDateInterval($ttl);
        }

        if ($ttl < 1)
        {
            return true;
        }

        $metadataDir = $this->config['cache_metadata_location'];
        $contentDir = $this->config['cache_content_location'];
        if (! $this->checkFolderLocation($metadataDir) || ! $this->checkFolderLocation($contentDir))
        {
            throw new \Exception('System: Can not create metadata or content location!');
        }

        $now = time();
        $metadata = $key.self::SEPARATOR_DATA.
            $now.self::SEPARATOR_DATA.
            $ttl.self::SEPARATOR_DATA.
            ($now + $ttl);
        $cacheMetadataFileName = $metadataDir.$key.self::CACHE_FILE_EXT;
        $cacheContentFileName = $contentDir.$key.self::CACHE_FILE_EXT;
        $isMetadataSaved = \file_put_contents($cacheMetadataFileName, $metadata);
        $isContentSaved = false;
        if ($isMetadataSaved)
        {
            $isContentSaved = \file_put_contents($cacheContentFileName, $value);
        }

        if (! $isContentSaved)
        {
            \unlink($cacheMetadataFileName);
        }
        
        return $isMetadataSaved && $isContentSaved;
    }

    /**
     * {@inheritDoc}
     */
    public function delete($key): bool
    {
        $metadata = $this->config['cache_metadata_location'].$key.self::CACHE_FILE_EXT;
        $content = $this->config['cache_content_location'].$key.self::CACHE_FILE_EXT;
        $isMetadataExists = \file_exists($metadata);
        $isContentExists = \file_exists($content);
        if (! $isMetadataExists && ! $isContentExists)
        {
            return true;
        }

        $isMetadataDeleted = false;
        if ($isMetadataExists) 
        {
            $isMetadataDeleted = \unlink($metadata);
        }

        $isContentDeleted = false;
        if ($isContentExists)
        {
            $isContentDeleted = \unlink($content);
        }

        return $isMetadataDeleted && $isContentDeleted;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): bool
    {
        // delete all metadata cache files.
        $metadataDir = $this->config['cache_metadata_location'];
        $metadataFiles = new DirectoryIterator($metadataDir);
        while ($metadataFiles->valid())
        {
            $fileName = $metadataFiles->getFilename();
            $fileExt = '.'.$metadataFiles->getExtension();
            if ($fileName === '.' || $fileName === '..' || $fileExt !== self::CACHE_FILE_EXT)
            {
                $metadataFiles->next();
                continue;
            }

            \unlink($metadataDir.$fileName);
            $metadataFiles->next();
        }

        // delete all content cache files.
        $contentDir = $this->config['cache_content_location'];
        $contentFiles = new DirectoryIterator($contentDir);
        while ($contentFiles->valid())
        {
            $fileName = $contentFiles->getFilename();
            $fileExt = '.'.$metadataFiles->getExtension();
            if ($fileName === '.' || $fileName === '..' || $fileExt !== self::CACHE_FILE_EXT)
            {
                $contentFiles->next();
                continue;
            }

            \unlink($contentDir.$fileName);
            $contentFiles->next();
        }

        return true;
    }

    /**
     * Get multiple cache from storage.
     * 
     * @param iterable $keys List/Iterable string key.
     * @param mixed $default Default value for all cache keys.
     * @return iterable Yield iterable $key => $value.
     */
    public function getMultiple($keys, $default = null): iterable
    {
        foreach ($keys as $key) 
        {
            yield $key => $this->get($key, $default);
        }
    }

    /**
     * Set/save multiple data to cache, process will break if one cache fail to save.
     * 
     * @param iterable $values Array data to store $key => $value format.
     * @param null|int|\DateInterval $ttl Seconds Time to live for all $values.
     * @param bool Process result for save multiple data.
     */
    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value)
        {
            if (! $this->set($key, $value, $ttl))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Delete multiple cache based $keys, 
     * always return true even it fail to delete one cache.
     * 
     * @param iterable $keys Iterable/Array string key.
     * @return bool Always return true.
     */
    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key)
        {
            $this->delete($key);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key): bool
    {
        if ($key !== $this->currentKey)
        {
            $this->currentKey = $key;
            $this->currentMetadata = $this->getMetadata($key);
        }

        if (! isset($this->currentMetadata['deleteAt']))
        {
            return false;
        }

        $timeDiff = $this->currentMetadata['deleteAt'] - time();
        if ($timeDiff < 1)
        {
            $this->delete($key);
            $this->currentKey = null;
            $this->currentMetadata = null;
            return false;
        }

        return true;
    }

    /**
     * Get cache metadata.
     * 
     * @param string $key Cache key metadata.
     * @return ?array Return assoc array if success else null.
     */
    public function getMetadata (string $key) : ?array
    {
        $metadataFilename = $this->config['cache_metadata_location'].$key.self::CACHE_FILE_EXT;
        if (! \file_exists($metadataFilename)) 
        {
            return null;
        }

        $rawMetadata = file_get_contents($metadataFilename);
        if ($rawMetadata === false)
        {
            throw new \Exception('System: Failed load cache metadata!');
        }

        $tmp = explode(self::SEPARATOR_DATA, $rawMetadata);
        if (count($tmp) < 4) 
        {
            return null;
        }

        return [
            'key' => $tmp[0],
            'createAt' => $tmp[1],
            'ttl' => $tmp[2],
            'deleteAt' => $tmp[3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTtl () : int
    {
        return $this->config['default_ttl'];
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentKey () : ?string
    {
        return $this->currentKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentMetadata () : ?array 
    {
        return $this->currentMetadata;
    }

    /**
     * Get second from DateInterval object.
     * 
     * @param \DateInterval $ttl DateInterval instance.
     * @return int Seconds diff from $ttl.
     */
    private function secondDateInterval (\DateInterval $ttl) : int
    {
        $dtInterval = new \DateTime();
        $dtInterval->add($ttl);
        
        return $dtInterval->format('U') - time();
    }

    /**
     * Check folder location or create new if not exists.
     * 
     * @param string $location Absolute or relative location folder path.
     * @return bool Result check or create location.
     */
    private function checkFolderLocation (string $location) : bool
    {
        if (\file_exists($location) && \is_dir($location)) 
        {
            return true;
        }

        return \mkdir($location, 0755, true);
    }
}