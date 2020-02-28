<?php

/**
 * Checks if this library was installed via composer.
 *
 * @return bool
 */
function is_installed_via_composer(): bool
{
    $assumedVendorDir = dirname(__DIR__, 3);
    # try to see if we can find a vendor directory
    $isVendorDir = ends_with($assumedVendorDir, 'vendor');
    # check if it's actually a vendor directory
    $hasAutoloadFile = file_exists(implode(DIRECTORY_SEPARATOR, [$assumedVendorDir, 'autoload.php']));
    # check if there's an autoload.php file present inside the vendor directory
    return $isVendorDir && $hasAutoloadFile;

}

/**
 * Returns a path string relative to the library's root installation directory.
 *
 * @param string|null $path
 *
 * @return string
 */
function octopus_sdk_root_path(string $path = null): string
{
    $level = is_installed_via_composer() ? 4 : 1;
    # the level in the tree to reach the application root path
    $appDir = dirname(__DIR__, $level);
    return implode(DIRECTORY_SEPARATOR, [$appDir, (string) $path]);
}


/**
 * Returns a path string relative to the library's base directory.
 *
 * @param string|null $path
 *
 * @return string
 */
function octopus_sdk_app_path(string $path = null): string
{
    $appDir = dirname(__DIR__, 1);
    return implode(DIRECTORY_SEPARATOR, [$appDir, (string) $path]);
}

/**
 * Returns the HTTP Client to use for making the requests.
 *
 * @param \GuzzleHttp\Psr7\Uri|null $uri
 *
 * @return \GuzzleHttp\Client
 */

function http_client(GuzzleHttp\Psr7\Uri $uri = null): GuzzleHttp\Client {
    $options = [
        \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true,
        \GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 30.0,
        \GuzzleHttp\RequestOptions::TIMEOUT => 30.0,
        \GuzzleHttp\RequestOptions::HEADERS => [
            'User-Agent' => 'OctopusX-sdk-php/'.Eic\OctopusX\Sdk::VERSION
        ]
    ];

    /*
     * attaches the base uri to the options request options
     */

    if($uri !== null){
        $options['base_uri'] = $uri->getScheme() . '://' . $uri->getAuthority();
        $options['base_uri'] .= !empty($uri->getPath()) ? '/'.$uri->getPath() : '';
    }
    return new \GuzzleHttp\Client($options);
}



/**
 * Loads the manifest.json file into an array.
 *
 * @return array
 */
function load_manifest(): array
{
    $contents = file_get_contents(octopus_sdk_app_path('manifest.json'));
    # read the manifest.json file in
    return json_decode($contents, true) ?? [];
}

/**
 * A small utility function to wrap the PHP parse_str function.
 *
 * @param string $queryString
 *
 * @return array
 */
function parse_query_parameters(string $queryString): array
{
    $params = [];
    parse_str($queryString, $params);
    return $params;
}

/**
 * Performs a login for using the provided details; if successful, it returns the "access_token"
 * (or OctopusX Response  - depending on the value of the $returnToken parameter), else it will
 * return the actual response object.
 *
 * NOTE: The client_id, and client_secret must be present as request params given to you.
 *
 *
 * @param EIC\OctopusX\Sdk $sdk
 * @param string               $username
 * @param string               $password
 * @param bool                 $returnToken
 *
 * @return EIC\OctopusX\Responses\OctopusXResponses|string
 * @throws EIC\OctopusX\Exception\OctopusXException
 * @throws \GuzzleHttp\Exception\GuzzleException
 */

function login_via_password(\EIC\OctopusX\Sdk $sdk, string $username, string $password, bool $returnToken = true)
{
    $service = $sdk->createLoginService();
    $response = $service->addBodyParam('username', $username)
        ->addBodyParam('password', $password)
        ->send('post');
    # sends a HTTP POST request with the parameters
    return $response->isSuccessful() && $returnToken ? $response->getData()['access_token'] : $response;
}



