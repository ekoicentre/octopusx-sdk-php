<?php
namespace EIC\OctopusX\Tests;

use EIC\OctopusX\Exception\OctopusXException;
use PHPUnit\Framework\TestCase;
use EIC\OctopusX\Sdk as Sdk;

class SdkTest extends TestCase
{
    /** @var $sdk  */
    protected $sdk;
    public function init(){
        $this->sdk = new Sdk(['credentials' => ['id' => 1, 'secret' => 'fake-secret-code']]);
    }

    public function testCheckCredentialsFails()
    {
        $this->expectException(OctopusXException::class);
        $sdk = new Sdk();
    }
}