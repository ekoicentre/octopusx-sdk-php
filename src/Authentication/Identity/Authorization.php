<?php


namespace EIC\OctopusX\Authentication\Identity;


use EIC\OctopusX\Authentication\AbstractAuthentication;

class Authorization extends AbstractAuthentication
{
    /**
     * Returns the name of the resource.
     *
     * @return string
     */

    function getName(): string
    {
        return 'Authorization';
    }

}