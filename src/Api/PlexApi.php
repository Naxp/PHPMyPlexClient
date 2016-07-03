<?php
/*
 * The MIT License
 *
 * Copyright 2015 Chris Stretton <cstretton@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Cheezykins\PHPMyPlex\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Webmozart\KeyValueStore\Api\KeyValueStore;

/**
 * Provides an interface to the Httpful request library to communicate with plex.
 * Defines custom headers that Plex requires as part of its API specification.
 *
 * @author Chris Stretton <cstretton@gmail.com>
 */
class PlexApi
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_HEAD = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';
    const OPTION_METHOD = 'method';
    const OPTION_URL = 'url';
    const OPTION_OPTIONS = 'options';
    const SIGN_IN_URL = 'https://plex.tv/users/sign_in.xml';
    protected $headers = [
        'X-Plex-Platform' => PHP_OS,
        'X-Plex-Platform-Version' => false,
        'X-Plex-Provides' => 'controller',
        'X-Plex-Product' => 'PHPMyPlex',
        'X-Plex-Version' => false,
        'X-Plex-Device' => false,
        'X-Plex-Client-Identifier' => false,
    ];
    protected $storage;
    protected $client;

    protected $username;
    protected $password;
    protected $token;

    public function __construct(KeyValueStore $storage, Client $client)
    {
        $this->storage = $storage;
        $this->client = $client;
    }

    public function login($userName, $passWord)
    {
        $token = $this->storage->get('token_' . $userName);
        if (!$this->token) {
            $response = $this->post(self::SIGN_IN_URL);
        }
    }

    /**
     * Helper method to allow retrieval of headers.
     *
     * @param string $name
     *
     * @return string
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->headers)) {
            return $this->headers[$name];
        }
        throw new \OutOfRangeException($name . ' does not exist');
    }

    /**
     * Helper method allows headers to be set as parameters.
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->setHeader($name, $value);
    }

    /**
     * Sets a header in the HTTP request.
     * Headers in camelCase are transposed to X-Plex-Camel-Case.
     *
     * @param string $header
     * @param string $value
     */
    public function setHeader($header, $value)
    {
        if (\substr($header, 0, 7) != 'X-Plex-') {
            $header = 'X-Plex-' . \ucfirst(\preg_replace('/([A-Z])/', '-$0', $header));
        }
        $this->headers[$header] = $value;
    }

    /**
     * @param $url string
     * @param null|array $data
     * @return \stdClass
     * @throws \Exception
     */
    public function get($url, $data = null)
    {
        $requestOptions = [
            'query' => $data
        ];
        $options = $this->buildRequest(self::METHOD_GET, $url, $requestOptions);
        return $this->sendRequest($options);
    }

    /**
     * Builds the options for the request
     * @param $method string
     * @param $url string
     * @param array $options
     * @return array
     */
    protected function buildRequest($method, $url, $options = [])
    {
        if (count($this->headers) > 0) {
            if (!array_key_exists('headers', $options) || !is_array($options['headers'])) {
                $options['headers'] = [];
            }
            foreach ($this->headers as $headerName => $headerValue) {
                $options['headers'][$headerName] = $headerValue;
            }
        }

        $options['verify'] = false;

        $options = $this->addAuthHeader($options);

        return [
            self::OPTION_METHOD => $method,
            self::OPTION_URL => $url,
            self::OPTION_OPTIONS => $options
        ];
    }

    /**
     * @param $options array
     * @return array
     */
    protected function addAuthHeader($options)
    {
        if ($this->token !== null) {
            if (!array_key_exists('headers',
                    $options) || !is_array($options['headers'])
            ) {
                $options['headers'] = [];
            }
            $options['headers'] = 'Bearer ' . $this->token;
        }

        if ($this->username !== null && $this->password !== null) {
            if (!array_key_exists('headers',
                    $options) || !is_array($options['headers'])
            ) {
                $options['headers'] = [];
            }
            $options['headers']['Authorization'] = 'Basic ' . base64_encode($this->username . ':' . $this->password);
        }

        return $options;
    }

    /**
     * @param $options array
     * @return \stdClass
     * @throws \Exception
     */
    protected function sendRequest($options)
    {
        try {
            $response = $this->client->request($options[self::OPTION_METHOD], $options[self::OPTION_URL],
                $options[self::OPTION_OPTIONS]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $request = $e->getRequest();
            $body = (string)$response->getBody();
            $message = "REST Client Error: {$request->getMethod()} {$response->getStatusCode()} {$body}";
            throw new \Exception($message, $e->getCode(), $e);
        }
        return new \SimpleXMLElement($response->getBody()->getContents());
    }

    /**
     * @param $url string
     * @param null|array $data
     * @param boolean $isJson
     * @return \stdClass
     * @throws \Exception
     */
    public function post($url, $data = null, $isJson = true)
    {
        $dataType = 'form_params';
        if ($isJson) {
            $dataType = 'json';
        }
        $requestOptions = [
            $dataType => $data
        ];
        $options = $this->buildRequest(self::METHOD_POST, $url, $requestOptions);
        return $this->sendRequest($options);
    }

    /**
     * @param $url string
     * @param array|null $data
     * @param boolean $isJson
     * @return \stdClass
     * @throws \Exception
     */
    public function put($url, $data = null, $isJson = true)
    {
        $dataType = 'form_params';
        if ($isJson) {
            $dataType = 'json';
        }
        $requestOptions = [
            $dataType => $data
        ];
        $options = $this->buildRequest(self::METHOD_PUT, $url, $requestOptions);
        return $this->sendRequest($options);
    }

    /**
     * @param $url string
     * @param array|null $data
     * @param bool $isJson
     * @return \stdClass
     * @throws \Exception
     */
    public function patch($url, $data = null, $isJson = true)
    {
        $dataType = 'form_params';
        if ($isJson) {
            $dataType = 'json';
        }
        $requestOptions = [
            $dataType => $data
        ];
        $options = $this->buildRequest(self::METHOD_PATCH, $url, $requestOptions);
        return $this->sendRequest($options);
    }

    /**
     * @param $url string
     * @return \stdClass
     * @throws \Exception
     */
    public function head($url)
    {
        $options = $this->buildRequest(self::METHOD_HEAD, $url);
        return $this->sendRequest($options);
    }

    /**
     * @param $url string
     * @return \stdClass
     * @throws \Exception
     */
    public function options($url)
    {
        $options = $this->buildRequest(self::METHOD_OPTIONS, $url);
        return $this->sendRequest($options);
    }

    /**
     * @param $url string
     * @return \stdClass
     * @throws \Exception
     */
    public function delete($url)
    {
        $options = $this->buildRequest(self::METHOD_DELETE, $url);
        return $this->sendRequest($options);
    }

    /**
     * Retrieves a client identifier from storage, or generates a new one and stores it.
     *
     * @return string
     */
    protected function getClientIdentifier()
    {
        $clientIdentifier = $this->storage->get('clientIdentifier', false);

        if ($clientIdentifier === null) {
            $clientIdentifier = uniqid('PHPMyPlex_');
            $this->storage->set('clientIdentifier', $clientIdentifier);
        }

        return $clientIdentifier;
    }
}
