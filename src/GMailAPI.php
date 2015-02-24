<?php
namespace Codeception\Module;
use Codeception\Module;
class GMailAPI extends Module
{
	public function seeTrue()
    {
        $this->assertTrue(true, "This is a simple test");
    }
}