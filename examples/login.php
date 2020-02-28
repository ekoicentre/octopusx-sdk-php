<?php
session_start();
require_once(dirname(__DIR__).'/vendor/autoload.php');

$sdk = new EIC\OctopusX\Sdk(['credentials' => ['id' => 2, 'secret' => 'hFWx5xkPbVKXvLwD17Lbl5MFczORgKZwvawKOzpc']]);
try {
    if (empty($_SESSION['token'])) {
        $response = login_via_password($sdk, 'fake-admin@yemisi.com', 'randomPass');
        if (is_string($response)) {
            $_SESSION['token'] = $response;
        } else {
            echo($response->getRawResponse());
        }
    }
    var_dump($_SESSION['token']);
} catch (EIC\OctopusX\Exception\OctopusXException $e) {
    var_dump($e->getMessage(), $e->context);
} catch (Exception $e) {
    echo($e->getMessage());
}