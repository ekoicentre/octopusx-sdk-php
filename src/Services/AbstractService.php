<?php


namespace EIC\OctopusX\Services;


use EIC\OctopusX\Exception\OctopusXException;
use EIC\OctopusX\Responses\OctopusXResponses;
use EIC\OctopusX\Sdk;
use EIC\OctopusX\SendHttpsRequestTrait;
use GuzzleHttp\Psr7\Uri;
use EIC\OctopusX\RequestInterface;

abstract class AbstractService implements ServiceInterface
{
    use SendHttpsRequestTrait{
        send as httpSend;
    }

    /** @var Sdk  */
    protected $sdk;

    /** @var array */
    protected $query;

    public function __construct(Sdk $sdk)
    {
        $this->sdk = $sdk;
        $this->query = [];

    }

    /**
     * @inheritdoc
     */
    public function requiresAuthorization(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getAuthorizationHeader(): string
    {
        return 'Bearer ' . $this->sdk->getAuthorizationToken();
    }

    /**
     * @inheritdoc
     */
    public function getRequestUrl(array $extras = []): Uri
    {
        $path = $this->sdk->getManifest()->getService(static::getName(), 'path');
        # get the path for the service
        if (!empty($this->id)) {
            # requesting data off a service item
            $path .= '/' . $this->id;
        }
        $base = $this->sdk->getUrlRegistry()->getUrl($path, ['path' => $extras, 'query' => $this->getQuery()]);
        # compose the full request URL
        return $base;
    }


    /**
     * @inheritdoc
     */
    public function addQueryArgument(string $name, $value, bool $overwrite = false): RequestInterface
    {
        if (array_key_exists($name, $this->query) && !$overwrite) {
            return $this;
        }
        if (is_null($value)) {
            unset($this->query[$name]);
            return $this;
        }
        $this->query[$name] = $value;
        return $this;
    }


    /**
     * @inheritdoc
     */
    public function setQuery(array $params = []): RequestInterface
    {
        $this->query = $params;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * @inheritdoc
     */
    public function validate(): bool
    {
        $parameters = $this->sdk->getManifest()->getService(static::getName(), 'parameters');
        $headers = $this->sdk->getManifest()->getService(static::getName(), 'headers');
        # get the parameters and headers
        $required = $parameters['required'] ?? [];
        if (empty($required) && empty($headers)) {
            return true;
        }
        $missing = ['body' => [], 'headers' => []];
        # missing container
        if (!empty($headers)) {
            foreach ($headers as $header) {
                if (array_key_exists($header, $this->headers)) {
                    continue;
                }
                $missing['headers'][] = $header;
            }
        }
        if (!empty($required)) {
            foreach ($required as $parameter) {
                if (array_key_exists($parameter, $this->body)) {
                    continue;
                }
                $missing['body'][] = $parameter;
            }
        }
        if (!empty($missing['header']) || !empty($missing['body'])) {
            throw new OctopusXException('Some required parameters are missing in the request.', $missing);
        }
        return true;
    }

    /**
     * @param string $method
     * @param array  $path
     *
     * @return OctopusXResponses
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send(string $method, array $path = [])
    {
        return $this->httpSend($method, $this->sdk->getHttpClient(), $path);
    }

    /**
     * Returns the name of the resource.
     *
     * @return string
     */
    abstract function getName(): string;


}