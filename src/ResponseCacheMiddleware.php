<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ryudith\MezzioResponseCache\CacheHandler\CacheHandlerInterface;

/**
 * Middleware for cache every response data that not in exclude rules.
 */
class ResponseCacheMiddleware implements MiddlewareInterface
{
    /**
     * Do nothing.
     * 
     * @param CacheHandler $cacheHandler Cache handler instance.
     * @return self ResponseCacheMiddleware instance.
     */
    public function __construct (
        /**
         * Cache handler instance.
         * 
         * @var CacheHandler $cacheHandler
         */
        private CacheHandlerInterface $cacheHandler
    ) {
    }

    /**
     * Cache every request that not in exclude rules.
     * 
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handle
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $uriPath = $request->getUri()->getPath();
        $cacheControl = 'max-age='.$this->cacheHandler->getTtl().', must-revalidate, private';
        $this->cacheHandler
            ->setIP($_SERVER['REMOTE_ADDR'])
            ->setPath($uriPath)
            ->validate();
        if ($this->cacheHandler->allowCache())
        {
            if ($this->cacheHandler->hasCache())
            {
                $content = $this->cacheHandler->getContent();

                $requestContentType = $request->getHeader('content-type');
                if (isset($requestContentType[0]) && \str_contains($requestContentType[0], 'application/json'))
                {
                    return new JsonResponse($content);
                }

                return new HtmlResponse($content);
            }
            
            $content = (string) $response->getBody();
            $this->cacheHandler->setContent($content);

            return $response->withHeader('Cache-Control', $cacheControl);
        }
        
        return $response;
    }
}