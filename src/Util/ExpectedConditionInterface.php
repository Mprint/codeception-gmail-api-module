<?php
namespace Codeception\Util;

interface ExpectedConditionInterface {
    /**
     * @return (function():T) a closure function to be executed
     */
    public function getApply();
}