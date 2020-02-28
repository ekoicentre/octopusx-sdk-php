<?php


namespace EIC\OctopusX\Services\Identity;


use EIC\OctopusX\Services\AbstractService;

class Authorization extends AbstractService
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