<?php


namespace EIC\OctopusX\Exception;


class OctopusXException extends \RuntimeException
{
    /** @var array  */
    public $context;

    public function __construct(string $message = "", array $context = [])
    {
        parent::__construct($message, 0, null);
        $this->context = $context ?: [];
    }
}