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
use Phalcon\Http\Request\File;
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
        $this->swooleRequest->files = $this->swooleRequest->files ?: [];

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

    /**
     * Gets a charsets array and their quality accepted by the browser/client
     * from $_SERVER["HTTP_ACCEPT_CHARSET"].
     *
     * @return array
     */
    public function getClientCharsets(): array
    {
        return $this->getQualityHeader('accept_charset', 'charset');
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

    /**
     * Gets active server host or name
     *
     * @return string
     */
    public function getHttpHost(): string
    {
        return $this->getServer('http_host') ?:
            $this->getServerName();
    }

    public function getHTTPReferer(): string
    {
        return $this->swooleRequest->server['http_referer'] ?? '';
    }

    /**
     * Gets decoded JSON HTTP raw request body.
     *
     * @param bool $associative
     * @return mixed
     */
    public function getJsonRawBody(bool $associative = false): mixed
    {
        $rawBody = $this->getRawBody();

        if (!is_string($rawBody)) {
            return false;
        }

        return json_decode($rawBody, $associative);
    }

    /**
     * Gets languages array and their quality accepted by the browser/client
     * from $_SERVER["HTTP_ACCEPT_LANGUAGE"].
     *
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->getQualityHeader('accept-language', 'language');
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

    /**
     * Gets HTTP URI which request has been made to
     *
     *```php
     * // Returns /some/path?with=queryParams
     * $uri = $request->getURI();
     *
     * // Returns /some/path
     * $uri = $request->getURI(true);
     *```
     *
     * @param bool $onlyPath If true, query part will be omitted
     * @return string
     */
    public function getURI(bool $onlyPath = false): string
    {
        $requestUri = $this->getServer('request_uri');
        if (null === $requestUri) {
            return '';
        }

        if ($onlyPath) {
            return explode('?', $requestUri)[0];
        }

        return $requestUri;
    }

    /**
     * Gets a variable from the $_POST superglobal applying filters if needed
     * If no parameters are given the $_POST superglobal is returned
     *
     *```php
     * // Returns value from $_POST["user_email"] without sanitizing
     * $userEmail = $request->getPost("user_email");
     *
     * // Returns value from $_POST["user_email"] with sanitizing
     * $userEmail = $request->getPost("user_email", "email");
     *```
     *
     * @param string|null $name
     * @param null $filters
     * @param null $defaultValue
     * @param bool $notAllowEmpty
     * @param bool $noRecursive
     * @return mixed
     * @throws Exception
     */
    public function getPost(
        string $name = null,
        $filters = null,
        $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false,
    ) {
        return $this->getHelper($this->swooleRequest->post, $name, $filters, $defaultValue, $notAllowEmpty, $noRecursive);
    }

    public function getPut(string $name = null, $filters = null, $defaultValue = null, bool $notAllowEmpty = false, bool $noRecursive = false)
    {
        // TODO: Implement getPut() method.
    }

    /**
     * Gets variable from $_GET superglobal applying filters if needed
     * If no parameters are given the $_GET superglobal is returned
     *
     *```php
     * // Returns value from $_GET["id"] without sanitizing
     * $id = $request->getQuery("id");
     *
     * // Returns value from $_GET["id"] with sanitizing
     * $id = $request->getQuery("id", "int");
     *
     * // Returns value from $_GET["id"] with a default value
     * $id = $request->getQuery("id", null, 150);
     *```
     *
     * @param string|null $name
     * @param null $filters
     * @param null $defaultValue
     * @param bool $notAllowEmpty
     * @param bool $noRecursive
     * @return mixed
     * @throws Exception
     */
    public function getQuery(
        string $name = null,
        $filters = null,
        $defaultValue = null,
        bool $notAllowEmpty = false,
        bool $noRecursive = false,
    ): mixed {
        return $this->getHelper(
            $this->swooleRequest->get,
            $name,
            $filters,
            $defaultValue,
            $notAllowEmpty,
            $noRecursive,
        );
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

    /**
     * Gets attached files as Phalcon\Http\Request\File instances.
     *
     * @param bool $onlySuccessful
     * @param bool $namedKeys
     * @return File[]
     */
    public function getUploadedFiles(bool $onlySuccessful = false, bool $namedKeys = false): array
    {
        $files = [];

        foreach ($this->swooleRequest->files as $prefix => $input) {
            if (is_array($input['name'])) {
                $smoothInput = $this->smoothFiles(
                    $input['name'],
                    $input['type'],
                    $input['tmp_name'],
                    $input['size'],
                    $input['error'],
                    $prefix,
                );

                foreach ($smoothInput as $file) {
                    if ($onlySuccessful === false || $file['error'] == UPLOAD_ERR_OK) {
                        $dataFile = [
                            'name' => $file['name'],
                            'type' => $file['type'],
                            'tmp_name' => $file['tmp_name'],
                            'size' => $file['size'],
                            'error' => $file['error'],
                        ];

                        if ($namedKeys === true) {
                            $files[$file['key']] = new File($dataFile, $file['key']);
                        } else {
                            $files[] = new File($dataFile, $file['key']);
                        }
                    }
                }
            }

            if (!$onlySuccessful || $input['error'] == UPLOAD_ERR_OK) {
                if ($namedKeys === true) {
                    $files[$prefix] = new File($input, $prefix);
                } else {
                    $files[] = new File($input, $prefix);
                }
            }
        }

        return $files;
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

    /**
     * Smooth out $_FILES to have plain array with all files uploaded.
     *
     * @param array $names
     * @param array $types
     * @param array $tmpNames
     * @param array $sizes
     * @param array $errors
     * @param string $prefix
     * @return array
     */
    final protected function smoothFiles(
        array $names,
        array $types,
        array $tmpNames,
        array $sizes,
        array $errors,
        string $prefix,
    ): array {
        $files = [];

        foreach ($names as $idx => $name) {
            $p = $prefix . '.' . $idx;

            if (is_string($name)) {
                $files[] = [
                    'name' => $name,
                    'type' => $types[$idx],
                    'tmp_name' => $tmpNames[$idx],
                    'size' => $sizes[$idx],
                    'error' => $sizes[$idx],
                    'key' => $p,
                ];
            }

            if (is_array($name)) {
                $parentFiles = $this->smoothFiles(
                    $name,
                    $types[$idx],
                    $tmpNames[$idx],
                    $sizes[$idx],
                    $errors[$idx],
                    $p,
                );

                foreach ($parentFiles as $file) {
                    $files[] = $file;
                }
            }
        }

        return $files;
    }

    /**
     * Process a request header and return an array of values with their qualities.
     *
     * @param string $serverIndex
     * @param string $name
     * @return array
     */
    final protected function getQualityHeader(string $serverIndex, string $name): array
    {
        $returnedParts = [];
        $serverValue = $this->getServer($serverIndex);
        $serverValue = (null === $serverValue) ? '' : $serverValue;

        $parts = preg_split('/,\\s*/', $serverValue, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $part) {
            $headerParts = [];
            $headerSplit = preg_split('/\s*;\s*/', trim($part), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($headerSplit as $headerPart) {
                if (strpos($headerPart, '=') !== false) {
                    $split = explode('=', $headerPart, 2);
                    if ($split[0] === 'q') {
                        $headerParts['quality'] = (float)$split[1];
                    } else {
                        $headerParts[$split[0]] = $split[1];
                    }
                } else {
                    $headerParts[$name] = $headerPart;
                    $headerParts['quality'] = 1.0;
                }

                $returnedParts[] = $headerParts;
            }
        }

        return $returnedParts;
    }
}
