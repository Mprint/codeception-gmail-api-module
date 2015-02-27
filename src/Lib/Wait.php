<?php
namespace Codeception\Lib;

use Codeception\Exception\TimeOut;
use Codeception\Lib\Interfaces\ExpectedCondition;

class Wait {

    protected $timeout;
    protected $interval;
    protected $driver;

    public function __construct($driver, $timeout_in_second = null, $interval_in_millisecond = null) {
        $this->driver = $driver;
        $this->timeout = ($timeout_in_second) ? $timeout_in_second : 30;
        $this->interval =
            ($interval_in_millisecond) ? $interval_in_millisecond : 250;
    }


    /**
     * Calls the function provided with the driver as an argument until the return
     * value is not falsey.
     *
     * @param $func_or_ec (closure|ExpectedConditionInterface)
     * @param string $message
     * @return mixed The return value of $func_or_ec
     * @throws TimeOut
     */
    public function until($func_or_ec, $message = "") {
        $end = time() + $this->timeout;
        $last_exception = null;

        while ($end > time()) {
            if ($func_or_ec instanceof ExpectedCondition) {
                $ret_val = call_user_func($func_or_ec->getApply(), $this->driver);
            } else {
                $ret_val = call_user_func($func_or_ec, $this->driver);
            }
            if ($ret_val) {
                return $ret_val;
            }
            usleep($this->interval * 1000);
        }

        throw new TimeOut($message);
    }
}