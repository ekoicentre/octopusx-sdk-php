<?php


namespace EIC\OctopusX\Authentication\Identity;


use EIC\OctopusX\Authentication\AbstractAuthentication;

class Login extends AbstractAuthentication
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
        $this->body['grant_type'] = 'password';
        $this->body['scope'] = '*';
        return $this;
    }

    function getName(): string
    {
        return 'Login';
    }

}