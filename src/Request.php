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
use Phalcon\Filter\FilterInterface;
use Phalcon\Http\Message\RequestMethodInterface;
use Phalcon\Http\RequestInterface;
use Phalcon\Http\Request\Exception;
use Swoole\Http\Request as SwooleRequest;

class Request extends AbstractInjectionAware implements RequestInterface, RequestMethodInterface, InjectionAwareInterface
{
    private SwooleRequest $swooleRequest;

    /**
     * Emulation of `$_REQUEST` variable.
     *
     * @var array
     */
    private array $combinedRequest;

    /**
     * @var FilterInterface|null
     */
    private ?FilterInterface $filterService = null;

    public function __construct(SwooleRequest $swooleRequest)
    {
        $this->swooleRequest = $swooleRequest;

        /**
         * Adjust default `null` value to empty array.
         */
        $this->swooleRequest->get = $this->swooleRequest->get ?: [];
        $this->swooleRequest->post = $this->swooleRequest->post ?: [];
        $this->swooleRequest->cookie = $this->swooleRequest->cookie ?: [];

        $this->combinedRequest = array_merge(
            $this->swooleRequest->get,
            $this->swooleRequest->post,
            $this->swooleRequest->cookie,
        );
    }

    /**
     * Gets a variable from the $_REQUEST superglobal applying filters if
     * needed. If no parameters are given the $_REQUEST superglobal is returned
     *
     *```php
     * // Returns value from $_REQUEST["user_email"] without sanitizing
     * $userEmail = $request->get("user_email");
     *
     * // Returns value from $_REQUEST["user_email"] with sanitizing
     * $userEmail = $request->get("user_email", "email");
     *
     * @param string|null $name
     * @param null $filters
     * @param null $defaultValue
     * @param bool $notAllowEmpty
     * @param bool $noRecursive
     * @return mixed
     * @throws Exception
     */
    public function get(
        string $name = null,
        $filters = null,
        $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false
    ): mixed {
        return $this->getHelper($this->combinedRequest, $name, $filters, $defaultValue, $notAllowEmpty, $noRecursive);
    }

    public function getAcceptableContent(): array
    {
        // TODO: Implement getAcceptableContent() method.
    }

