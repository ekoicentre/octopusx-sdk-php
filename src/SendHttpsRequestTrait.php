<?php /** @noinspection NotOptimalIfConditionsInspection */


namespace EIC\OctopusX;


use EIC\OctopusX\Exception\OctopusXException;
use EIC\OctopusX\Responses\OctopusXResponses;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\ConnectException;
use EIC\OctopusX\SendsHttpRequestTrait;
use GuzzleHttp\Exception\BadResponseException;

trait SendHttpsRequestTrait
{
    /** @var array  */
    protected $headers = [];

    /** @var array  */
    protected $body = [];

    /** @var array  */
    protected $multipart = [];

    /**
     * @inheritdoc
     */
    public function requiresAuthorization(): bool
    {
        return false;
    }


    /**
     * The value for the Authorization header.
     *
     * @return string
     */
    public function getAuthorizationHeader(): string
    {
        return '';
    }

    /**
     * Does the request carry JSON data?
     *
     * @return bool
     */
    public function isJsonRequest(): bool
    {
        return true;
    }

    /**
     * The request URL.
     *
     * @param array $path
     * @return Uri
     */
    public function getRequestUrl(array $path): Uri
    {
        return new Uri();
    }

    /**
     * Pre-fills the request header with some default values as required.
     *
     * @return $this
     */

    protected function prefillHeader()
    {
        if ($this->requiresAuthorization() && !empty($this->getAuthorizationHeader())) {
            $this->headers['Authorization'] = $this->getAuthorizationHeader();
        }
        return $this;
    }

    /**
     * Pre-fills the request body with whatever data is required.
     * This method should be overridden to customise what should be placed into the body by default.
     * This applies to DELETE, POST, and PUT requests, allows you to set the payload
     *
     * @return $this
     */
    protected function prefillBody()
    {
        return $this;
    }

    /**
     * Adds a parameter to the body of the request.
     *
     * @param string $name
     * @param        $value
     * @param bool   $overwrite
     *
     * @return $this
     */


    public function addBodyParam(string $name, $value, bool $overwrite = false): self
    {
        $keyExists = array_key_exists($name, $this->body);
        # check if the key already exists
        if ($keyExists && !$overwrite) {
            return $this;
        }
        if ($value === null) {
            if ($keyExists) {
                unset($this->body[$name]);
            }
            return $this;
        }
        if ($value === null) {
            unset($this->query[$name]);
            return $this;
        }
        $this->body[$name] = $value;
        return $this;
    }

    /**
     * Adds some multipart data to the request body.
     *
     * @param string            $name
     * @param string|resource   $content the string content for the key; or resource gotten from fopen()
     * @param string|null       $filename
     * @param bool              $overwrite
     *
     * @return $this
     */
    public function addMultipartParam(string $name, $content, string $filename = null, bool $overwrite = false): self
    {
        if (array_key_exists($name, $this->multipart) && !$overwrite) {
            return $this;
        }
        $part = ['name' => $name, 'contents' => $content];
        if (!empty($filename)) {
            $part['filename'] = $filename;
        }
        $this->multipart[] = $part;
        return $this;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        throw new OctopusXException('You should override this method.');
    }


    public function send(string $method, Client $httpClient, array $path = []): OctopusXResponses
    {
        $this->prefillHeader();
        $this->prefillBody();
        if (strtolower($method) !== 'get') {
            # we don't validate GEt requests
            $this->validate();
        }
        $uri = $this->getRequestUrl($path);
        $url = $uri->getScheme() . '://' . $uri->getAuthority() . $uri->getPath();
        # set the URL
        try {
            $options = [];
            # the request data
            if (!empty($this->headers)) {
                $options[RequestOptions::HEADERS] = $this->headers;
            }
            if (!empty($uri->getQuery())) {
                # some query parameters are present in the URL
                $options[RequestOptions::QUERY] = parse_query_parameters($uri->getQuery());
            }
            if (strtolower($method) !== 'get') {
                # not a get request
                if (!empty($this->multipart)) {
                    # check if we have some multipart data first
                    foreach ($this->body as $key => $value) {
                        # add the requested body params to the multipart data
                        $this->multipart[] = ['name' => $key, 'contents' => $value];
                    }
                    $options[RequestOptions::MULTIPART] = $this->multipart;

                } elseif ($this->isJsonRequest() && !empty($this->body)) {
                    # a JSON request
                    $options[RequestOptions::JSON] = $this->body;

                } elseif (!empty($this->body)) {
                    # we switch to an application/www-form-urlencoded type
                    $options[RequestOptions::FORM_PARAMS] = $this->body;
                }
            }
            $response = $httpClient->request($method, $url, $options);
            return new OctopusXResponses((string) $response->getBody());

        } catch (BadResponseException $e) {
            // in the case of a failure, let's know the status
            return new OctopusXResponses((string) $e->getResponse()->getBody(), $e->getResponse()->getStatusCode(), $e->getRequest());

        } catch (ConnectException $e) {
            return new OctopusXResponses('{"status": "error", "data": "'.$e->getMessage().'"}', 0);
        }
    }

}