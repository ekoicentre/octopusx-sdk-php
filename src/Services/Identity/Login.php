<?php


namespace EIC\OctopusX\Services\Identity;


use EIC\OctopusX\Services\AbstractService;

class Login extends AbstractService
{
    /**
     * Returns the name of the resource.
     *
     * @return string
     */

    protected function prefillBody()
    {
        $this->body['client_id'] = $this->sdk->getClientId();
        $this->body['client_secret'] = $this->sdk->getClientSecret();
//        $this->body['grant_type'] = 'password';
//        $this->body['scope'] = '*';
        return $this;
    }

    function getName(): string
    {
        return 'Login';
    }

}