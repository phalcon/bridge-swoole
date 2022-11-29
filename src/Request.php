<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Bridge\Swoole;

use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Http\Message\RequestMethodInterface;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Request\Exception;
use Swoole\Http\Request as SwooleRequest;

class Request extends AbstractInjectionAware implements RequestInterface, RequestMethodInterface, InjectionAwareInterface
{
    private SwooleRequest $swooleRequest;

    public function __construct(SwooleRequest $swooleRequest)
    {
        $this->swooleRequest = $swooleRequest;
    }

    public function get(string $name = null, $filters = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false)
    {
        // TODO: Implement get() method.
    }

    public function getAcceptableContent(): array
    {
        // TODO: Implement getAcceptableContent() method.
    }

    public function getBasicAuth(): ?array
    {
        // TODO: Implement getBasicAuth() method.
    }

    public function getBestAccept(): string
    {
        // TODO: Implement getBestAccept() method.
    }

    public function getBestCharset(): string
    {
        // TODO: Implement getBestCharset() method.
    }

    public function getBestLanguage(): string
    {
        // TODO: Implement getBestLanguage() method.
    }

    public function getClientAddress(bool $trustForwardedHeader = false)
    {
        // TODO: Implement getClientAddress() method.
    }

    public function getClientCharsets(): array
    {
        // TODO: Implement getClientCharsets() method.
    }

    public function getContentType(): ?string
    {
        // TODO: Implement getContentType() method.
    }

    public function getDigestAuth(): array
    {
        // TODO: Implement getDigestAuth() method.
    }

    public function getHeader(string $header): string
    {
        // TODO: Implement getHeader() method.
    }

    public function getHeaders(): array
    {
        // TODO: Implement getHeaders() method.
    }

    public function getHttpHost(): string
    {
        // TODO: Implement getHttpHost() method.
    }

    public function getHTTPReferer(): string
    {
        // TODO: Implement getHTTPReferer() method.
    }

    public function getJsonRawBody(bool $associative = false)
    {
        // TODO: Implement getJsonRawBody() method.
    }

    public function getLanguages(): array
    {
        // TODO: Implement getLanguages() method.
    }

    public function getMethod(): string
    {
        $method = $this->swooleRequest->getMethod();

        return $method !== false ? $method : '';
    }

    public function getPort(): int
    {
        return $this->swooleRequest->server['server_port'];
    }

    public function getURI(bool $onlyPath = false): string
    {
        // TODO: Implement getURI() method.
    }

    public function getPost(string $name = null, $filters = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false)
    {
        // TODO: Implement getPost() method.
    }

    public function getPut(string $name = null, $filters = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false)
    {
        // TODO: Implement getPut() method.
    }

    public function getQuery(string $name = null, $filters = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false)
    {
        // TODO: Implement getQuery() method.
    }

    public function getRawBody(): string
    {
        return (string)$this->swooleRequest->rawContent();
    }

    public function getScheme(): string
    {
        $https = $this->swooleRequest->server['HTTPS'] ?? '';

        return $https && $https !== "off" ? 'https' : 'http';
    }

    public function getServer(string $name): ?string
    {
        return $this->swooleRequest->get();
    }

    public function getServerAddress(): string
    {
        // TODO: Implement getServerAddress() method.
    }

    public function getServerName(): string
    {
        // TODO: Implement getServerName() method.
    }

    public function getUploadedFiles(bool $onlySuccessful = false, bool $namedKeys = false): array
    {
        // TODO: Implement getUploadedFiles() method.
    }

    public function getUserAgent(): string
    {
        // TODO: Implement getUserAgent() method.
    }

    public function has(string $name): bool
    {
        // TODO: Implement has() method.
    }

    public function hasFiles(): bool
    {
        // TODO: Implement hasFiles() method.
    }

    public function hasHeader(string $header): bool
    {
        // TODO: Implement hasHeader() method.
    }

    public function hasQuery(string $name): bool
    {
        // TODO: Implement hasQuery() method.
    }

    public function hasPost(string $name): bool
    {
        return isset($this->swooleRequest->post[$name]);
    }

    public function hasPut(string $name): bool
    {
        return isset($this->swooleRequest->post[$name]);
    }

    public function hasServer(string $name): bool
    {
        return isset($this->swooleRequest->server[$name]);
    }

    public function isAjax(): bool
    {
        // TODO: Implement isAjax() method.
    }

    public function isConnect(): bool
    {
        return $this->getMethod() === self::METHOD_CONNECT;
    }

    public function isDelete(): bool
    {
        return $this->getMethod() === self::METHOD_DELETE;
    }

    public function isGet(): bool
    {
        return $this->getMethod() === self::METHOD_GET;
    }

    public function isHead(): bool
    {
        return $this->getMethod() === self::METHOD_HEAD;
    }

    public function isMethod($methods, bool $strict = false): bool
    {
        $httpMethod = $this->getMethod();

        if (is_string($methods)) {
        if ($strict && !$this->isValidHttpMethod($methods)) {
            throw new Exception("Invalid HTTP method: " . $methods);
        }

            return $methods == $httpMethod;
        }

        if (is_array($methods)) {
            foreach ($methods as $method) {
                if ($this->isMethod($method, $strict)) {
                    return true;
                }
            }

            return false;
        }

        if ($strict) {
            throw new Exception("Invalid HTTP method: non-string");
        }

        return false;
    }

    public function isOptions(): bool
    {
        return $this->getMethod() === self::METHOD_OPTIONS;
    }

    public function isPost(): bool
    {
        return $this->getMethod() === self::METHOD_POST;
    }

    public function isPurge(): bool
    {
        return $this->getMethod() === self::METHOD_PURGE;
    }

    public function isPut(): bool
    {
        return $this->getMethod() === self::METHOD_PUT;
    }

    public function isSecure(): bool
    {
        //$this->swooleRequest->
    }

    public function isSoap(): bool
    {
        // TODO: Implement isSoap() method.
    }

    public function isTrace(): bool
    {
        return $this->getMethod() === self::METHOD_TRACE;
    }

    /**
     * TODO: Implement
     *
     * @param bool $onlySuccessful
     * @return int
     */
    public function numFiles(bool $onlySuccessful = false): int
    {
        return 0;
    }

    /**
     * Checks if a method is a valid HTTP method
     */
    public function isValidHttpMethod(string $method): bool
    {
        switch (strtoupper($method)) {
            case self::METHOD_CONNECT:
            case self::METHOD_DELETE:
            case self::METHOD_GET:
            case self::METHOD_HEAD:
            case self::METHOD_OPTIONS:
            case self::METHOD_PATCH:
            case self::METHOD_POST:
            case self::METHOD_PURGE:  // Squid and Varnish support
            case self::METHOD_PUT:
            case self::METHOD_TRACE:
                return true;
        }

        return false;
}
}
