<?php
namespace Codeception\Util;

use Codeception\Exception\TimeOut;

class RemoteWait {

    protected $timeout;
    protected $interval;
    protected $remote;

    public function __construct(GMailRemote $remote, $timeout_in_second = null, $interval_in_millisecond = null) {
        $this->remote = $remote;
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
            if ($func_or_ec instanceof ExpectedConditionInterface) {
                $ret_val = call_user_func($func_or_ec->getApply(), $this->remote);
            } else {
                $ret_val = call_user_func($func_or_ec, $this->remote);
            }
            if ($ret_val) {
                return $ret_val;
            }
            usleep($this->interval * 1000);
        }

        throw new TimeOut($message);
    }
}