<?php

namespace Exozet\GruntBundle\Tests;

use Buzz\Message\Request;
use Buzz\Message\Response;

class GruntCacheWarmerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    protected function foo(){
        $true = true;
        $this->assertEquals(true, $true);
    }
}
