<?php
namespace Codeception\Lib\Interfaces;

interface ExpectedCondition {
    /**
     * @return (function():T) a closure function to be executed
     */
    public function getApply();
}