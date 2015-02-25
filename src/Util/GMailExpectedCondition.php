<?php
namespace Codeception\Util;

class GMailExpectedCondition implements ExpectedConditionInterface{

    /**
     * A closure function to be executed by RemoteWait. It should return
     * a truthy value, mostly boolean, array or a /Google_Service_Gmail_Message, on success.
     */
    private $apply;

    /**
     * @return (function():T) a closure function to be executed
     */
    public function getApply() {
        return $this->apply;
    }

    protected function __construct($apply) {
        $this->apply = $apply;
    }

    /**
     * An expectation for checking that an email from a email address is present in inbox
     *
     * @param $address string
     * @return GMailExpectedCondition
     */
    public static function emailFrom($address) {
        return new GMailExpectedCondition(
            function ($remote) use ($address) {
                return $remote->getEmails(array(
                    'maxResults' => 1,
                    'q' => "from:{$address}",
                ));
            }
        );
    }

}