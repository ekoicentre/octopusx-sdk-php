<?php
session_start();
require_once(dirname(__DIR__).'/vendor/autoload.php');


$sdk = new EIC\OctopusX\Sdk(['credentials' => ['id' => 2, 'secret' => 'hFWx5xkPbVKXvLwD17Lbl5MFczORgKZwvawKOzpc']]);
 function login($sdk){
    try {
        if (empty($_SESSION['token'])) {
            $response = login_via_password($sdk, 'fake-admin@yemisi.com', 'randomPass');
            if (is_string($response)) {
                $_SESSION['token'] = $response;
            } else {
                return var_dump($response->getRawResponse());
            }
        }
        return json_encode($_SESSION['token']);
    } catch (EIC\OctopusX\Exception\OctopusXException $e) {
        return json_encode($e->getMessage(), $e->context);
    } catch (Exception $e) {
        return json_encode($e->getMessage());
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
    }
}

login($sdk);