    public function getBasicAuth(): ?array
    {
        if (!$this->hasServer('php_auth_user') || !$this->hasServer('php_auth_pw')) {
            return null;
        }

        return [
            'username' => $this->getServer('php_auth_user'),
            'password' => $this->getServer('php_auth_pw'),
        ];
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

    /**
     * Gets content type which request has been made
     */
    public function getContentType(): ?string
    {
        return $this->getServerArray()['content_type'] ?? null;
    }

    public function getDigestAuth(): array
    {
        // TODO: Implement getDigestAuth() method.
    }

    /**
     * Gets HTTP header from request data
     */
    public function getHeader(string $header): string
    {
        $name = strtolower(strtr($header, '_', '-'));
        $server = $this->getServerArray();

        return $server[$name] ?? '';
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
        $https = $this->swooleRequest->server['https'] ?? '';

        return $https && $https !== "off" ? 'https' : 'http';
    }

    /**
     * Gets variable from $_SERVER superglobal
     */
    public function getServer(string $name): ?string
    {
        return $this->swooleRequest->server[$name] ?? null;
    }

    /**
     * Gets active server address IP
     */
    public function getServerAddress(): string
    {
        $serverAddress = $this->getServer('server_addr');
        if (null === $serverAddress) {
            return gethostbyname('localhost');
        }

        return $serverAddress;
    }

    /**
     * Gets active server name
     */
    public function getServerName(): string
    {
        return $this->getServer('server_name') ?: 'localhost';
    }

    public function getUploadedFiles(bool $onlySuccessful = false, bool $namedKeys = false): array
    {
        // TODO: Implement getUploadedFiles() method.
    }

    public function getUserAgent(): string
    {
        return $this->getHeader('user-agent');
    }

    public function has(string $name): bool
    {
        return isset($this->combinedRequest[$name]);
    }

    public function hasFiles(): bool
    {
        return $this->numFiles(true) > 0;
    }

    public function hasHeader(string $header): bool
    {
        return isset($this->swooleRequest->header[$header]);
    }

    public function hasQuery(string $name): bool
    {
        return isset($this->swooleRequest->get[$name]);
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

    /**
     * Checks whether request has been made using ajax
     */
    public function isAjax(): bool
    {
        return $this->hasServer('http_x_requested_with') &&
            $this->getServer('http_x_requested_with') === 'XMLHttpRequest';
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

    /**
     * Check if HTTP method match any of the passed methods
     * When strict is true it checks if validated methods are real HTTP methods
     *
     * @param array|string $methods
     * @param bool $strict
     * @return bool
     * @throws Exception
     */
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

    /**
     * Checks whether request has been made using any secure layer
     */
    public function isSecure(): bool
    {
        return $this->getScheme() === 'https';
    }

    /**
     * Checks whether request has been made using SOAP
     */
    public function isSoap(): bool
    {
        if ($this->hasServer('http_soapaction')) {
            return true;
        }

        return strstr($this->getContentType(), 'application/soap+xml') !== false;
    }

    public function isTrace(): bool
    {
        return $this->getMethod() === self::METHOD_TRACE;
    }

    /**
     * Returns the number of files available
     *
     * @param bool $onlySuccessful
     * @return int
     */
    public function numFiles(bool $onlySuccessful = false): int
    {
        $numFiles = 0;

        foreach ($this->swooleRequest->files as $file) {
            if (!isset($file['error'])) {
                continue;
            }

            $error = $file['error'];
            if (!is_array($error)) {
                if (!$error || !$onlySuccessful) {
                    $numFiles++;
                }
            } else {
                $numFiles += $this->hasFileHelper($error, $onlySuccessful);
            }
        }

        return $numFiles;
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

    /**
     * Recursively counts file in an array of files
     *
     * @param mixed $data
     * @param bool $onlySuccessful
     * @return int
     */
    final protected function hasFileHelper(mixed $data, bool $onlySuccessful): int
    {
        if (!is_array($data)) {
            return 1;
        }

        $numberFiles = 0;

        foreach ($data as $value) {
            if (!is_array($value)) {
                if (!$value || !$onlySuccessful) {
                    $numberFiles++;
                }
            } else {
                $numberFiles += $this->hasFileHelper($value, $onlySuccessful);
            }
        }

        return $numberFiles;
    }

    /**
     * Helper to get data from superglobals, applying filters if needed.
     * If no parameters are given the superglobal is returned.
     *
     * @param array $source
     * @param string|null $name
     * @param mixed|null $filters
     * @param mixed|null $defaultValue
     * @param bool $notAllowEmpty
     * @param bool $noRecursive
     * @return mixed
     * @throws Exception
     */
    final protected function getHelper(
        array   $source,
        ?string $name = null,
        mixed   $filters = null,
        mixed   $defaultValue = null,
        bool    $notAllowEmpty = false,
        bool    $noRecursive = false
    ): mixed
    {
        if ($name === null) {
            return $source;
        }

        if (!isset($source[$name])) {
            return $defaultValue;
        }

        $value = $source[$name];
        if (!is_numeric($value) && empty($value) && $notAllowEmpty) {
            return $defaultValue;
        }

        if ($filters !== null) {
            $filterService = $this->getFilterService();
            $value = $filterService->sanitize($value, $filters, $noRecursive);

            /**
             * @todo Leave this here for PHP 7.4/8.0. Remove when appropriate.
             * Some filters use filter_var which can return `false`
             */
            if ($value === false) {
                return $defaultValue;
            }
        }

        return $value;
    }

    /**
     * Checks the filter service and assigns it to the class parameter
     * @throws Exception
     */
    private function getFilterService(): FilterInterface
    {
        if ($this->filterService !== null) {
            return $this->filterService;
        }

        if ($this->container === null) {
            throw new Exception(
                "A dependency injection container is required to access the 'filter' service"
            );
        }

        return $this->filterService = $this->container->getShared('filter');
    }

    private function getServerArray(): array
    {
        return $this->swooleRequest->header ?: [];
    }
}
