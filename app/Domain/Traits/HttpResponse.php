<?php

namespace App\Domain\Traits;

use Illuminate\Support\Facades\Request;

trait HttpResponse
{

    /**
     * @var string
     */
    public $version;

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var string
     */
    protected $statusText;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var array
     */
    protected $httpHeaders = [];

    /**
     * @var array
     */
    public static $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Mandatory fields should be validated',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        423 => 'Workflow error'
    ];


    /**
     * @return array
     */
    public function getDefaultHttpHeaders()
    {
        return [
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Send API Response
     *
     * @param array | Collection $data
     * @param string $message
     * @param int $statusCode
     * @param array $headers
     *
     * @param null $statusText
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($data = null, $message = 'OK', $statusCode = 200, $headers = [])
    {

        $this->setStatusCode($statusCode);
        $this->addHttpHeaders($headers);

        return Response()->json([
            'status_code' => $this->getStatusCode(),
            'status' => $this->getStatusText(),
            'message' => $message,
            'result' => $data,
            'locale' => app()->getLocale(),
        ], $statusCode)
            ->withHeaders($this->getHttpHeaders());
    }


    /**
     * @param null $data
     * @param string $message
     * @param int $statusCode
     * @param array $headers
     *
     * @param null $statusText
     *
     * @param null $debug
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendErrorResponse(
        $data = null,
        $message = 'ERROR',
        $statusCode = 400,
        $headers = [],
        $statusText = null,
        $debug = null
        )
    {

        $this->setStatusCode($statusCode, $statusText);
        $this->addHttpHeaders($headers);

        $errorObject = [
            'status_code' => $this->getStatusCode(),
            'status' => $this->getStatusText(),
            'message' => $message,
            'result' => $data
        ];

        if ($debug) {
            $errorObject['debug'] = $debug;
        }

        return Response()->json($errorObject, $statusCode)
            ->withHeaders($this->getHttpHeaders());
    }

    /**
     *
     * @param Exception $exception
     * @param int $statusCode
     * @param array $headers
     * @param string $message
     * @param array $data
     *
     * @return Response
     */

    public function sendException($exception, $statusCode = 400, $headers = [], $message = 'Error', $data = null)
    {
        if ($exception) {
            $statusCode = $exception->getCode();
            $message = $exception->getMessage();
        }

        $this->setStatusCode($statusCode);
        $this->addHttpHeaders($headers);

        return Response()->json([
            'status_code' => $this->getStatusCode(),
            'status' => $this->getStatusText(),
            'message' => $message,
            'result' => $data
        ], $statusCode)
            ->withHeaders($this->getHttpHeaders());
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @param string $text
     *
     * @throws InvalidArgumentException
     */
    public function setStatusCode($statusCode, $text = null)
    {
        $this->statusCode = (int) $statusCode;
        if ($this->isInvalid()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $statusCode));
        }

        if ($text === false) {
            $this->statusText = '';
        } else {
            $this->statusText = $text === null ? self::$statusTexts[$this->statusCode] : $text;
        }
    }

    /**
     * @return string
     */
    public function getStatusText()
    {
        return $this->statusText;
    }


    /**
     * @param array $httpHeaders
     */
    public function setHttpHeaders(array $httpHeaders)
    {
        $this->httpHeaders = $httpHeaders;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setHttpHeader($name, $value)
    {
        $this->httpHeaders[$name] = $value;
    }

    /**
     * @param array $httpHeaders
     */
    public function addHttpHeaders(array $httpHeaders)
    {
        $this->httpHeaders = array_merge($this->httpHeaders, $httpHeaders);
    }

    /**
     * @return array
     */
    public function getHttpHeaders()
    {

        $defaultHeaders = $this->getDefaultHttpHeaders();
        return array_merge($defaultHeaders, $this->httpHeaders);
    }

    /**
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getHttpHeader($name, $default = null)
    {
        return isset($this->httpHeaders[$name]) ? $this->httpHeaders[$name] : $default;
    }

    /**
     * @param string $format
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function getResponseBody($format = 'json')
    {
        if ($format === 'json') {
            return $this->parameters ? json_encode($this->parameters) : '';
        } elseif ($format === 'xml') {
            // this only works for single-level arrays
            $xml = new \SimpleXMLElement('<response/>');
            foreach ($this->parameters as $key => $param) {
                $xml->addChild($key, $param);
            }
            return $xml->asXML();
        }

        throw new \InvalidArgumentException(sprintf('The format %s is not supported', $format));
    }

    /**
     * @param string $format
     */
    public function send($format = 'json')
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return;
        }

        if ($format === 'json') {
            $this->setHttpHeader('Content-Type', 'application/json');
        } elseif ($format === 'xml') {
            $this->setHttpHeader('Content-Type', 'text/xml');
        }
        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText));

        foreach ($this->getHttpHeaders() as $name => $header) {
            header(sprintf('%s: %s', $name, $header));
        }
        return $this->getResponseBody($format);
    }

    /**
     * @param int $statusCode
     * @param string $error
     * @param string $errorDescription
     * @param string $errorUri
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function setError($statusCode, $error, $errorDescription = null, $errorUri = null)
    {
        $parameters = [
            'error' => [
                'code' => $statusCode,
                'data' => [
                    'error' => $error,
                    'message' => $errorDescription
                ]
            ]
        ];

        if (!is_null($errorUri)) {
            if (strlen($errorUri) > 0 && $errorUri[0] == '#') {
                // we are referencing an oauth bookmark (for brevity)
                $errorUri = 'https://tools.ietf.org/html/rfc6749' . $errorUri;
            }
            $parameters['error_uri'] = $errorUri;
        }

        $httpHeaders = [
            'Cache-Control' => 'no-store'
        ];

        $this->setStatusCode($statusCode);
        $this->addHttpHeaders($httpHeaders);

        if (!$this->isClientError() && !$this->isServerError()) {
            throw new \InvalidArgumentException(
                sprintf('The HTTP status code is not an error ("%s" given).', $statusCode)
            );
        }
    }

    /**
     * @param int $statusCode
     * @param string $url
     * @param string $state
     * @param string $error
     * @param string $errorDescription
     * @param string $errorUri
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function setRedirect(
        $statusCode, $url, $state = null, $error = null, $errorDescription = null, $errorUri = null
        )
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $parameters = [];

        if (!is_null($state)) {
            $parameters['state'] = $state;
        }

        if (!is_null($error)) {
            $this->setError(400, $error, $errorDescription, $errorUri);
        }
        $this->setStatusCode($statusCode);
        $this->addParameters($parameters);

        if (count($this->parameters) > 0) {
            // add parameters to URL redirection
            $parts = parse_url($url);
            $sep = isset($parts['query']) && count($parts['query']) > 0 ? '&' : '?';
            $url .= $sep . http_build_query($this->parameters);
        }

        $this->addHttpHeaders(['Location' => $url]);

        if (!$this->isRedirection()) {
            throw new \InvalidArgumentException(
                sprintf('The HTTP status code is not a redirect ("%s" given).', $statusCode)
            );
        }
    }

    /**
     * @return Boolean
     *
     * @api
     *
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    public function isInvalid()
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isInformational()
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isSuccessful()
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isRedirection()
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isClientError()
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * @return Boolean
     *
     * @api
     */
    public function isServerError()
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Function from Symfony2 HttpFoundation - output pretty header
     *
     * @param array $headers
     *
     * @return string
     */
    private function getHttpHeadersAsString($headers)
    {
        if (count($headers) == 0) {
            return '';
        }

        $max = max(array_map('strlen', array_keys($headers))) + 1;
        $content = '';
        ksort($headers);
        foreach ($headers as $name => $values) {
            foreach ($values as $value) {
                $content .= sprintf("%-{$max}s %s\r\n", $this->beautifyHeaderName($name) . ':', $value);
            }
        }

        return $content;
    }

    /**
     * Function from Symfony2 HttpFoundation - output pretty header
     *
     * @param string $name
     *
     * @return mixed
     */
    private function beautifyHeaderName($name)
    {
        return preg_replace_callback('/\-(.)/', [$this, 'beautifyCallback'], ucfirst($name));
    }

    /**
     * Function from Symfony2 HttpFoundation - output pretty header
     *
     * @param array $match
     *
     * @return string
     */
    private function beautifyCallback($match)
    {
        return '-' . strtoupper($match[1]);
    }

    /**
     * @param null $data
     * @param int $statusCode
     * @param array $headers
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendAPIErrorResponse($data = null, $statusCode = 422, $headers = [])
    {

        $this->addHttpHeaders($headers);

        return Response()->json([
            'status' => $statusCode,
            'content' => $data
        ], $statusCode)
            ->withHeaders($this->getHttpHeaders());
    }
}
