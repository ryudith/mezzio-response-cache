<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioResponseCache\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioResponseCache\Helper;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ryudith\MezzioResponseCache\CacheHandler\CacheHandlerInterface;

/**
 * Delete or clear cache for web helper.
 */
class WebHandlerCache implements RequestHandlerInterface
{
    private const DELETE_OPERATION = 'DELETE';
    private const CLEAR_OPERATION = 'CLEAR';

    /**
     * WebHandlerCache instance constructor.
     * 
     * @param CacheHandlerInterface $cacheHandler
     * @return self
     */
    public function __construct (
        private CacheHandlerInterface $cacheHandler
    ) {
        
    }

    /**
     * Handle the request for helper.
     * Query params :
     * 1. o : Operation to run, 'delete' or 'clear'.
     * 2. k : Cache sha1 key string for delete operation.
     * 3. p : URL path string for delete operation, if 'k' not exists else it will use 'k' parameter.
     * 
     * @param ServerRequestInterface $request Request instance.
     * @return ResponseInterface Text response message result.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $isOperationExists = isset($params['o']);
        $isPathExists = isset($params['p']) && \filter_var('http://localhost.com/'.$params['p'], \FILTER_VALIDATE_URL);
        $isKeyExists = isset($params['k']) && \preg_match('/^[a-zA-Z0-9\-\.\_]+$/', $params['k']);
        if (! $isOperationExists)
        {
            return new TextResponse("No operation parameter provide");
        }
        else if ($isOperationExists && \strtoupper($params['o']) == self::DELETE_OPERATION && ! $isPathExists && ! $isKeyExists)
        {
            return new TextResponse("No data parameter provide");
        }

        $key = null;
        if ($isKeyExists)
        {
            $key = $params['k'];
        }
        else if ($isPathExists)
        {
            $key = \sha1($params['p']);
        }

        $message = 'Invalid operation parameter!';
        $params['o'] = \strtoupper($params['o']);
        if ($key != null && $params['o'] == self::DELETE_OPERATION)
        {
            $message = ($this->cacheHandler->deleteCache($key) ? 'Sucess' : 'Failed').' delete cache '.$key;
        }
        else if ($params['o'] == self::CLEAR_OPERATION)
        {
            // clear operation
            $message = ($this->cacheHandler->clearCache() ? 'Success' : 'Failed').' clear cache';
        }

        return new HtmlResponse($message);
    }
}